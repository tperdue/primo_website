<?php

use App\Models\BusinessSettingsModel;
use App\Models\CustomerModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use App\Models\QuoteStatusHistoryModel;
use App\Models\QuoteVersionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class QuoteManagementTest extends CIUnitTestCase
{
    use AuthenticationTesting;
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        auth('session')->logout();
        service('superglobals')->setGlobalArray('post', []);
        (new BusinessSettingsModel())->update(1, [
            'default_quote_expiration_days' => 21,
            'default_quote_terms' => 'A 50% deposit begins the engagement.',
            'default_deposit_percentage' => '50.00',
        ]);
    }

    public function testQuoteRequestStatusesUseTheSimplifiedWorkflow(): void
    {
        $this->assertSame([
            'new' => 'New',
            'needs_information' => 'Needs more information from client',
            'ready_to_quote' => 'Ready to quote',
            'closed_quote_created' => 'Closed, Quote Created',
            'closed_wont_pursue' => "Closed, Won't pursue.",
        ], QuoteRequestModel::STATUSES);
    }

    public function testReadyRequestCreatesOneCustomerLinkedQuoteWithSnapshots(): void
    {
        $requestId = $this->request('ready_to_quote');
        $admin = $this->createUser('admin');

        $result = $this->actingAs($admin)->post('/admin/quote-requests/' . $requestId . '/quote', [csrf_token() => csrf_hash()]);

        $quote = (new QuoteModel())->where('quote_request_id', $requestId)->first();
        $result->assertRedirectTo('/admin/quotes/' . $quote['id'] . '/edit');
        $this->assertSame('Q-' . date('Y') . '-' . str_pad((string) $quote['id'], 6, '0', STR_PAD_LEFT), $quote['quote_number']);
        $this->assertSame(900.0, (float) $quote['subtotal']);
        $this->assertSame(450.0, (float) $quote['deposit_amount']);
        $this->assertSame('A 50% deposit begins the engagement.', $quote['terms']);
        $this->assertSame(1, (new CustomerModel())->countAllResults());
        $this->assertSame(1, (new QuoteLineItemModel())->where('quote_id', $quote['id'])->countAllResults());
        $this->assertSame(1, (new QuoteVersionModel())->where('quote_id', $quote['id'])->countAllResults());
        $this->assertSame('closed_quote_created', (new QuoteRequestModel())->find($requestId)['status']);

        $this->actingAs($admin)->post('/admin/quote-requests/' . $requestId . '/quote', [csrf_token() => csrf_hash()]);
        $this->assertSame(1, (new QuoteModel())->where('quote_request_id', $requestId)->countAllResults());
        $this->assertSame(1, (new CustomerModel())->countAllResults());
    }

    public function testQuoteUpdateRecalculatesTotalsAndCreatesRevisionAndStatusHistory(): void
    {
        $quote = $this->createQuote();
        $admin = $this->createUser('admin');
        $input = $this->quoteInput('ready');

        $result = $this->actingAs($admin)->post('/admin/quotes/' . $quote['id'], $input);

        $result->assertRedirectTo('/admin/quotes/' . $quote['id'] . '/edit');
        $updated = (new QuoteModel())->find($quote['id']);
        $this->assertSame(1400.0, (float) $updated['subtotal']);
        $this->assertSame(130.0, (float) $updated['tax_amount']);
        $this->assertSame(1430.0, (float) $updated['total']);
        $this->assertSame(715.0, (float) $updated['deposit_amount']);
        $this->assertSame('2', (string) $updated['version']);
        $this->assertSame(2, (new QuoteLineItemModel())->where('quote_id', $quote['id'])->countAllResults());
        $this->assertSame(2, (new QuoteVersionModel())->where('quote_id', $quote['id'])->countAllResults());
        $history = (new QuoteStatusHistoryModel())->where('quote_id', $quote['id'])->orderBy('id', 'DESC')->first();
        $this->assertSame('draft', $history['from_status']);
        $this->assertSame('ready', $history['to_status']);
    }

    public function testSecureProposalIsHiddenUntilReadyAndEscapesCustomerContent(): void
    {
        $quote = $this->createQuote();
        try {
            $this->get('/proposal/' . $quote['access_token']);
            $this->fail('Expected a draft quote to remain private.');
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }

        $input = $this->quoteInput('ready');
        $input['items'][0]['details'] = '<script>alert(1)</script>';
        $this->actingAs($this->createUser('admin'))->post('/admin/quotes/' . $quote['id'], $input);

        $page = $this->get('/proposal/' . $quote['access_token']);
        $page->assertOK();
        $this->assertStringContainsString('no-store', $page->response()->getHeaderLine('Cache-Control'));
        $page->assertSee('USD 1,430.00');
        $page->assertSee('content="noindex,nofollow,noarchive"');
        $page->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
        $page->assertDontSee('<script>alert(1)</script>');
    }

    public function testUnconfiguredEmailDoesNotMarkReadyQuoteAsSent(): void
    {
        $quote = $this->createQuote();
        (new QuoteModel())->update($quote['id'], ['status' => 'ready']);

        $this->actingAs($this->createUser('admin'))->post('/admin/quotes/' . $quote['id'] . '/send', [csrf_token() => csrf_hash()])
            ->assertRedirectTo('/admin/quotes/' . $quote['id'] . '/edit');

        $updated = (new QuoteModel())->find($quote['id']);
        $this->assertSame('ready', $updated['status']);
        $this->assertSame('not_configured', $updated['delivery_status']);
    }

    public function testGuestAndOrdinaryUserCannotManageQuotes(): void
    {
        $this->get('/admin/quotes')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/quotes')->assertRedirect();
    }

    public function testRequestMustBeInAQuoteableStatusBeforeQuoteCreation(): void
    {
        $requestId = $this->request('needs_information');

        $this->actingAs($this->createUser('admin'))->post('/admin/quote-requests/' . $requestId . '/quote', [csrf_token() => csrf_hash()])
            ->assertRedirectTo('/admin/quote-requests/' . $requestId);

        $this->assertSame(0, (new QuoteModel())->countAllResults());
        $this->assertSame(0, (new CustomerModel())->countAllResults());
    }

    public function testClosedQuoteCreatedStatusAllowsMissingQuoteToBeCreated(): void
    {
        $requestId = $this->request('closed_quote_created');

        $this->actingAs($this->createUser('admin'))->post('/admin/quote-requests/' . $requestId . '/quote', [csrf_token() => csrf_hash()]);

        $this->assertSame(1, (new QuoteModel())->where('quote_request_id', $requestId)->countAllResults());
        $this->assertSame('closed_quote_created', (new QuoteRequestModel())->find($requestId)['status']);
    }

    /** @return array<string, mixed> */
    private function createQuote(): array
    {
        $requestId = $this->request('ready_to_quote');
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->post('/admin/quote-requests/' . $requestId . '/quote', [csrf_token() => csrf_hash()]);

        return (new QuoteModel())->where('quote_request_id', $requestId)->first();
    }

    private function request(string $status): int
    {
        $requestId = (int) (new QuoteRequestModel())->insert([
            'reference_number' => 'PR-20260919-' . strtoupper(bin2hex(random_bytes(6))),
            'name' => 'Avery Client',
            'company' => 'Avery Studio',
            'email' => 'avery@example.com',
            'phone' => '404-555-0100',
            'preferred_contact' => 'email',
            'project_summary' => 'A launch identity for a new customer-facing company.',
            'desired_completion_date' => null,
            'budget_range' => '$1,500-$3,000',
            'additional_notes' => null,
            'status' => $status,
            'owner_notification_status' => 'not_configured',
            'customer_notification_status' => 'not_configured',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
        (new QuoteRequestServiceModel())->insert([
            'quote_request_id' => $requestId,
            'service_id' => null,
            'service_name' => 'Brand identity',
            'service_slug' => 'brand-identity',
            'service_summary' => 'A flexible visual identity system.',
            'service_description' => 'Strategy and visual design.',
            'starting_price' => '900.00',
            'currency_code' => 'USD',
            'show_price' => 1,
            'sort_order' => 0,
        ]);

        return $requestId;
    }

    /** @return array<string, mixed> */
    private function quoteInput(string $status): array
    {
        return [
            csrf_token() => csrf_hash(),
            'quote_date' => date('Y-m-d'),
            'expires_on' => date('Y-m-d', strtotime('+21 days')),
            'currency_code' => 'USD',
            'status' => $status,
            'discount_amount' => '100.00',
            'tax_rate' => '10',
            'deposit_percentage' => '50',
            'terms' => 'A deposit is required to begin.',
            'notes' => 'Thank you for the opportunity.',
            'items' => [
                ['description' => 'Identity system', 'details' => 'Core visual direction.', 'quantity' => '1', 'unit_price' => '1200'],
                ['description' => 'Social templates', 'details' => 'Two reusable templates.', 'quantity' => '2', 'unit_price' => '100'],
            ],
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-quote-manager-' . bin2hex(random_bytes(3)),
            'email' => $group . '-quote-manager-' . bin2hex(random_bytes(3)) . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
