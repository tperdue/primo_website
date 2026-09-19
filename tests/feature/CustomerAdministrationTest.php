<?php

use App\Models\CustomerModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use App\Libraries\QuoteRequestSubmission;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class CustomerAdministrationTest extends CIUnitTestCase
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

    public function testAdminCanCreateSearchAndUpdateCustomerProfile(): void
    {
        $admin = $this->createUser('admin');
        $create = $this->actingAs($admin)->post('/admin/customers', $this->customerInput([
            'email' => '  CASEY@Example.COM ',
            'notes' => '<script>private</script>',
        ]));

        $customer = (new CustomerModel())->where('normalized_email', 'casey@example.com')->first();
        $create->assertRedirectTo('/admin/customers/' . $customer['id']);
        $this->assertSame('casey@example.com', $customer['email']);
        $this->assertSame('Atlanta', $customer['city']);

        $directory = $this->actingAs($admin)->get('/admin/customers?q=Casey');
        $directory->assertOK();
        $directory->assertSee('Casey Creative');
        $directory->assertSee('casey@example.com');

        $profile = $this->actingAs($admin)->get('/admin/customers/' . $customer['id']);
        $profile->assertOK();
        $profile->assertSee('&lt;script&gt;private&lt;/script&gt;');
        $profile->assertDontSee('<script>private</script>');

        $update = $this->actingAs($admin)->post('/admin/customers/' . $customer['id'], $this->customerInput([
            'name' => 'Casey Updated',
            'phone' => '404-555-0199',
        ]));
        $update->assertRedirectTo('/admin/customers/' . $customer['id']);
        $this->assertSame('Casey Updated', (new CustomerModel())->find($customer['id'])['name']);
    }

    public function testDuplicateEmailIsRejectedCaseInsensitively(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->post('/admin/customers', $this->customerInput());

        $result = $this->actingAs($admin)->post('/admin/customers', $this->customerInput([
            'name' => 'Another Person',
            'email' => 'CASEY@example.com',
        ]));

        $result->assertRedirect();
        $this->assertSame(1, (new CustomerModel())->countAllResults());
        $this->assertSame('A customer already uses this email address.', session('errors')['email']);
    }

    public function testQuoteConversionReusesCustomerAndLinksMatchingRequestHistory(): void
    {
        $firstRequestId = $this->request('Casey@Example.com');
        $secondRequestId = $this->request('casey@example.COM');
        $admin = $this->createUser('admin');

        $this->actingAs($admin)->post('/admin/quote-requests/' . $firstRequestId . '/quote', [csrf_token() => csrf_hash()]);
        $this->actingAs($admin)->post('/admin/quote-requests/' . $secondRequestId . '/quote', [csrf_token() => csrf_hash()]);

        $this->assertSame(1, (new CustomerModel())->countAllResults());
        $customer = (new CustomerModel())->first();
        $this->assertSame((string) $customer['id'], (string) (new QuoteRequestModel())->find($firstRequestId)['customer_id']);
        $this->assertSame((string) $customer['id'], (string) (new QuoteRequestModel())->find($secondRequestId)['customer_id']);
        $this->assertSame(2, (new QuoteModel())->where('customer_id', $customer['id'])->countAllResults());

        $profile = $this->actingAs($admin)->get('/admin/customers/' . $customer['id']);
        $profile->assertOK();
        $profile->assertSee('Relationship history');
        $profile->assertSee('2');
        $profile->assertSee('Brand identity');
    }

    public function testEditingCustomerDoesNotRewriteQuoteSnapshot(): void
    {
        $requestId = $this->request('casey@example.com');
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->post('/admin/quote-requests/' . $requestId . '/quote', [csrf_token() => csrf_hash()]);
        $quote = (new QuoteModel())->where('quote_request_id', $requestId)->first();

        $this->actingAs($admin)->post('/admin/customers/' . $quote['customer_id'], $this->customerInput([
            'name' => 'Casey New Name',
            'email' => 'new-casey@example.com',
        ]));

        $quote = (new QuoteModel())->find($quote['id']);
        $this->assertSame('Casey Client', $quote['customer_name']);
        $this->assertSame('casey@example.com', strtolower($quote['customer_email']));
    }

    public function testNewRequestImmediatelyLinksToAnExistingCustomer(): void
    {
        $customerId = (int) (new CustomerModel())->insert([
            'name' => 'Returning Client',
            'email' => 'returning@example.com',
            'normalized_email' => 'returning@example.com',
        ]);
        $request = (new QuoteRequestSubmission())->create([
            'name' => 'Returning Client',
            'company' => null,
            'email' => 'returning@example.com',
            'normalized_email' => 'returning@example.com',
            'phone' => null,
            'preferred_contact' => 'email',
            'project_summary' => 'A new assignment from a returning customer.',
            'desired_completion_date' => null,
            'budget_range' => null,
            'additional_notes' => null,
        ], [[
            'id' => null,
            'name' => 'Brand identity',
            'slug' => 'brand-identity',
            'summary' => 'A flexible visual identity system.',
            'description' => 'Strategy and visual design.',
            'starting_price' => '900.00',
            'currency_code' => 'USD',
            'show_price' => 1,
        ]], [], [], []);

        $this->assertSame((string) $customerId, (string) $request['customer_id']);
        $this->assertSame(1, (new CustomerModel())->countAllResults());
    }

    public function testCustomerAdministrationRequiresAdminAccess(): void
    {
        $this->get('/admin/customers')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/customers')->assertRedirect();
    }

    /** @param array<string, string> $overrides @return array<string, string> */
    private function customerInput(array $overrides = []): array
    {
        return $overrides + [
            csrf_token() => csrf_hash(),
            'name' => 'Casey Client',
            'business_name' => 'Casey Creative',
            'email' => 'casey@example.com',
            'phone' => '404-555-0100',
            'address_line_1' => '100 Design Way',
            'address_line_2' => '',
            'city' => 'Atlanta',
            'region' => 'GA',
            'postal_code' => '30344',
            'country' => 'United States',
            'notes' => 'Repeat identity client.',
        ];
    }

    private function request(string $email): int
    {
        $normalizedEmail = strtolower(trim($email));
        $requestId = (int) (new QuoteRequestModel())->insert([
            'reference_number' => 'PR-20260919-' . strtoupper(bin2hex(random_bytes(6))),
            'name' => 'Casey Client',
            'company' => 'Casey Creative',
            'email' => trim($email),
            'normalized_email' => $normalizedEmail,
            'phone' => '404-555-0100',
            'preferred_contact' => 'email',
            'project_summary' => 'Brand identity work for the next customer launch.',
            'status' => 'ready_to_quote',
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

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-customer-admin-' . bin2hex(random_bytes(3)),
            'email' => $group . '-customer-admin-' . bin2hex(random_bytes(3)) . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
