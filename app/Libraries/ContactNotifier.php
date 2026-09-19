<?php

namespace App\Libraries;

class ContactNotifier
{
    /** @param array<string, mixed> $submission @param array<string, mixed> $business */
    public function notify(array $submission, array $business): string
    {
        $recipient = trim((string) (($business['notification_email'] ?? '') ?: ($business['contact_email'] ?? '')));
        $config = config('Email');

        if ($recipient === '' || trim($config->fromEmail) === '') {
            return 'not_configured';
        }

        $email = service('email');
        $email->clear(true);
        $email->setFrom($config->fromEmail, $config->fromName ?: ($business['business_name'] ?? 'Design studio'));
        $email->setTo($recipient);
        $email->setReplyTo((string) $submission['email'], $this->headerText((string) $submission['name']));
        $email->setSubject('New contact request: ' . $this->headerText((string) $submission['subject']));
        $email->setMessage($this->message($submission));

        if (! $email->send()) {
            log_message('error', 'Contact notification could not be sent for submission {id}.', ['id' => $submission['id'] ?? 'unknown']);

            return 'failed';
        }

        return 'sent';
    }

    /** @param array<string, mixed> $submission */
    private function message(array $submission): string
    {
        return implode("\n", [
            'A new contact request was submitted.',
            '',
            'Name: ' . $submission['name'],
            'Email: ' . $submission['email'],
            'Phone: ' . ($submission['phone'] ?: 'Not provided'),
            'Subject: ' . $submission['subject'],
            '',
            (string) $submission['message'],
        ]);
    }

    private function headerText(string $value): string
    {
        return trim((string) preg_replace('/[\r\n]+/', ' ', $value));
    }
}
