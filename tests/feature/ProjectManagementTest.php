<?php

use App\Models\CustomerModel;
use App\Models\ProjectModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class ProjectManagementTest extends CIUnitTestCase
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

    public function testAcceptedQuoteCreatesExactlyOneSeededProject(): void
    {
        $quote = $this->quote('accepted');
        $admin = $this->createUser('admin');

        $this->actingAs($admin)->post('/admin/quotes/' . $quote['id'] . '/project', [csrf_token() => csrf_hash()]);

        $project = (new ProjectModel())->where('quote_id', $quote['id'])->first();
        $this->assertNotNull($project);
        $this->assertSame('Brand identity for Avery Studio', $project['name']);
        $this->assertSame('awaiting_deposit', $project['status']);
        $this->assertSame('PRJ-' . date('Y') . '-' . str_pad((string) $project['id'], 6, '0', STR_PAD_LEFT), $project['project_number']);

        $this->actingAs($admin)->post('/admin/quotes/' . $quote['id'] . '/project', [csrf_token() => csrf_hash()])
            ->assertRedirectTo('/admin/projects/' . $project['id'] . '/edit');
        $this->assertSame(1, (new ProjectModel())->where('quote_id', $quote['id'])->countAllResults());
    }

    public function testNonAcceptedQuoteCannotCreateProject(): void
    {
        $quote = $this->quote('sent');

        $this->actingAs($this->createUser('admin'))->post('/admin/quotes/' . $quote['id'] . '/project', [csrf_token() => csrf_hash()])
            ->assertRedirectTo('/admin/quotes/' . $quote['id'] . '/edit');

        $this->assertSame(0, (new ProjectModel())->countAllResults());
    }

    public function testAdminCanUpdateProjectAndInvalidScheduleIsRejected(): void
    {
        $quote = $this->quote('accepted');
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->post('/admin/quotes/' . $quote['id'] . '/project', [csrf_token() => csrf_hash()]);
        $project = (new ProjectModel())->where('quote_id', $quote['id'])->first();

        $valid = $this->projectInput();
        $this->actingAs($admin)->post('/admin/projects/' . $project['id'], $valid)
            ->assertRedirectTo('/admin/projects/' . $project['id'] . '/edit');
        $updated = (new ProjectModel())->find($project['id']);
        $this->assertSame('in_progress', $updated['status']);
        $this->assertSame('2026-10-30', $updated['due_date']);

        $invalid = $this->projectInput();
        $invalid['start_date'] = '2026-11-01';
        $invalid['due_date'] = '2026-10-01';
        $this->actingAs($admin)->post('/admin/projects/' . $project['id'], $invalid)->assertRedirect();
        $this->assertSame('2026-10-30', (new ProjectModel())->find($project['id'])['due_date']);
    }

    public function testProjectAdministrationRequiresAdminAccess(): void
    {
        $this->get('/admin/projects')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/projects')->assertRedirect();
    }

    /** @return array<string, mixed> */
    private function quote(string $status): array
    {
        $customerId = (int) (new CustomerModel())->insert([
            'name' => 'Avery Client',
            'business_name' => 'Avery Studio',
            'email' => 'avery-project@example.com',
            'normalized_email' => 'avery-project@example.com',
        ]);
        $quoteId = (int) (new QuoteModel())->insert([
            'quote_number' => 'Q-2026-' . strtoupper(bin2hex(random_bytes(4))),
            'access_token' => bin2hex(random_bytes(32)),
            'customer_id' => $customerId,
            'quote_request_id' => null,
            'customer_name' => 'Avery Client',
            'customer_business_name' => 'Avery Studio',
            'customer_email' => 'avery-project@example.com',
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
            'delivery_status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'responded_at' => $status === 'accepted' ? date('Y-m-d H:i:s') : null,
        ]);
        (new QuoteLineItemModel())->insert([
            'quote_id' => $quoteId,
            'description' => 'Brand identity',
            'details' => null,
            'quantity' => '1.00',
            'unit_price' => '1200.00',
            'line_total' => '1200.00',
            'sort_order' => 0,
        ]);

        return (new QuoteModel())->find($quoteId);
    }

    /** @return array<string, string> */
    private function projectInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'name' => 'Avery identity rollout',
            'start_date' => '2026-10-01',
            'due_date' => '2026-10-30',
            'status' => 'in_progress',
            'notes' => 'Internal production notes.',
            'customer_update' => 'Initial concepts are underway.',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-project-manager-' . bin2hex(random_bytes(3)),
            'email' => $group . '-project-manager-' . bin2hex(random_bytes(3)) . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
