<?php

use App\Models\CustomerModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use App\Models\QuoteStatusHistoryModel;
use App\Models\QuoteVersionModel;
use App\Libraries\QuoteManager;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class ProposalResponseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        auth('session')->logout();
        service('superglobals')->setGlobalArray('post', []);
    }

    public function testCustomerCanAcceptAnOpenQuoteOnce(): void
    {
        $quote = $this->quote('sent');

        $this->post('/proposal/' . $quote['access_token'] . '/respond', [
            csrf_token() => csrf_hash(),
            'decision' => 'accepted',
            'response_note' => 'Tuesday works well for kickoff.',
        ])->assertRedirectTo('/proposal/' . $quote['access_token']);

        $updated = (new QuoteModel())->find($quote['id']);
        $this->assertSame('accepted', $updated['status']);
        $this->assertSame('Tuesday works well for kickoff.', $updated['customer_response_note']);
        $this->assertNotEmpty($updated['responded_at']);
        $this->assertSame('2', (string) $updated['version']);
        $history = (new QuoteStatusHistoryModel())->where('quote_id', $quote['id'])->first();
        $this->assertSame('customer', $history['actor']);
        $this->assertSame('sent', $history['from_status']);
        $this->assertSame('accepted', $history['to_status']);
        $this->assertSame(1, (new QuoteVersionModel())->where('quote_id', $quote['id'])->countAllResults());

        $page = $this->get('/proposal/' . $quote['access_token']);
        $page->assertSee('This quote has been accepted.');
        $page->assertDontSee('Accept quote');
    }

    public function testFinalCustomerDecisionCannotBeReversedFromProposal(): void
    {
        $quote = $this->quote('ready');
        $this->post('/proposal/' . $quote['access_token'] . '/respond', [
            csrf_token() => csrf_hash(),
            'decision' => 'declined',
            'response_note' => 'The timing changed.',
        ]);

        $this->post('/proposal/' . $quote['access_token'] . '/respond', [
            csrf_token() => csrf_hash(),
            'decision' => 'accepted',
        ])->assertRedirectTo('/proposal/' . $quote['access_token']);

        $this->assertSame('declined', (new QuoteModel())->find($quote['id'])['status']);
        $this->assertSame(1, (new QuoteStatusHistoryModel())->where('quote_id', $quote['id'])->countAllResults());
    }

    public function testCustomerRespondedQuoteCannotBeCommerciallyRewritten(): void
    {
        $quote = $this->quote('sent');
        $this->post('/proposal/' . $quote['access_token'] . '/respond', [
            csrf_token() => csrf_hash(),
            'decision' => 'accepted',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer-responded quotes are locked.');
        (new QuoteManager())->update((int) $quote['id'], [
            'quote_date' => $quote['quote_date'],
            'expires_on' => $quote['expires_on'],
            'currency_code' => 'USD',
            'status' => 'sent',
            'discount_amount' => '0.00',
            'tax_rate' => '0',
            'deposit_percentage' => '50',
            'terms' => 'Changed terms.',
            'notes' => '',
        ], [[
            'description' => 'Brand identity',
            'details' => '',
            'quantity' => '1',
            'unit_price' => '1200',
        ]]);
    }

    public function testExpiredQuoteRejectsDecisionAndRecordsSystemExpiration(): void
    {
        $quote = $this->quote('sent', date('Y-m-d', strtotime('-1 day')));

        $this->post('/proposal/' . $quote['access_token'] . '/respond', [
            csrf_token() => csrf_hash(),
            'decision' => 'accepted',
        ])->assertRedirectTo('/proposal/' . $quote['access_token']);

        $this->assertSame('expired', (new QuoteModel())->find($quote['id'])['status']);
        $history = (new QuoteStatusHistoryModel())->where('quote_id', $quote['id'])->first();
        $this->assertSame('system', $history['actor']);
        $this->assertSame('expired', $history['to_status']);
        $this->get('/proposal/' . $quote['access_token'])->assertSee('This quote is no longer open for a response.');
    }

    public function testUnknownProposalTokenIsNotExposed(): void
    {
        try {
            $this->post('/proposal/' . str_repeat('a', 64) . '/respond', [
                csrf_token() => csrf_hash(),
                'decision' => 'accepted',
            ]);
            $this->fail('Expected an unknown proposal token to remain hidden.');
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }

    /** @return array<string, mixed> */
    private function quote(string $status, ?string $expiresOn = null): array
    {
        $customerId = (int) (new CustomerModel())->insert([
            'name' => 'Avery Client',
            'business_name' => 'Avery Studio',
            'email' => 'avery@example.com',
            'normalized_email' => 'avery@example.com',
        ]);
        $quoteId = (int) (new QuoteModel())->insert([
            'quote_number' => 'Q-2026-' . strtoupper(bin2hex(random_bytes(4))),
            'access_token' => bin2hex(random_bytes(32)),
            'customer_id' => $customerId,
            'quote_request_id' => null,
            'customer_name' => 'Avery Client',
            'customer_business_name' => 'Avery Studio',
            'customer_email' => 'avery@example.com',
            'customer_phone' => null,
            'quote_date' => date('Y-m-d'),
            'expires_on' => $expiresOn ?? date('Y-m-d', strtotime('+14 days')),
            'currency_code' => 'USD',
            'status' => $status,
            'subtotal' => '1200.00',
            'discount_amount' => '0.00',
            'tax_rate' => '0.000',
            'tax_amount' => '0.00',
            'total' => '1200.00',
            'deposit_percentage' => '50.00',
            'deposit_amount' => '600.00',
            'terms' => 'A deposit begins the engagement.',
            'notes' => null,
            'version' => 1,
            'delivery_status' => $status === 'sent' ? 'sent' : 'pending',
            'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
        ]);
        (new QuoteLineItemModel())->insert([
            'quote_id' => $quoteId,
            'description' => 'Brand identity',
            'details' => 'Identity system and launch assets.',
            'quantity' => '1.00',
            'unit_price' => '1200.00',
            'line_total' => '1200.00',
            'sort_order' => 0,
        ]);

        return (new QuoteModel())->find($quoteId);
    }
}
