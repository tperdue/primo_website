<?php

namespace App\Controllers;

use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Proposal extends BaseController
{
    public function show(string $token): ResponseInterface
    {
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            throw PageNotFoundException::forPageNotFound();
        }
        $quote = (new QuoteModel())->where('access_token', $token)->first();
        if ($quote === null || ! in_array($quote['status'], QuoteModel::PUBLIC_STATUSES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Cache-Control', 'private, no-store, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Referrer-Policy', 'no-referrer')
            ->setHeader('X-Robots-Tag', 'noindex, nofollow')
            ->setBody(view('quotes/show', $this->publicSiteData() + [
                'quote' => $quote,
                'items' => (new QuoteLineItemModel())->where('quote_id', $quote['id'])->orderBy('sort_order', 'ASC')->findAll(),
            ]));
    }
}
