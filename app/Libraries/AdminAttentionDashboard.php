<?php

namespace App\Libraries;

use App\Models\ContactSubmissionModel;
use App\Models\PortfolioProjectModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use App\Models\ServiceModel;

class AdminAttentionDashboard
{
    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $newRequests = (new QuoteRequestModel())->where('status', 'new')->countAllResults();
        $needsInformation = (new QuoteRequestModel())->where('status', 'needs_information')->countAllResults();
        $readyRequests = (new QuoteRequestModel())->where('status', 'ready_to_quote')->countAllResults();
        $readyQuotes = (new QuoteModel())->where('status', 'ready')->countAllResults();
        $sentQuotes = (new QuoteModel())->where('status', 'sent')->countAllResults();
        $acceptedQuotes = (new QuoteModel())->where('status', 'accepted')->countAllResults();
        $newContacts = (new ContactSubmissionModel())->where('status', 'new')->countAllResults();

        return [
            'attentionCounts' => [
                'openRequests' => $newRequests + $needsInformation + $readyRequests,
                'quotesToSend' => $readyQuotes,
                'awaitingResponse' => $sentQuotes,
                'newContacts' => $newContacts,
            ],
            'attentionItems' => $this->attentionItems(),
            'recentWins' => (new QuoteModel())
                ->where('status', 'accepted')
                ->orderBy('updated_at', 'DESC')
                ->findAll(3),
            'catalogHealth' => [
                'totalServices' => (new ServiceModel())->countAllResults(),
                'publishedServices' => (new ServiceModel())->where('status', 'published')->countAllResults(),
                'draftServices' => (new ServiceModel())->where('status', 'draft')->countAllResults(),
                'portfolioTotal' => (new PortfolioProjectModel())->countAllResults(),
                'portfolioPublished' => (new PortfolioProjectModel())->where('status', 'published')->countAllResults(),
                'acceptedQuotes' => $acceptedQuotes,
            ],
            'recentActivity' => [
                'requests' => (new QuoteRequestModel())->orderBy('created_at', 'DESC')->findAll(4),
                'contacts' => (new ContactSubmissionModel())->orderBy('created_at', 'DESC')->findAll(4),
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private function attentionItems(): array
    {
        $items = [];

        foreach ((new QuoteRequestModel())->where('status', 'new')->orderBy('created_at', 'DESC')->findAll(5) as $request) {
            $items[] = [
                'priority' => 'high',
                'label' => 'New request',
                'title' => (string) ($request['company'] ?: $request['name']),
                'detail' => 'Review intake and decide the next workflow step.',
                'meta' => $this->dateMeta('Received', $request['created_at'] ?? $request['submitted_at'] ?? null),
                'href' => '/admin/quote-requests/' . $request['id'],
                'action' => 'Review',
            ];
        }

        foreach ((new QuoteRequestModel())->where('status', 'ready_to_quote')->orderBy('updated_at', 'DESC')->findAll(5) as $request) {
            $items[] = [
                'priority' => 'high',
                'label' => 'Ready to quote',
                'title' => (string) ($request['company'] ?: $request['name']),
                'detail' => 'Build the commercial quote from the qualified request.',
                'meta' => $this->dateMeta('Updated', $request['updated_at'] ?? null),
                'href' => '/admin/quote-requests/' . $request['id'],
                'action' => 'Create quote',
            ];
        }

        foreach ((new QuoteModel())->where('status', 'ready')->orderBy('updated_at', 'DESC')->findAll(5) as $quote) {
            $items[] = [
                'priority' => 'high',
                'label' => 'Ready to send',
                'title' => (string) $quote['quote_number'],
                'detail' => 'Send or manually share the secure proposal link.',
                'meta' => $this->dateMeta('Expires', $quote['expires_on'] ?? null),
                'href' => '/admin/quotes/' . $quote['id'] . '/edit',
                'action' => 'Send',
            ];
        }

        foreach ((new ContactSubmissionModel())->where('status', 'new')->orderBy('created_at', 'DESC')->findAll(5) as $submission) {
            $items[] = [
                'priority' => 'medium',
                'label' => 'New contact',
                'title' => (string) $submission['subject'],
                'detail' => 'General website inquiry from ' . $submission['name'] . '.',
                'meta' => $this->dateMeta('Received', $submission['created_at'] ?? null),
                'href' => '/admin/contacts/' . $submission['id'],
                'action' => 'Open',
            ];
        }

        foreach ((new QuoteRequestModel())->where('status', 'needs_information')->orderBy('updated_at', 'ASC')->findAll(4) as $request) {
            $items[] = [
                'priority' => 'medium',
                'label' => 'Needs information',
                'title' => (string) ($request['company'] ?: $request['name']),
                'detail' => 'Client follow-up is still part of this request.',
                'meta' => $this->dateMeta('Updated', $request['updated_at'] ?? null),
                'href' => '/admin/quote-requests/' . $request['id'],
                'action' => 'Follow up',
            ];
        }

        foreach ((new QuoteModel())->where('status', 'sent')->orderBy('sent_at', 'ASC')->findAll(4) as $quote) {
            $items[] = [
                'priority' => 'low',
                'label' => 'Awaiting response',
                'title' => (string) $quote['quote_number'],
                'detail' => 'Proposal has been sent and is waiting on the customer.',
                'meta' => $this->dateMeta('Sent', $quote['sent_at'] ?? $quote['updated_at'] ?? null),
                'href' => '/admin/quotes/' . $quote['id'] . '/edit',
                'action' => 'Check',
            ];
        }

        usort($items, static function (array $a, array $b): int {
            $priority = ['high' => 0, 'medium' => 1, 'low' => 2];

            return ($priority[$a['priority']] ?? 3) <=> ($priority[$b['priority']] ?? 3);
        });

        return array_slice($items, 0, 10);
    }

    private function dateMeta(string $label, mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return $label . ' date unavailable';
        }

        return $label . ' ' . substr($value, 0, 10);
    }
}
