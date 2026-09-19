<?php

namespace App\Libraries;

use App\Models\ServiceModel;

class QuoteCart
{
    private const SERVICE_KEY = 'quote_cart_service_ids';
    private const ANSWER_KEY = 'quote_cart_answers';
    private const MAX_SERVICES = 25;
    private const MAX_ANSWERS = 200;

    /** @return list<int> */
    public function serviceIds(): array
    {
        $stored = session()->get(self::SERVICE_KEY);
        if (! is_array($stored)) {
            return [];
        }

        $ids = [];
        foreach ($stored as $value) {
            if ((is_int($value) || (is_string($value) && ctype_digit($value))) && (int) $value > 0) {
                $ids[(int) $value] = (int) $value;
            }
            if (count($ids) >= self::MAX_SERVICES) {
                break;
            }
        }
        $ids = array_values($ids);
        if ($ids === []) {
            session()->remove(self::SERVICE_KEY);
            return [];
        }

        $published = (new ServiceModel())->select('id')->where('status', 'published')->whereIn('id', $ids)->findAll();
        $available = array_fill_keys(array_map('intval', array_column($published, 'id')), true);
        $valid = array_values(array_filter($ids, static fn (int $id): bool => isset($available[$id])));
        if ($valid !== $ids) {
            $this->storeServiceIds($valid);
        }

        return $valid;
    }

    /** @return list<array<string, mixed>> */
    public function services(): array
    {
        $ids = $this->serviceIds();
        if ($ids === []) {
            return [];
        }

        $records = (new ServiceModel())->withImage()->where('services.status', 'published')->whereIn('services.id', $ids)->findAll();
        $byId = array_column($records, null, 'id');

        return array_values(array_filter(array_map(
            static fn (int $id): ?array => $byId[$id] ?? null,
            $ids,
        )));
    }

    public function count(): int
    {
        return count($this->serviceIds());
    }

    public function add(int $serviceId): bool
    {
        $service = (new ServiceModel())->where('id', $serviceId)->where('status', 'published')->first();
        if ($service === null) {
            return false;
        }

        $ids = $this->serviceIds();
        if (in_array($serviceId, $ids, true)) {
            return true;
        }
        if (count($ids) >= self::MAX_SERVICES) {
            return false;
        }

        $ids[] = $serviceId;
        $this->storeServiceIds($ids);

        return true;
    }

    public function remove(int $serviceId): void
    {
        $this->storeServiceIds(array_values(array_filter(
            $this->serviceIds(),
            static fn (int $id): bool => $id !== $serviceId,
        )));
    }

    /** @return array<int, string|list<string>> */
    public function answers(): array
    {
        $answers = session()->get(self::ANSWER_KEY);

        return is_array($answers) ? array_slice($answers, 0, self::MAX_ANSWERS, true) : [];
    }

    /**
     * @param mixed $submitted
     * @param list<array<string, mixed>> $catalog
     */
    public function saveAnswers(mixed $submitted, array $catalog): void
    {
        if (! is_array($submitted)) {
            return;
        }

        $answers = $this->answers();
        $known = 0;
        foreach ($catalog as $group) {
            foreach ($group['questions'] as $question) {
                if (++$known > self::MAX_ANSWERS) {
                    break 2;
                }
                $id = (int) $question['id'];
                $value = $submitted[$id] ?? null;
                $answers[$id] = $this->sanitizeAnswer($question, $value);
            }
        }

        session()->set(self::ANSWER_KEY, $answers);
    }

    public function clear(): void
    {
        session()->remove([self::SERVICE_KEY, self::ANSWER_KEY]);
    }

    /** @param list<int> $ids */
    private function storeServiceIds(array $ids): void
    {
        if ($ids === []) {
            session()->remove(self::SERVICE_KEY);
            return;
        }

        session()->set(self::SERVICE_KEY, array_slice($ids, 0, self::MAX_SERVICES));
    }

    /** @param array<string, mixed> $question
     *  @return string|list<string>
     */
    private function sanitizeAnswer(array $question, mixed $value): string|array
    {
        $type = $question['field_type'];
        if ($type === 'file') {
            return '';
        }

        if (in_array($type, ['select', 'radio', 'checkboxes'], true)) {
            $allowed = array_fill_keys(array_map('strval', array_column($question['options'], 'id')), true);
            $values = $type === 'checkboxes' && is_array($value) ? $value : [$value];
            $selected = [];
            foreach ($values as $optionId) {
                if (is_scalar($optionId) && isset($allowed[(string) $optionId])) {
                    $selected[(string) $optionId] = (string) $optionId;
                }
            }

            return $type === 'checkboxes' ? array_values($selected) : (array_values($selected)[0] ?? '');
        }

        if ($type === 'yes_no') {
            return in_array($value, ['yes', 'no'], true) ? $value : '';
        }

        if (! is_scalar($value)) {
            return '';
        }

        $limit = $type === 'textarea' ? 5000 : 500;

        return mb_substr(trim((string) $value), 0, $limit);
    }
}
