<?php

namespace App\Controllers;

use App\Libraries\QuoteResponseManager;
use App\Libraries\QuoteResponseNotifier;
use App\Models\BusinessSettingsModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

class Proposal extends BaseController
{
    public function show(string $token): ResponseInterface
    {
        $quote = $this->findPublicQuote($token);
        $isExpired = in_array($quote['status'], ['ready', 'sent'], true) && (string) $quote['expires_on'] < date('Y-m-d');

        return $this->response
            ->setHeader('Cache-Control', 'private, no-store, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Referrer-Policy', 'no-referrer')
            ->setHeader('X-Robots-Tag', 'noindex, nofollow')
            ->setBody(view('quotes/show', $this->publicSiteData() + [
                'quote' => $quote,
                'items' => (new QuoteLineItemModel())->where('quote_id', $quote['id'])->orderBy('sort_order', 'ASC')->findAll(),
                'canRespond' => in_array($quote['status'], ['ready', 'sent'], true) && ! $isExpired,
                'displayStatus' => $isExpired ? 'expired' : $quote['status'],
            ]));
    }

    public function respond(string $token): RedirectResponse
    {
        $quote = $this->findPublicQuote($token);
        $decision = $this->request->getPost('decision');
        $note = $this->request->getPost('response_note');
        $decision = is_string($decision) ? trim($decision) : '';
        $note = is_string($note) ? trim($note) : '';

        $wasFinal = in_array($quote['status'], QuoteResponseManager::DECISIONS, true);
        try {
            $updated = (new QuoteResponseManager())->respond((int) $quote['id'], $decision, $note);
        } catch (Throwable $exception) {
            log_message('warning', 'Proposal response rejected for quote {id}: {message}', ['id' => $quote['id'], 'message' => $exception->getMessage()]);
            $message = $exception instanceof RuntimeException ? $exception->getMessage() : 'Your response could not be saved.';

            return redirect()->to('/proposal/' . $token)->with('response_error', $message);
        }

        $notificationStatus = 'failed';
        try {
            if (! $wasFinal) {
                $notificationStatus = (new QuoteResponseNotifier())->send($updated, (new BusinessSettingsModel())->find(1) ?? []);
            }
        } catch (Throwable $exception) {
            log_message('error', 'Quote response notification failed for {number}: {message}', ['number' => $updated['quote_number'], 'message' => $exception->getMessage()]);
        }
        $message = $updated['status'] === 'accepted'
            ? 'Thank you. This quote has been accepted.'
            : 'Your decline response has been recorded.';
        $message .= $notificationStatus === 'sent'
            ? ' The studio has been notified.'
            : ' The studio can see your response in its workspace.';

        return redirect()->to('/proposal/' . $token)->with('response_message', $message);
    }

    /** @return array<string, mixed> */
    private function findPublicQuote(string $token): array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            throw PageNotFoundException::forPageNotFound();
        }
        $quote = (new QuoteModel())->where('access_token', $token)->first();
        if ($quote === null || ! in_array($quote['status'], QuoteModel::PUBLIC_STATUSES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $quote;
    }
}
