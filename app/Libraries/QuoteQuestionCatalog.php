<?php

namespace App\Libraries;

use App\Models\QuestionGroupServiceModel;
use App\Models\QuoteQuestionGroupModel;
use App\Models\QuoteQuestionModel;
use App\Models\QuoteQuestionOptionModel;

class QuoteQuestionCatalog
{
    /**
     * Return active general groups and groups assigned to at least one selected service.
     *
     * @param list<int> $serviceIds
     * @return list<array<string, mixed>>
     */
    public function forServices(array $serviceIds): array
    {
        $selected = array_fill_keys(array_values(array_unique(array_filter(
            array_map('intval', $serviceIds),
            static fn (int $id): bool => $id > 0,
        ))), true);
        $assignments = [];
        foreach ((new QuestionGroupServiceModel())->findAll() as $assignment) {
            $assignments[(int) $assignment['group_id']][] = (int) $assignment['service_id'];
        }

        $groups = (new QuoteQuestionGroupModel())
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
        $catalog = [];

        foreach ($groups as $group) {
            $serviceAssignments = $assignments[(int) $group['id']] ?? [];
            if ($serviceAssignments !== [] && ! array_filter($serviceAssignments, static fn (int $id): bool => isset($selected[$id]))) {
                continue;
            }

            $questions = (new QuoteQuestionModel())
                ->where('group_id', $group['id'])
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();
            foreach ($questions as &$question) {
                $question['options'] = (new QuoteQuestionOptionModel())
                    ->where('question_id', $question['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->findAll();
            }
            unset($question);

            if ($questions !== []) {
                $group['questions'] = $questions;
                $catalog[] = $group;
            }
        }

        return $catalog;
    }
}
