<?php

namespace App\Libraries;

use App\Models\QuoteRequestAnswerModel;
use App\Models\QuoteRequestFileModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;
use Throwable;

class QuoteRequestSubmission
{
    public const MAX_FILE_BYTES = 8 * 1024 * 1024;
    public const MAX_TOTAL_FILE_BYTES = 20 * 1024 * 1024;
    public const MAX_FILES = 5;

    private const ALLOWED_FILES = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    /**
     * @param list<array<string, mixed>> $catalog
     * @param array<int, string|list<string>> $answers
     * @param array<int, UploadedFile|null> $files
     * @return array<string, string>
     */
    public function validate(array $catalog, array $answers, array $files): array
    {
        $errors = [];
        $fileCount = 0;
        $fileBytes = 0;

        foreach ($catalog as $group) {
            foreach ($group['questions'] as $question) {
                $id = (int) $question['id'];
                $type = (string) $question['field_type'];
                $value = $answers[$id] ?? ($type === 'checkboxes' ? [] : '');
                $file = $files[$id] ?? null;

                if ($type === 'file') {
                    if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
                        if ((int) $question['is_required'] === 1) {
                            $errors['files.' . $id] = $question['label'] . ' is required.';
                        }
                        continue;
                    }
                    $fileCount++;
                    $fileBytes += (int) $file->getSize();
                    $fileError = $this->fileError($file);
                    if ($fileError !== null) {
                        $errors['files.' . $id] = $question['label'] . ': ' . $fileError;
                    }
                    continue;
                }

                if ((int) $question['is_required'] === 1 && $this->isEmpty($value)) {
                    $errors['answers.' . $id] = $question['label'] . ' is required.';
                    continue;
                }
                if ($this->isEmpty($value)) {
                    continue;
                }
                if ($type === 'email' && (! is_string($value) || filter_var($value, FILTER_VALIDATE_EMAIL) === false)) {
                    $errors['answers.' . $id] = $question['label'] . ' must be a valid email address.';
                } elseif ($type === 'number' && (! is_string($value) || ! is_numeric($value))) {
                    $errors['answers.' . $id] = $question['label'] . ' must be a number.';
                } elseif ($type === 'date' && (! is_string($value) || ! $this->validDate($value))) {
                    $errors['answers.' . $id] = $question['label'] . ' must be a valid date.';
                }
            }
        }

