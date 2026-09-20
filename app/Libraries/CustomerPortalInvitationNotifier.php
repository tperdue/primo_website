<?php

namespace App\Libraries;

class CustomerPortalInvitationNotifier
{
    /** @param array<string, mixed> $customer @param array<string, mixed> $business */
    public function send(array $customer, array $business, string $activationUrl): string
    {
        $config = config('Email');
        if (trim((string) $config->fromEmail) === '') {
            return 'not_configured';
        }

        $email = service('email');
        $email->clear(true);
        $email->setFrom($config->fromEmail, $config->fromName ?: ($business['business_name'] ?? 'Design studio'));
        $email->setTo((string) $customer['email']);
        if (filter_var($business['contact_email'] ?? null, FILTER_VALIDATE_EMAIL)) {
            $email->setReplyTo((string) $business['contact_email'], $this->headerText((string) ($business['business_name'] ?? 'Design studio')));
        }
        $email->setSubject('Set up your client portal');
        $email->setMessage(implode("\n", [
            'Hello ' . $customer['name'] . ',', '',
            ($business['business_name'] ?? 'The studio') . ' invited you to a private client portal.',
            'Create your password within 72 hours: ' . $activationUrl, '',
            'If you were not expecting this invitation, you can ignore this email.',
        ]));

        if (! $email->send()) {
            log_message('error', 'Customer portal invitation could not be sent for customer {id}.', ['id' => $customer['id'] ?? 'unknown']);

            return 'failed';
        }

        return 'sent';
    }

    private function headerText(string $value): string
    {
        return trim((string) preg_replace('/[\r\n]+/', ' ', $value));
    }
}
