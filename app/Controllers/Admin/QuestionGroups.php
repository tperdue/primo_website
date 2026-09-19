<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\QuestionGroupServiceModel;
use App\Models\QuoteQuestionGroupModel;
use App\Models\QuoteQuestionModel;
use App\Models\QuoteQuestionOptionModel;
use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class QuestionGroups extends BaseController
{
    public function index(): string
    {
        $groups = (new QuoteQuestionGroupModel())->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll();
        foreach ($groups as &$group) {
            $group['question_count'] = (new QuoteQuestionModel())->where('group_id', $group['id'])->countAllResults();
            $group['active_question_count'] = (new QuoteQuestionModel())
                ->where('group_id', $group['id'])->where('is_active', 1)->countAllResults();
            $group['service_count'] = (new QuestionGroupServiceModel())->where('group_id', $group['id'])->countAllResults();
        }
        unset($group);

        return view('admin/question_groups/index', [
            'businessName' => $this->businessName(),
            'groups' => $groups,
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function edit(int $id): string
    {
        return $this->form($this->findGroup($id));
    }

    public function store(): RedirectResponse
    {
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $serviceIds = $this->validatedServiceIds();
        if ($serviceIds === false) {
            return redirect()->back()->withInput()->with('errors', ['service_ids' => 'Choose services from the catalog.']);
        }
        if ($this->nameExists($data['name'])) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Choose a distinct group name.']);
        }

        $db = db_connect();
        $db->transStart();
        $id = (int) (new QuoteQuestionGroupModel())->insert($data);
        $this->syncServices($id, $serviceIds);
        $db->transComplete();

        return redirect()->to('/admin/question-groups/' . $id . '/edit')->with('message', 'Question group created. Add its questions below.');
    }

    public function update(int $id): RedirectResponse
    {
        $this->findGroup($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $serviceIds = $this->validatedServiceIds();
        if ($serviceIds === false) {
            return redirect()->back()->withInput()->with('errors', ['service_ids' => 'Choose services from the catalog.']);
        }
        if ($this->nameExists($data['name'], $id)) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Choose a distinct group name.']);
        }

        $db = db_connect();
        $db->transStart();
        (new QuoteQuestionGroupModel())->update($id, $data);
        $this->syncServices($id, $serviceIds);
        $db->transComplete();

        return redirect()->to('/admin/question-groups/' . $id . '/edit')->with('message', 'Question group saved.');
    }

    /** @param array<string, mixed>|null $group */
    private function form(?array $group): string
    {
        $serviceIds = [];
        $questions = [];
        if ($group !== null) {
            $serviceIds = array_map('intval', array_column(
                (new QuestionGroupServiceModel())->where('group_id', $group['id'])->findAll(),
                'service_id',
            ));
            $questions = (new QuoteQuestionModel())
                ->where('group_id', $group['id'])
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();
            foreach ($questions as &$question) {
                $question['option_count'] = (new QuoteQuestionOptionModel())
                    ->where('question_id', $question['id'])->countAllResults();
            }
            unset($question);
        }

        return view('admin/question_groups/form', [
            'businessName' => $this->businessName(),
            'group' => $group,
            'services' => (new ServiceModel())->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(),
            'selectedServiceIds' => $serviceIds,
            'questions' => $questions,
            'fieldTypes' => QuoteQuestionModel::FIELD_TYPES,
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

    /** @return array<string, mixed>|null */
    private function validatedInput(): ?array
    {
        $rules = [
            'name' => 'required|max_length[120]',
            'description' => 'permit_empty|max_length[500]',
            'sort_order' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[9999]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        $data['is_active'] = $this->request->getPost('is_active') === '1' ? 1 : 0;

        if (! $this->validateData($data, $rules)) {
            return null;
        }

        $data['description'] = $data['description'] === '' ? null : $data['description'];
        $data['sort_order'] = (int) $data['sort_order'];

        return $data;
    }

    /** @return list<int>|false */
    private function validatedServiceIds(): array|false
    {
        $values = $this->request->getPost('service_ids') ?? [];
        if (! is_array($values)) {
            return false;
        }

        $serviceIds = [];
        $seen = [];
        foreach ($values as $value) {
            if (! is_string($value) || ! ctype_digit($value) || (int) $value < 1 || (new ServiceModel())->find((int) $value) === null) {
                return false;
            }
            if (! isset($seen[$value])) {
                $serviceIds[] = (int) $value;
                $seen[$value] = true;
            }
        }

        return $serviceIds;
    }

    private function nameExists(string $name, ?int $exceptId = null): bool
    {
        $normalized = mb_strtolower($name);
        foreach ((new QuoteQuestionGroupModel())->findAll() as $group) {
            if (($exceptId === null || (int) $group['id'] !== $exceptId) && mb_strtolower($group['name']) === $normalized) {
                return true;
            }
        }

        return false;
    }

    /** @param list<int> $serviceIds */
    private function syncServices(int $groupId, array $serviceIds): void
    {
        $model = new QuestionGroupServiceModel();
        $model->where('group_id', $groupId)->delete();
        foreach ($serviceIds as $serviceId) {
            $model->insert(['group_id' => $groupId, 'service_id' => $serviceId]);
        }
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