        if ($fileCount > self::MAX_FILES) {
            $errors['files'] = 'Attach no more than ' . self::MAX_FILES . ' files.';
        }
        if ($fileBytes > self::MAX_TOTAL_FILE_BYTES) {
            $errors['files'] = 'Attachments must be 20 MB or less in total.';
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $customer
     * @param list<array<string, mixed>> $services
     * @param list<array<string, mixed>> $catalog
     * @param array<int, string|list<string>> $answers
     * @param array<int, UploadedFile|null> $files
     * @return array<string, mixed>
     */
    public function create(array $customer, array $services, array $catalog, array $answers, array $files): array
    {
        if ($services === []) {
            throw new RuntimeException('A quote request must include at least one available service.');
        }

        $db = db_connect();
        $movedPaths = [];
        $db->transBegin();

        try {
            $requestModel = new QuoteRequestModel();
            $reference = $this->referenceNumber();
            $requestId = (int) $requestModel->insert($customer + [
                'reference_number' => $reference,
                'status' => 'new',
                'owner_notification_status' => 'pending',
                'customer_notification_status' => 'pending',
                'submitted_at' => date('Y-m-d H:i:s'),
            ]);
            if ($requestId < 1) {
                throw new RuntimeException('The quote request could not be created.');
            }

            $serviceModel = new QuoteRequestServiceModel();
            foreach ($services as $index => $service) {
                $serviceModel->insert([
                    'quote_request_id' => $requestId,
                    'service_id' => $service['id'],
                    'service_name' => $service['name'],
                    'service_slug' => $service['slug'],
                    'service_summary' => $service['summary'],
                    'service_description' => $service['description'],
                    'starting_price' => $service['starting_price'],
                    'currency_code' => $service['currency_code'],
                    'show_price' => $service['show_price'],
                    'sort_order' => $index,
                ]);
            }

            $answerModel = new QuoteRequestAnswerModel();
            $fileModel = new QuoteRequestFileModel();
            $answerOrder = 0;
            foreach ($catalog as $group) {
                foreach ($group['questions'] as $question) {
                    $questionId = (int) $question['id'];
                    $type = (string) $question['field_type'];
                    $value = $answers[$questionId] ?? ($type === 'checkboxes' ? [] : '');
                    $answerText = $this->answerText($question, $value);
                    $answerJson = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                    $file = $files[$questionId] ?? null;
                    if ($type === 'file' && $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                        $storedName = bin2hex(random_bytes(16)) . '.' . strtolower($file->getClientExtension());
                        $relativeDirectory = 'quote_requests/' . $reference;
                        $absoluteDirectory = WRITEPATH . 'uploads/' . $relativeDirectory;
                        if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0700, true) && ! is_dir($absoluteDirectory)) {
                            throw new RuntimeException('The attachment directory could not be created.');
                        }
                        $file->move($absoluteDirectory, $storedName);
                        $relativePath = $relativeDirectory . '/' . $storedName;
                        $movedPaths[] = WRITEPATH . 'uploads/' . $relativePath;
                        $answerText = $file->getClientName();
                        $answerJson = json_encode($file->getClientName());
                        $fileModel->insert([
                            'quote_request_id' => $requestId,
                            'question_id' => $questionId,
                            'original_name' => mb_substr($file->getClientName(), 0, 255),
                            'stored_name' => $storedName,
                            'relative_path' => $relativePath,
                            'mime_type' => $file->getMimeType(),
                            'extension' => strtolower($file->getClientExtension()),
                            'size_bytes' => $file->getSize(),
                        ]);
                    }

                    $answerModel->insert([
                        'quote_request_id' => $requestId,
                        'question_id' => $questionId,
                        'group_name' => $group['name'],
                        'question_label' => $question['label'],
                        'field_type' => $type,
                        'answer_text' => $answerText !== '' ? $answerText : null,
                        'answer_json' => $answerJson !== false ? $answerJson : null,
                        'sort_order' => $answerOrder++,
                    ]);
                }
            }

            if (! $db->transStatus()) {
                throw new RuntimeException('The quote request could not be saved.');
            }
            $db->transCommit();

            return $requestModel->find($requestId);
        } catch (Throwable $exception) {
            $db->transRollback();
            foreach ($movedPaths as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            throw $exception;
        }
    }

    /** @param array<string, mixed> $question @param string|list<string> $value */
    private function answerText(array $question, string|array $value): string
    {
        if (in_array($question['field_type'], ['select', 'radio', 'checkboxes'], true)) {
            $labels = array_column($question['options'], 'label', 'id');
            $values = is_array($value) ? $value : [$value];

            return implode(', ', array_values(array_filter(array_map(
                static fn (string $id): ?string => isset($labels[$id]) ? (string) $labels[$id] : null,
                array_map('strval', $values),
            ))));
        }

        if ($question['field_type'] === 'yes_no' && is_string($value)) {
            return ucfirst($value);
        }

        return is_array($value) ? implode(', ', $value) : $value;
    }

    private function fileError(UploadedFile $file): ?string
    {
        if (! $file->isValid()) {
            return 'The upload did not complete successfully.';
        }
        if ($file->getSize() > self::MAX_FILE_BYTES) {
            return 'Each file must be 8 MB or less.';
        }
        $extension = strtolower($file->getClientExtension());
        $allowedMimes = self::ALLOWED_FILES[$extension] ?? [];
        if ($allowedMimes === [] || ! in_array($file->getMimeType(), $allowedMimes, true)) {
            return 'Use a PDF, JPG, PNG, DOC, or DOCX file.';
        }

        return null;
    }

    /** @param string|list<string> $value */
    private function isEmpty(string|array $value): bool
    {
        return is_array($value) ? $value === [] : trim($value) === '';
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function referenceNumber(): string
    {
        $model = new QuoteRequestModel();
        do {
            $reference = 'PR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(6)));
        } while ($model->where('reference_number', $reference)->first() !== null);

        return $reference;
    }
}
