<?php

use App\Models\ContactSubmissionModel;
use App\Models\CustomerModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class AdminDashboardTest extends CIUnitTestCase
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
    }

    public function testDashboardPrioritizesExistingOperationalWork(): void
    {
        $this->quoteRequest('new', 'Avery Studio');
        $this->quoteRequest('ready_to_quote', 'Bright Goods');
        $this->quote('ready', 'Q-2026-000101', 'Mara Client');
        $this->quote('sent', 'Q-2026-000102', 'Noah Client');
        $this->quote('accepted', 'Q-2026-000103', 'Won Client');
        (new ContactSubmissionModel())->insert([
            'name' => 'Taylor Prospect',
            'email' => 'taylor@example.com',
            'subject' => 'Campaign system',
            'message' => 'We need a campaign system.',
            'status' => 'new',
            'notification_status' => 'not_configured',
        ]);

        $result = $this->actingAs($this->createUser('admin'))->get('/admin/');

        $result->assertOK();
        $result->assertSee('Attention queue');
        $result->assertSee('Open quote requests');
        $result->assertSee('New request');
        $result->assertSee('Ready to quote');
        $result->assertSee('Ready to send');
        $result->assertSee('New contact');
        $result->assertSee('Awaiting response');
        $result->assertSee('Recent wins');
        $result->assertSee('Q-2026-000103');
    }

    public function testDashboardShowsClearStateWhenNothingNeedsAttention(): void
    {
        $result = $this->actingAs($this->createUser('admin'))->get('/admin/');

        $result->assertOK();
        $result->assertSee('All clear');
        $result->assertSee('No quote requests, proposals, or contact messages need attention right now.');
    }

    public function testDashboardRequiresAdminAccess(): void
    {
        $this->get('/admin/')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/')->assertRedirect();
    }

    private function quoteRequest(string $status, string $company): int
    {
        $email = strtolower(str_replace(' ', '-', $company)) . '@example.com';

        return (int) (new QuoteRequestModel())->insert([
            'reference_number' => 'PR-20260919-' . strtoupper(bin2hex(random_bytes(6))),
            'name' => $company . ' Lead',
            'company' => $company,
            'email' => $email,
            'normalized_email' => $email,
            'preferred_contact' => 'email',
            'project_summary' => 'A visual identity project that needs admin review.',
            'status' => $status,
            'owner_notification_status' => 'not_configured',
            'customer_notification_status' => 'not_configured',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function quote(string $status, string $number, string $customerName): int
    {
        $email = strtolower(str_replace(' ', '-', $customerName)) . '@example.com';
        $customerId = (int) (new CustomerModel())->insert([
            'name' => $customerName,
            'email' => $email,
            'normalized_email' => $email,
        ]);

        return (int) (new QuoteModel())->insert([
            'quote_number' => $number,
            'access_token' => bin2hex(random_bytes(32)),
            'customer_id' => $customerId,
            'quote_request_id' => null,
            'customer_name' => $customerName,
            'customer_business_name' => null,
            'customer_email' => $email,
            'customer_phone' => null,
            'quote_date' => date('Y-m-d'),
            'expires_on' => date('Y-m-d', strtotime('+14 days')),
            'currency_code' => 'USD',
            'status' => $status,
            'subtotal' => '1200.00',
            'discount_amount' => '0.00',
            'tax_rate' => '0.000',
            'tax_amount' => '0.00',
            'total' => '1200.00',
            'deposit_percentage' => '50.00',
            'deposit_amount' => '600.00',
            'terms' => null,
            'notes' => null,
            'version' => 1,
            'delivery_status' => $status === 'sent' ? 'sent' : 'pending',
            'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
        ]);
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-dashboard-' . bin2hex(random_bytes(3)),
            'email' => $group . '-dashboard-' . bin2hex(random_bytes(3)) . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
