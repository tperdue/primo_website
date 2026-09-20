<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ProjectManager;
use App\Models\BusinessSettingsModel;
use App\Models\CustomerModel;
use App\Models\ProjectModel;
use App\Models\QuoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use RuntimeException;
use Throwable;

class Projects extends BaseController
{
    public function index(): string
    {
        return view('admin/projects/index', [
            'businessName' => $this->businessName(),
            'projects' => (new ProjectModel())->withRelationships()->orderBy('projects.updated_at', 'DESC')->findAll(),
            'statuses' => ProjectModel::STATUSES,
        ]);
    }

    public function createFromQuote(int $quoteId): RedirectResponse
    {
        try {
            $project = (new ProjectManager())->createFromQuote($quoteId);
        } catch (Throwable $exception) {
            log_message('error', 'Project creation failed for quote {id}: {message}', ['id' => $quoteId, 'message' => $exception->getMessage()]);
            $message = $exception instanceof RuntimeException ? $exception->getMessage() : 'The project could not be created.';

            return redirect()->to('/admin/quotes/' . $quoteId . '/edit')->with('errors', [$message]);
        }

        return redirect()->to('/admin/projects/' . $project['id'] . '/edit')->with('message', 'Project created from the accepted quote.');
    }

    public function edit(int $id): string
    {
        $project = $this->findProject($id);

        return view('admin/projects/form', [
            'businessName' => $this->businessName(),
            'project' => $project,
            'quote' => (new QuoteModel())->find($project['quote_id']),
            'customer' => (new CustomerModel())->find($project['customer_id']),
            'statuses' => ProjectModel::STATUSES,
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findProject($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ($data['start_date'] !== null && $data['due_date'] !== null && $data['due_date'] < $data['start_date']) {
            return redirect()->back()->withInput()->with('errors', ['due_date' => 'Due date must be on or after the start date.']);
        }

        (new ProjectModel())->update($id, $data);

        return redirect()->to('/admin/projects/' . $id . '/edit')->with('message', 'Project saved.');
    }

    /** @return array<string, mixed> */
    private function findProject(int $id): array
    {
        $project = (new ProjectModel())->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $project;
    }

    /** @return array<string, mixed>|null */
    private function validatedInput(): ?array
    {
        $rules = [
            'name' => 'required|max_length[180]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'due_date' => 'permit_empty|valid_date[Y-m-d]',
            'status' => 'required|in_list[' . implode(',', array_keys(ProjectModel::STATUSES)) . ']',
            'notes' => 'permit_empty|max_length[10000]',
            'customer_update' => 'permit_empty|max_length[5000]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        if (! $this->validateData($data, $rules)) {
            return null;
        }
        foreach (['start_date', 'due_date', 'notes', 'customer_update'] as $nullable) {
            $data[$nullable] = $data[$nullable] === '' ? null : $data[$nullable];
        }

        return $data;
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
