<?php

namespace App\Libraries;

class QuoteDeliveryNotifier
{
    /** @param array<string, mixed> $quote @param array<string, mixed> $business */
    public function send(array $quote, array $business): string
    {
        $config = config('Email');
        if (trim((string) $quote['customer_email']) === '' || trim((string) $config->fromEmail) === '') {
            return 'not_configured';
        }

        $email = service('email');
        $email->clear(true);
        $email->setFrom($config->fromEmail, $config->fromName ?: ($business['business_name'] ?? 'Design studio'));
        $email->setTo((string) $quote['customer_email']);
        if (filter_var($business['contact_email'] ?? null, FILTER_VALIDATE_EMAIL)) {
            $email->setReplyTo((string) $business['contact_email'], $this->headerText((string) ($business['business_name'] ?? 'Design studio')));
        }
        $email->setSubject('Your quote ' . $this->headerText((string) $quote['quote_number']));
        $email->setMessage(implode("\n", [
            'Hello ' . $quote['customer_name'] . ',', '',
            ($business['business_name'] ?? 'The studio') . ' prepared a quote for your project.',
            'View it securely: ' . base_url('proposal/' . $quote['access_token']), '',
            'Total: ' . $quote['currency_code'] . ' ' . number_format((float) $quote['total'], 2),
            'Expires: ' . $quote['expires_on'], '',
            'Please reply to this email with any questions.',
        ]));

        return $email->send() ? 'sent' : 'failed';
    }

    private function headerText(string $value): string
    {
        return trim((string) preg_replace('/[\r\n]+/', ' ', $value));
    }
}
