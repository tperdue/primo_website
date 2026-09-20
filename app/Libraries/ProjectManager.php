<?php

namespace App\Libraries;

use App\Models\ProjectModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use RuntimeException;
use Throwable;

class ProjectManager
{
    /** @return array<string, mixed> */
    public function createFromQuote(int $quoteId): array
    {
        $projectModel = new ProjectModel();
        $existing = $projectModel->where('quote_id', $quoteId)->first();
        if ($existing !== null) {
            return $existing;
        }

        $quote = (new QuoteModel())->find($quoteId);
        if ($quote === null || $quote['status'] !== 'accepted') {
            throw new RuntimeException('Only accepted quotes can be converted into projects.');
        }
        $firstItem = (new QuoteLineItemModel())->where('quote_id', $quoteId)->orderBy('sort_order', 'ASC')->first();
        $customer = trim((string) ($quote['customer_business_name'] ?: $quote['customer_name']));
        $scope = trim((string) ($firstItem['description'] ?? 'Design project'));
        $name = mb_substr($scope . ' for ' . $customer, 0, 180);

        $db = db_connect();
        $db->transBegin();
        try {
            $projectId = (int) $projectModel->insert([
                'project_number' => 'PENDING-' . strtoupper(bin2hex(random_bytes(6))),
                'quote_id' => $quoteId,
                'customer_id' => $quote['customer_id'],
                'name' => $name,
                'start_date' => null,
                'due_date' => null,
                'status' => (float) $quote['deposit_amount'] > 0 ? 'awaiting_deposit' : 'scheduled',
                'notes' => null,
                'customer_update' => null,
            ]);
            $projectModel->update($projectId, ['project_number' => sprintf('PRJ-%s-%06d', date('Y'), $projectId)]);
            if (! $db->transStatus()) {
                throw new RuntimeException('The project could not be created.');
            }
            $db->transCommit();

            return $projectModel->find($projectId);
        } catch (Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }
}
