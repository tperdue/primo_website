<?php

namespace App\Libraries;

class QuoteRequestNotifier
{
    /** @param array<string, mixed> $request @param array<string, mixed> $business */
    public function notifyOwner(array $request, array $business): string
    {
        $recipient = trim((string) (($business['notification_email'] ?? '') ?: ($business['contact_email'] ?? '')));
        if (! $this->configured($recipient)) {
            return 'not_configured';
        }

        $email = $this->email($business);
        $email->setTo($recipient);
        $email->setReplyTo((string) $request['email'], $this->headerText((string) $request['name']));
        $email->setSubject('New quote request ' . $this->headerText((string) $request['reference_number']));
        $email->setMessage(implode("\n", [
            'A new quote request was submitted.', '',
            'Reference: ' . $request['reference_number'],
            'Name: ' . $request['name'],
            'Email: ' . $request['email'],
            'Phone: ' . ($request['phone'] ?: 'Not provided'), '',
            (string) $request['project_summary'],
        ]));

        return $email->send() ? 'sent' : 'failed';
    }

    /** @param array<string, mixed> $request @param array<string, mixed> $business */
    public function confirmCustomer(array $request, array $business): string
    {
        if (! $this->configured((string) $request['email'])) {
            return 'not_configured';
        }

        $email = $this->email($business);
        $email->setTo((string) $request['email']);
        $email->setSubject('We received your quote request ' . $this->headerText((string) $request['reference_number']));
        $email->setMessage(implode("\n", [
            'Thank you, ' . $request['name'] . '.', '',
            'We received your request and will review the project details before following up.',
            'Reference: ' . $request['reference_number'], '',
            'This confirmation is not a final quote, price, or acceptance of the project.',
        ]));

        return $email->send() ? 'sent' : 'failed';
    }

    /** @param array<string, mixed> $business */
    private function email(array $business): \CodeIgniter\Email\Email
    {
        $config = config('Email');
        $email = service('email');
        $email->clear(true);
        $email->setFrom($config->fromEmail, $config->fromName ?: ($business['business_name'] ?? 'Design studio'));

        return $email;
    }

    private function configured(string $recipient): bool
    {
        return trim($recipient) !== '' && trim((string) config('Email')->fromEmail) !== '';
    }

    private function headerText(string $value): string
    {
        return trim((string) preg_replace('/[\r\n]+/', ' ', $value));
    }
}
