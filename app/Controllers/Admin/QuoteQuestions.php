<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\QuoteQuestionGroupModel;
use App\Models\QuoteQuestionModel;
use App\Models\QuoteQuestionOptionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class QuoteQuestions extends BaseController
{
    private array $inputErrors = [];

    public function create(int $groupId): string
    {
        return $this->form($this->findGroup($groupId), null);
    }

    public function edit(int $groupId, int $id): string
    {
        return $this->form($this->findGroup($groupId), $this->findQuestion($groupId, $id));
    }

    public function store(int $groupId): RedirectResponse
    {
        $this->findGroup($groupId);
        $input = $this->validatedInput();
        if ($input === null) {
            return redirect()->back()->withInput()->with('errors', $this->inputErrors);
        }

        $db = db_connect();
        $db->transStart();
        $questionId = (int) (new QuoteQuestionModel())->insert(['group_id' => $groupId] + $input['question']);
        $this->syncOptions($questionId, $input['options']);
        $db->transComplete();

        return redirect()->to('/admin/question-groups/' . $groupId . '/edit')->with('message', 'Question created.');
    }

    public function update(int $groupId, int $id): RedirectResponse
    {
        $this->findGroup($groupId);
        $this->findQuestion($groupId, $id);
        $input = $this->validatedInput();
        if ($input === null) {
            return redirect()->back()->withInput()->with('errors', $this->inputErrors);
        }

        $db = db_connect();
        $db->transStart();
        (new QuoteQuestionModel())->update($id, $input['question']);
        $this->syncOptions($id, $input['options']);
        $db->transComplete();

        return redirect()->to('/admin/question-groups/' . $groupId . '/edit')->with('message', 'Question saved.');
    }

    /** @param array<string, mixed> $group
     *  @param array<string, mixed>|null $question
     */
    private function form(array $group, ?array $question): string
    {
        $options = $question === null ? [] : (new QuoteQuestionOptionModel())
            ->where('question_id', $question['id'])
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('admin/questions/form', [
            'businessName' => (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio',
            'group' => $group,
            'question' => $question,
            'fieldTypes' => QuoteQuestionModel::FIELD_TYPES,
            'optionsText' => implode("\n", array_column($options, 'label')),
        ]);
    }

    /** @return array<string, mixed> */
    private function findGroup(int $id): array
    {
        $group = (new QuoteQuestionGroupModel())->find($id);
        if ($group === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $group;
    }

    /** @return array<string, mixed> */
    private function findQuestion(int $groupId, int $id): array
    {
        $question = (new QuoteQuestionModel())->where('id', $id)->where('group_id', $groupId)->first();
        if ($question === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $question;
    }

    /** @return array{question: array<string, mixed>, options: list<string>}|null */
    private function validatedInput(): ?array
    {
        $rules = [
            'label' => 'required|max_length[180]',
            'field_type' => 'required|in_list[' . implode(',', array_keys(QuoteQuestionModel::FIELD_TYPES)) . ']',
            'help_text' => 'permit_empty|max_length[500]',
            'placeholder' => 'permit_empty|max_length[180]',
            'sort_order' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[9999]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        $data['is_required'] = $this->request->getPost('is_required') === '1' ? 1 : 0;
        $data['is_active'] = $this->request->getPost('is_active') === '1' ? 1 : 0;

        if (! $this->validateData($data, $rules)) {
            $this->inputErrors = $this->validator->getErrors();
            return null;
        }

        $options = $this->validatedOptions($data['field_type']);
        if ($options === false) {
            return null;
        }

        $data['help_text'] = $data['help_text'] === '' ? null : $data['help_text'];
        $data['placeholder'] = $data['placeholder'] === '' ? null : $data['placeholder'];
        $data['sort_order'] = (int) $data['sort_order'];

        return ['question' => $data, 'options' => $options];
    }

    /** @return list<string>|false */
    private function validatedOptions(string $fieldType): array|false
    {
        if (! in_array($fieldType, QuoteQuestionModel::CHOICE_TYPES, true)) {
            return [];
        }

        $raw = $this->request->getPost('options');
        if (! is_string($raw)) {
            $this->inputErrors['options'] = 'Enter at least two choices, one per line.';
            return false;
        }
        if (mb_strlen($raw) > 9050) {
            $this->inputErrors['options'] = 'Choice options are too long.';
            return false;
        }

        $options = [];
        $seen = [];
        foreach (preg_split('/\R/u', $raw) ?: [] as $line) {
            $label = trim($line);
            if ($label === '') {
                continue;
            }
            if (mb_strlen($label) > 180) {
                $this->inputErrors['options'] = 'Each choice must be 180 characters or fewer.';
                return false;
            }
            $key = mb_strtolower($label);
            if (isset($seen[$key])) {
                $this->inputErrors['options'] = 'Each choice must be distinct.';
                return false;
            }
            $options[] = $label;
            $seen[$key] = true;
        }

        if (count($options) < 2 || count($options) > 50) {
            $this->inputErrors['options'] = 'Enter between 2 and 50 choices, one per line.';
            return false;
        }

        return $options;
    }

    /** @param list<string> $options */
    private function syncOptions(int $questionId, array $options): void
    {
        $model = new QuoteQuestionOptionModel();
        $model->where('question_id', $questionId)->delete();
        foreach ($options as $sortOrder => $label) {
            $model->insert([
                'question_id' => $questionId,
                'label' => $label,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
