<?php

namespace App\Libraries;

use App\Models\QuoteModel;
use App\Models\QuoteStatusHistoryModel;
use RuntimeException;
use Throwable;

class QuoteResponseManager
{
    public const DECISIONS = ['accepted', 'declined'];

    /** @return array<string, mixed> */
    public function respond(int $quoteId, string $decision, ?string $note = null): array
    {
        if (! in_array($decision, self::DECISIONS, true)) {
            throw new RuntimeException('Choose accept or decline.');
        }

        $note = trim((string) $note);
        if (mb_strlen($note) > 1000) {
            throw new RuntimeException('Your note must be 1,000 characters or fewer.');
        }

        $model = new QuoteModel();
        $quote = $model->find($quoteId);
        if ($quote === null) {
            throw new RuntimeException('Quote not found.');
        }
        if ($quote['status'] === $decision) {
            return $quote;
        }
        if (in_array($quote['status'], self::DECISIONS, true)) {
            throw new RuntimeException('This quote already has a final customer response.');
        }
        if (! in_array($quote['status'], ['ready', 'sent'], true)) {
            throw new RuntimeException('This quote is not available for a customer response.');
        }
        if ((string) $quote['expires_on'] < date('Y-m-d')) {
            $this->expire($quote);
            throw new RuntimeException('This quote has expired. Please contact the studio for an updated quote.');
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $respondedAt = date('Y-m-d H:i:s');
            $nextVersion = (int) $quote['version'] + 1;
            $db->table('quotes')
                ->where('id', $quoteId)
                ->whereIn('status', ['ready', 'sent'])
                ->update([
                    'status' => $decision,
                    'responded_at' => $respondedAt,
                    'customer_response_note' => $note === '' ? null : $note,
                    'version' => $nextVersion,
                    'updated_at' => $respondedAt,
                ]);
            if ($db->affectedRows() !== 1) {
                throw new RuntimeException('This quote changed before your response was saved. Please reload it.');
            }
            (new QuoteStatusHistoryModel())->insert([
                'quote_id' => $quoteId,
                'from_status' => $quote['status'],
                'to_status' => $decision,
                'note' => 'Customer responded through the secure proposal.',
                'actor' => 'customer',
            ]);
            (new QuoteManager())->recordVersion($quoteId, $nextVersion);
            if (! $db->transStatus()) {
                throw new RuntimeException('Your response could not be saved.');
            }
            $db->transCommit();

            return $model->find($quoteId);
        } catch (Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    /** @param array<string, mixed> $quote */
    private function expire(array $quote): void
    {
        $nextVersion = (int) $quote['version'] + 1;
        $db = db_connect();
        $db->transBegin();
        try {
            $db->table('quotes')
                ->where('id', $quote['id'])
                ->whereIn('status', ['ready', 'sent'])
                ->update(['status' => 'expired', 'version' => $nextVersion, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($db->affectedRows() !== 1) {
                throw new RuntimeException('This quote changed before its expiration was recorded.');
            }
            (new QuoteStatusHistoryModel())->insert([
                'quote_id' => $quote['id'],
                'from_status' => $quote['status'],
                'to_status' => 'expired',
                'note' => 'Quote expiration was enforced when a customer response was attempted.',
                'actor' => 'system',
            ]);
            (new QuoteManager())->recordVersion((int) $quote['id'], $nextVersion);
            if (! $db->transStatus()) {
                throw new RuntimeException('The quote expiration could not be recorded.');
            }
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }
}
