<?php

use App\Models\CustomerModel;
use App\Models\CustomerPortalInvitationModel;
use App\Models\ProjectModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class CustomerPortalTest extends CIUnitTestCase
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

    public function testAdminCreatesHashedExpiringInvitation(): void
    {
        $customerId = $this->customer('invitee@example.com');
        $result = $this->actingAs($this->user('admin'))->post('/admin/customers/' . $customerId . '/portal-invitation', [csrf_token() => csrf_hash()]);

        $result->assertRedirectTo('/admin/customers/' . $customerId);
        $url = (string) session('portal_invitation_url');
        $this->assertNotSame('', $url);
        $token = basename(parse_url($url, PHP_URL_PATH));
        $invitation = (new CustomerPortalInvitationModel())->where('customer_id', $customerId)->first();
        $this->assertSame(hash('sha256', $token), $invitation['token_hash']);
        $this->assertStringNotContainsString($token, json_encode($invitation));
        $this->assertGreaterThan(time(), strtotime($invitation['expires_at']));
    }

    public function testValidInvitationCreatesLinkedCustomerAccount(): void
    {
        $customerId = $this->customer('activate@example.com');
        $token = bin2hex(random_bytes(32));
        $invitationId = (int) (new CustomerPortalInvitationModel())->insert([
            'customer_id' => $customerId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + HOUR),
        ]);

        $result = $this->post('/portal/activate/' . $token, [
            csrf_token() => csrf_hash(),
            'password' => 'Long!River9Canvas#Orbit',
            'password_confirm' => 'Long!River9Canvas#Orbit',
        ]);

        $result->assertRedirectTo('/login');
        $customer = (new CustomerModel())->find($customerId);
        $this->assertNotNull($customer['user_id']);
        $this->assertTrue(auth()->getProvider()->findById($customer['user_id'])->inGroup('customer'));
        $this->assertNotNull((new CustomerPortalInvitationModel())->find($invitationId)['used_at']);
    }

    public function testExpiredAndUsedInvitationsCannotCreateAccounts(): void
    {
        foreach ([
            ['expires_at' => date('Y-m-d H:i:s', time() - HOUR), 'used_at' => null],
            ['expires_at' => date('Y-m-d H:i:s', time() + HOUR), 'used_at' => date('Y-m-d H:i:s')],
        ] as $index => $state) {
            $customerId = $this->customer('blocked-' . $index . '@example.com');
            $token = bin2hex(random_bytes(32));
            (new CustomerPortalInvitationModel())->insert($state + [
                'customer_id' => $customerId,
                'token_hash' => hash('sha256', $token),
            ]);
            $this->post('/portal/activate/' . $token, [
                csrf_token() => csrf_hash(),
                'password' => 'Long!River9Canvas#Orbit',
                'password_confirm' => 'Long!River9Canvas#Orbit',
            ])->assertRedirectTo('/portal/activate/' . $token);
            $this->assertNull((new CustomerModel())->find($customerId)['user_id']);
        }
    }

    public function testLoginRedirectsCustomersAndAdminsToTheirWorkspaces(): void
    {
        $customer = $this->user('customer', 'portal-login@example.com', 'Long!River9Canvas#Orbit');
        $this->post('/login', [csrf_token() => csrf_hash(), 'email' => 'portal-login@example.com', 'password' => 'Long!River9Canvas#Orbit'])
            ->assertRedirectTo('/portal');

        auth('session')->logout();
        $this->user('admin', 'admin-login@example.com', 'Long!River9Canvas#Orbit');
        $this->post('/login', [csrf_token() => csrf_hash(), 'email' => 'admin-login@example.com', 'password' => 'Long!River9Canvas#Orbit'])
            ->assertRedirectTo('/admin/');
        $this->assertTrue($customer->inGroup('customer'));
    }

    public function testPortalIsReadOnlyAndScopedToCurrentCustomer(): void
    {
        $user = $this->user('customer');
        $ownCustomerId = $this->customer('owner@example.com', $user->id, '<script>private customer notes</script>');
        $otherCustomerId = $this->customer('other@example.com');
        $ownRequestId = $this->request($ownCustomerId, 'Own brief', 'hidden request notes');
        $otherRequestId = $this->request($otherCustomerId, 'Other brief', 'other hidden notes');
        [$ownQuoteId, $ownProjectId] = $this->quoteAndProject($ownCustomerId, 'Visible client update', 'hidden production notes');
        [$otherQuoteId, $otherProjectId] = $this->quoteAndProject($otherCustomerId, 'Other update', 'other hidden notes');

        $dashboard = $this->actingAs($user)->get('/portal');
        $dashboard->assertOK();
        $this->assertStringContainsString('no-store', $dashboard->response()->getHeaderLine('Cache-Control'));
        $dashboard->assertHeader('Referrer-Policy', 'no-referrer');
        $dashboard->assertSee('Own brief');
        $dashboard->assertDontSee('Other brief');
        $request = $this->actingAs($user)->get('/portal/requests/' . $ownRequestId);
        $request->assertOK();
        $request->assertDontSee('hidden request notes');
        $quote = $this->actingAs($user)->get('/portal/quotes/' . $ownQuoteId);
        $quote->assertOK();
        $quote->assertSee('Brand identity');
        $project = $this->actingAs($user)->get('/portal/projects/' . $ownProjectId);
        $project->assertOK();
        $project->assertSee('Visible client update');
        $project->assertDontSee('hidden production notes');
        $profile = $this->actingAs($user)->get('/portal/profile');
        $profile->assertOK();
        $profile->assertDontSee('private customer notes');
        $this->assertNotFound(fn () => $this->actingAs($user)->get('/portal/requests/' . $otherRequestId));
        $this->assertNotFound(fn () => $this->actingAs($user)->get('/portal/quotes/' . $otherQuoteId));
        $this->assertNotFound(fn () => $this->actingAs($user)->get('/portal/projects/' . $otherProjectId));
    }

    public function testPortalRequiresCustomerAuthentication(): void
    {
        $this->get('/portal')->assertRedirectTo('/login');
        $this->actingAs($this->user('user'))->get('/portal')->assertRedirect();
    }

    public function testAdminEmailEditKeepsLinkedPortalLoginInSync(): void
    {
        $portalUser = $this->user('customer', 'before@example.com');
        $customerId = $this->customer('before@example.com', $portalUser->id);

        $this->actingAs($this->user('admin'))->post('/admin/customers/' . $customerId, [
            csrf_token() => csrf_hash(),
            'name' => 'Avery Client',
            'business_name' => 'Avery Studio',
            'email' => 'after@example.com',
            'phone' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'city' => '',
            'region' => '',
            'postal_code' => '',
            'country' => '',
            'notes' => '',
        ])->assertRedirectTo('/admin/customers/' . $customerId);

        $this->assertNull(auth()->getProvider()->findByCredentials(['email' => 'before@example.com']));
        $this->assertSame((int) $portalUser->id, (int) auth()->getProvider()->findByCredentials(['email' => 'after@example.com'])->id);
    }

    private function customer(string $email, ?int $userId = null, ?string $notes = null): int
    {
        return (int) (new CustomerModel())->insert([
            'user_id' => $userId,
            'name' => 'Avery Client',
            'business_name' => 'Avery Studio',
            'email' => $email,
            'normalized_email' => strtolower($email),
            'notes' => $notes,
        ]);
    }

    private function request(int $customerId, string $summary, string $internalNotes): int
    {
        return (int) (new QuoteRequestModel())->insert([
            'reference_number' => 'PR-' . strtoupper(bin2hex(random_bytes(7))),
            'customer_id' => $customerId,
            'name' => 'Avery Client',
            'email' => 'request-' . bin2hex(random_bytes(3)) . '@example.com',
            'normalized_email' => 'request-' . bin2hex(random_bytes(3)) . '@example.com',
            'preferred_contact' => 'email',
            'project_summary' => $summary,
            'status' => 'ready_to_quote',
            'internal_notes' => $internalNotes,
            'owner_notification_status' => 'not_configured',
            'customer_notification_status' => 'not_configured',
        ]);
    }

    /** @return array{int, int} */
    private function quoteAndProject(int $customerId, string $customerUpdate, string $notes): array
    {
        $quoteId = (int) (new QuoteModel())->insert([
            'quote_number' => 'Q-' . strtoupper(bin2hex(random_bytes(6))),
            'access_token' => bin2hex(random_bytes(32)),
            'customer_id' => $customerId,
            'customer_name' => 'Avery Client',
            'customer_email' => 'avery@example.com',
            'quote_date' => date('Y-m-d'),
            'expires_on' => date('Y-m-d', strtotime('+14 days')),
            'status' => 'accepted',
            'subtotal' => '1200.00',
            'discount_amount' => '0.00',
            'tax_rate' => '0.000',
            'tax_amount' => '0.00',
            'total' => '1200.00',
            'deposit_percentage' => '50.00',
            'deposit_amount' => '600.00',
            'version' => 1,
            'delivery_status' => 'sent',
        ]);
        (new QuoteLineItemModel())->insert([
            'quote_id' => $quoteId,
            'description' => 'Brand identity',
            'quantity' => '1.00',
            'unit_price' => '1200.00',
            'line_total' => '1200.00',
            'sort_order' => 0,
        ]);
        $projectId = (int) (new ProjectModel())->insert([
            'project_number' => 'PRJ-' . strtoupper(bin2hex(random_bytes(5))),
            'quote_id' => $quoteId,
            'customer_id' => $customerId,
            'name' => 'Avery identity rollout',
            'status' => 'in_progress',
            'notes' => $notes,
            'customer_update' => $customerUpdate,
        ]);

        return [$quoteId, $projectId];
    }

    private function user(string $group, ?string $email = null, string $password = 'StrongExamplePassword123!'): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-portal-' . bin2hex(random_bytes(4)),
            'email' => $email ?? $group . '-portal-' . bin2hex(random_bytes(4)) . '@example.com',
            'password' => $password,
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }

    private function assertNotFound(callable $request): void
    {
        try {
            $request();
            $this->fail('Expected a page-not-found response.');
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
