<?php

namespace App\Libraries;

class QuoteResponseNotifier
{
    /** @param array<string, mixed> $quote @param array<string, mixed> $business */
    public function send(array $quote, array $business): string
    {
        $config = config('Email');
        $recipient = trim((string) (($business['notification_email'] ?? '') ?: ($business['contact_email'] ?? '')));
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL) || trim((string) $config->fromEmail) === '') {
            return 'not_configured';
        }

        $email = service('email');
        $email->clear(true);
        $email->setFrom($config->fromEmail, $config->fromName ?: ($business['business_name'] ?? 'Design studio'));
        $email->setTo($recipient);
        $email->setSubject($this->headerText($quote['quote_number'] . ' was ' . $quote['status']));
        $email->setMessage(implode("\n", [
            $quote['customer_name'] . ' ' . $quote['status'] . ' quote ' . $quote['quote_number'] . '.',
            '',
            'Customer: ' . ($quote['customer_business_name'] ?: $quote['customer_name']),
            'Total: ' . $quote['currency_code'] . ' ' . number_format((float) $quote['total'], 2),
            'Response note: ' . ($quote['customer_response_note'] ?: 'No note provided.'),
            '',
            'Open the quote: ' . base_url('admin/quotes/' . $quote['id'] . '/edit'),
        ]));

        return $email->send() ? 'sent' : 'failed';
    }

    private function headerText(string $value): string
    {
        return trim((string) preg_replace('/[\r\n]+/', ' ', $value));
    }
}
