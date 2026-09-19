<?php

namespace App\Libraries;

use App\Models\CustomerModel;
use App\Models\QuoteRequestModel;
use RuntimeException;

class CustomerRelationships
{
    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /** @param array<string, mixed> $request @return array<string, mixed> */
    public function findOrCreateForRequest(array $request): array
    {
        $requestModel = new QuoteRequestModel();
        if (! empty($request['customer_id'])) {
            $linked = (new CustomerModel())->find((int) $request['customer_id']);
            if ($linked !== null) {
                return $linked;
            }
        }

        $normalizedEmail = self::normalizeEmail((string) $request['email']);
        $customerModel = new CustomerModel();
        $customer = $customerModel->where('normalized_email', $normalizedEmail)->first();
        if ($customer === null) {
            $customerId = (int) $customerModel->insert([
                'name' => $request['name'],
                'business_name' => $request['company'],
                'email' => $normalizedEmail,
                'normalized_email' => $normalizedEmail,
                'phone' => $request['phone'],
                'notes' => 'Created from quote request ' . $request['reference_number'] . '.',
            ]);
            if ($customerId < 1) {
                throw new RuntimeException('The customer record could not be created.');
            }
            $customer = $customerModel->find($customerId);
        }

        $this->linkMatchingRequests((int) $customer['id'], $normalizedEmail);
        if (($request['normalized_email'] ?? null) !== $normalizedEmail || (int) ($request['customer_id'] ?? 0) !== (int) $customer['id']) {
            $requestModel->update((int) $request['id'], [
                'normalized_email' => $normalizedEmail,
                'customer_id' => $customer['id'],
            ]);
        }

        return $customer;
    }

    /** @param array<string, mixed> $request @return array<string, mixed>|null */
    public function linkToExistingCustomer(array $request): ?array
    {
        $normalizedEmail = self::normalizeEmail((string) $request['email']);
        $customer = (new CustomerModel())->where('normalized_email', $normalizedEmail)->first();
        if ($customer !== null) {
            (new QuoteRequestModel())->update((int) $request['id'], [
                'normalized_email' => $normalizedEmail,
                'customer_id' => $customer['id'],
            ]);
        }

        return $customer;
    }

    public function linkMatchingRequests(int $customerId, string $email): void
    {
        $normalizedEmail = self::normalizeEmail($email);
        (new QuoteRequestModel())
            ->where('normalized_email', $normalizedEmail)
            ->where('customer_id', null)
            ->set(['customer_id' => $customerId])
            ->update();
    }

    public function emailInUse(string $email, ?int $exceptCustomerId = null): bool
    {
        $model = new CustomerModel();
        $model->where('normalized_email', self::normalizeEmail($email));
        if ($exceptCustomerId !== null) {
            $model->where('id !=', $exceptCustomerId);
        }

        return $model->first() !== null;
    }
}
