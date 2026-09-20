<?php

namespace App\Libraries;

use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use App\Models\QuoteStatusHistoryModel;
use App\Models\QuoteVersionModel;
use RuntimeException;
use Throwable;

class QuoteManager
{
    public const MAX_ITEMS = 50;

    /** @param array<string, mixed> $business @return array<string, mixed> */
    public function createFromRequest(int $requestId, array $business): array
    {
        $quoteModel = new QuoteModel();
        $existing = $quoteModel->where('quote_request_id', $requestId)->first();
        if ($existing !== null) {
            return $existing;
        }

        $request = (new QuoteRequestModel())->find($requestId);
        if ($request === null || ! in_array($request['status'], QuoteRequestModel::QUOTEABLE_STATUSES, true)) {
            throw new RuntimeException('Only requests ready to quote can be converted into quotes.');
        }

        $services = (new QuoteRequestServiceModel())
            ->where('quote_request_id', $requestId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        $db = db_connect();
        $db->transBegin();

        try {
            $customer = (new CustomerRelationships())->findOrCreateForRequest($request);
            $customerId = (int) $customer['id'];
            $quoteDate = date('Y-m-d');
            $expirationDays = max(1, min(365, (int) ($business['default_quote_expiration_days'] ?? 30)));
            $quoteId = (int) $quoteModel->insert([
                'quote_number' => 'PENDING-' . strtoupper(bin2hex(random_bytes(6))),
                'access_token' => bin2hex(random_bytes(32)),
                'customer_id' => $customerId,
                'quote_request_id' => $requestId,
                'customer_name' => $request['name'],
                'customer_business_name' => $request['company'],
                'customer_email' => $request['email'],
                'customer_phone' => $request['phone'],
                'quote_date' => $quoteDate,
                'expires_on' => date('Y-m-d', strtotime('+' . $expirationDays . ' days')),
                'currency_code' => $services[0]['currency_code'] ?? 'USD',
                'status' => 'draft',
                'terms' => $business['default_quote_terms'] ?? null,
                'notes' => null,
                'deposit_percentage' => $business['default_deposit_percentage'] ?? '50.00',
                'delivery_status' => 'pending',
                'version' => 1,
            ]);
            $quoteModel->update($quoteId, ['quote_number' => sprintf('Q-%s-%06d', date('Y'), $quoteId)]);

            $items = [];
            foreach ($services as $service) {
                $items[] = [
                    'description' => $service['service_name'],
                    'details' => $service['service_summary'],
                    'quantity' => '1.00',
                    'unit_price' => $service['show_price'] && $service['starting_price'] !== null ? $service['starting_price'] : '0.00',
                ];
            }
            if ($items === []) {
                $items[] = ['description' => 'Design services', 'details' => null, 'quantity' => '1.00', 'unit_price' => '0.00'];
            }
            $totals = $this->totals($items, 0.0, 0.0, (float) ($business['default_deposit_percentage'] ?? 50));
            $this->replaceItems($quoteId, $items);
            $quoteModel->update($quoteId, $totals);
            (new QuoteStatusHistoryModel())->insert(['quote_id' => $quoteId, 'from_status' => null, 'to_status' => 'draft', 'note' => 'Quote created from request.']);
            (new QuoteRequestModel())->update($requestId, ['status' => 'closed_quote_created']);
            $this->recordVersion($quoteId, 1);

            if (! $db->transStatus()) {
                throw new RuntimeException('The quote could not be created.');
            }
            $db->transCommit();

            return $quoteModel->find($quoteId);
        } catch (Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    /** @param array<string, mixed> $data @param mixed $submittedItems @return array<string, string> */
    public function validate(array $data, mixed $submittedItems): array
    {
        $errors = [];
        foreach (['quote_date', 'expires_on'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field]) || ! $this->validDate($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date.';
            }
        }
        if (! isset($errors['quote_date']) && ! isset($errors['expires_on']) && $data['expires_on'] < $data['quote_date']) {
            $errors['expires_on'] = 'Expiration must be on or after the quote date.';
        }
        if (! is_string($data['currency_code'] ?? null) || preg_match('/^[A-Z]{3}$/', $data['currency_code']) !== 1) {
            $errors['currency_code'] = 'Currency must be a three-letter code.';
        }
        if (! is_string($data['status'] ?? null) || ! isset(QuoteModel::STATUSES[$data['status']])) {
            $errors['status'] = 'Choose a valid quote status.';
        }
        foreach (['discount_amount' => 10000000, 'tax_rate' => 100, 'deposit_percentage' => 100] as $field => $maximum) {
            $value = $data[$field] ?? null;
            if (! is_scalar($value) || ! is_numeric((string) $value) || (float) $value < 0 || (float) $value > $maximum) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is outside the allowed range.';
            }
        }
        if (mb_strlen((string) ($data['terms'] ?? '')) > 10000) {
            $errors['terms'] = 'Terms must be 10,000 characters or fewer.';
        }
        if (mb_strlen((string) ($data['notes'] ?? '')) > 5000) {
            $errors['notes'] = 'Notes must be 5,000 characters or fewer.';
        }

        if (! is_array($submittedItems) || $submittedItems === [] || count($submittedItems) > self::MAX_ITEMS) {
            $errors['items'] = 'Add between 1 and ' . self::MAX_ITEMS . ' line items.';
            return $errors;
        }
        foreach (array_values($submittedItems) as $index => $item) {
            $number = $index + 1;
            if (! is_array($item) || trim((string) ($item['description'] ?? '')) === '' || mb_strlen((string) ($item['description'] ?? '')) > 200) {
                $errors['items.' . $index . '.description'] = 'Line ' . $number . ' needs a description of 200 characters or fewer.';
            }
            if (mb_strlen((string) ($item['details'] ?? '')) > 1000) {
                $errors['items.' . $index . '.details'] = 'Line ' . $number . ' details must be 1,000 characters or fewer.';
            }
            foreach (['quantity' => 9999.99, 'unit_price' => 10000000] as $field => $maximum) {
                $value = $item[$field] ?? null;
                $minimum = $field === 'quantity' ? 0.01 : 0;
                if (! is_scalar($value) || ! is_numeric((string) $value) || (float) $value < $minimum || (float) $value > $maximum) {
                    $errors['items.' . $index . '.' . $field] = 'Line ' . $number . ' has an invalid ' . str_replace('_', ' ', $field) . '.';
                }
            }
        }

        return $errors;
    }

    /** @param array<string, mixed> $data @param list<array<string, mixed>> $submittedItems @return array<string, mixed> */
    public function update(int $quoteId, array $data, array $submittedItems): array
    {
        $quoteModel = new QuoteModel();
        $quote = $quoteModel->find($quoteId);
        if ($quote === null) {
            throw new RuntimeException('Quote not found.');
        }
        if ($quote['responded_at'] !== null) {
            throw new RuntimeException('Customer-responded quotes are locked. Manage delivery work through the linked project.');
        }

        $items = [];
        foreach (array_values($submittedItems) as $item) {
            $items[] = [
                'description' => mb_substr(trim((string) $item['description']), 0, 200),
                'details' => trim((string) ($item['details'] ?? '')) ?: null,
                'quantity' => number_format((float) $item['quantity'], 2, '.', ''),
                'unit_price' => number_format((float) $item['unit_price'], 2, '.', ''),
            ];
        }
        $totals = $this->totals($items, (float) $data['discount_amount'], (float) $data['tax_rate'], (float) $data['deposit_percentage']);
        if ((float) $data['discount_amount'] > (float) $totals['subtotal']) {
            throw new RuntimeException('Discount cannot exceed the subtotal.');
        }

        $nextVersion = (int) $quote['version'] + 1;
        $update = [
            'quote_date' => $data['quote_date'],
            'expires_on' => $data['expires_on'],
            'currency_code' => strtoupper((string) $data['currency_code']),
            'status' => $data['status'],
            'discount_amount' => $data['discount_amount'],
            'tax_rate' => $data['tax_rate'],
            'deposit_percentage' => $data['deposit_percentage'],
            'terms' => trim((string) ($data['terms'] ?? '')) ?: null,
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            'version' => $nextVersion,
        ] + $totals;

        $db = db_connect();
        $db->transBegin();
        try {
            $quoteModel->update($quoteId, $update);
            $this->replaceItems($quoteId, $items);
            if ($quote['status'] !== $data['status']) {
                (new QuoteStatusHistoryModel())->insert([
                    'quote_id' => $quoteId,
                    'from_status' => $quote['status'],
                    'to_status' => $data['status'],
                    'note' => 'Status changed while editing quote.',
                ]);
                $this->markRequestQuoteCreated($quote['quote_request_id']);
            }
            $this->recordVersion($quoteId, $nextVersion);
            if (! $db->transStatus()) {
                throw new RuntimeException('The quote could not be saved.');
            }
            $db->transCommit();

            return $quoteModel->find($quoteId);
        } catch (Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    /** @return array<string, mixed> */
    public function recordDelivery(int $quoteId, string $deliveryStatus): array
    {
        $model = new QuoteModel();
        $quote = $model->find($quoteId);
        if ($quote === null) {
            throw new RuntimeException('Quote not found.');
        }

        $update = ['delivery_status' => $deliveryStatus];
        if ($deliveryStatus === 'sent') {
            $update += ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s'), 'version' => (int) $quote['version'] + 1];
        }
        $model->update($quoteId, $update);
        if ($deliveryStatus === 'sent' && $quote['status'] !== 'sent') {
            (new QuoteStatusHistoryModel())->insert([
                'quote_id' => $quoteId,
                'from_status' => $quote['status'],
                'to_status' => 'sent',
                'note' => 'Quote delivered by email.',
            ]);
            $this->markRequestQuoteCreated($quote['quote_request_id']);
            $this->recordVersion($quoteId, (int) $quote['version'] + 1);
        }

        return $model->find($quoteId);
    }

    /** @param list<array<string, mixed>> $items @return array<string, string> */
    private function totals(array $items, float $discount, float $taxRate, float $depositPercentage): array
    {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += round((float) $item['quantity'] * (float) $item['unit_price'], 2);
        }
        $subtotal = round($subtotal, 2);
        $discount = min(round($discount, 2), $subtotal);
        $tax = round(($subtotal - $discount) * ($taxRate / 100), 2);
        $total = round($subtotal - $discount + $tax, 2);
        $deposit = round($total * ($depositPercentage / 100), 2);

        return [
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'discount_amount' => number_format($discount, 2, '.', ''),
            'tax_rate' => number_format($taxRate, 3, '.', ''),
            'tax_amount' => number_format($tax, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'deposit_percentage' => number_format($depositPercentage, 2, '.', ''),
            'deposit_amount' => number_format($deposit, 2, '.', ''),
        ];
    }

    /** @param list<array<string, mixed>> $items */
    private function replaceItems(int $quoteId, array $items): void
    {
        db_connect()->table('quote_line_items')->where('quote_id', $quoteId)->delete();
        $model = new QuoteLineItemModel();
        foreach ($items as $index => $item) {
            $model->insert([
                'quote_id' => $quoteId,
                'description' => $item['description'],
                'details' => $item['details'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => number_format(round((float) $item['quantity'] * (float) $item['unit_price'], 2), 2, '.', ''),
                'sort_order' => $index,
            ]);
        }
    }

    public function recordVersion(int $quoteId, int $version): void
    {
        $quote = (new QuoteModel())->find($quoteId);
        $items = (new QuoteLineItemModel())->where('quote_id', $quoteId)->orderBy('sort_order', 'ASC')->findAll();
        (new QuoteVersionModel())->insert([
            'quote_id' => $quoteId,
            'version_number' => $version,
            'snapshot_json' => json_encode(['quote' => $quote, 'items' => $items], JSON_THROW_ON_ERROR),
        ]);
    }

    private function markRequestQuoteCreated(mixed $requestId): void
    {
        if (! is_numeric($requestId)) {
            return;
        }
        (new QuoteRequestModel())->update((int) $requestId, ['status' => 'closed_quote_created']);
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
