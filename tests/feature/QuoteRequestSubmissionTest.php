<?php

use App\Libraries\QuoteCart;
use App\Libraries\QuoteRequestSubmission;
use App\Models\QuoteQuestionGroupModel;
use App\Models\QuoteQuestionModel;
use App\Models\QuoteRequestAnswerModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use App\Models\ServiceModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class QuoteRequestSubmissionTest extends CIUnitTestCase
{
    use AuthenticationTesting;
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        auth('session')->logout();
        (new QuoteCart())->clear();
        service('cache')->clean();
        service('superglobals')->setGlobalArray('post', []);
        service('throttler')->remove('quote-submit-' . hash('sha256', service('request')->getIPAddress()));
    }

    public function testValidRequestCreatesImmutableSnapshotsAndClearsCart(): void
    {
        $serviceId = $this->service('Identity Design');
        $questionId = $this->question('What should the brand communicate?');
        (new QuoteCart())->add($serviceId);

        $result = $this->withSession($_SESSION)->post('/quote/submit', $this->requestInput($questionId));

        $request = (new QuoteRequestModel())->first();
        $this->assertNotNull($request);
        $result->assertRedirectTo('/quote/received/' . $request['reference_number']);
        $this->assertMatchesRegularExpression('/^PR-\d{8}-[A-F0-9]{12}$/', $request['reference_number']);
        $this->assertSame('new', $request['status']);
        $this->assertSame('not_configured', $request['owner_notification_status']);
        $this->assertSame('not_configured', $request['customer_notification_status']);
        $this->assertSame([], (new QuoteCart())->serviceIds());

        $serviceSnapshot = (new QuoteRequestServiceModel())->where('quote_request_id', $request['id'])->first();
        $answerSnapshot = (new QuoteRequestAnswerModel())->where('quote_request_id', $request['id'])->first();
        $this->assertSame('Identity Design', $serviceSnapshot['service_name']);
        $this->assertSame('Build trust with a warmer visual system.', $answerSnapshot['answer_text']);

        (new ServiceModel())->update($serviceId, ['name' => 'Renamed Service']);
        (new QuoteQuestionModel())->update($questionId, ['label' => 'A replacement question']);
        $this->assertSame('Identity Design', (new QuoteRequestServiceModel())->find($serviceSnapshot['id'])['service_name']);
        $this->assertSame('What should the brand communicate?', (new QuoteRequestAnswerModel())->find($answerSnapshot['id'])['question_label']);
    }

    public function testMissingRequiredAnswerDoesNotStoreRequestOrClearCart(): void
    {
        $serviceId = $this->service('Packaging Design');
        $questionId = $this->question('What is the product name?');
        (new QuoteCart())->add($serviceId);
        $input = $this->requestInput($questionId);
        $input['answers'][$questionId] = '';

        $this->withSession($_SESSION)->post('/quote/submit', $input)->assertRedirect();

        $this->assertSame(0, (new QuoteRequestModel())->countAllResults());
        $this->assertSame([$serviceId], (new QuoteCart())->serviceIds());
    }

    public function testAdminCanReviewAndUpdateQuoteWorkflow(): void
    {
        $requestId = (int) (new QuoteRequestModel())->insert([
            'reference_number' => 'PR-20260919-ABCDEF12',
            'name' => 'Avery Client',
            'company' => 'Avery Studio',
            'email' => 'avery@example.com',
            'phone' => null,
            'preferred_contact' => 'email',
            'project_summary' => 'We need a cohesive launch identity for a new consumer brand.',
            'desired_completion_date' => null,
            'budget_range' => null,
            'additional_notes' => null,
            'status' => 'new',
            'owner_notification_status' => 'not_configured',
            'customer_notification_status' => 'not_configured',
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->get('/admin/quote-requests')->assertRedirectTo('/login');
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->get('/admin/quote-requests')->assertSee('PR-20260919-ABCDEF12');
        $this->actingAs($admin)->get('/admin/quote-requests/' . $requestId)->assertSee('Avery Studio');

        $this->actingAs($admin)->post('/admin/quote-requests/' . $requestId, [
            csrf_token() => csrf_hash(),
            'status' => 'closed_quote_created',
            'internal_notes' => 'Prepare a two-option scope.',
        ])->assertRedirectTo('/admin/quote-requests/' . $requestId);

        $updated = (new QuoteRequestModel())->find($requestId);
        $this->assertSame('closed_quote_created', $updated['status']);
        $this->assertSame('Prepare a two-option scope.', $updated['internal_notes']);
    }

    public function testNonAdminCannotOpenQuoteInbox(): void
    {
        $this->actingAs($this->createUser('user'))->get('/admin/quote-requests')->assertRedirect();
    }

    public function testRequiredFileQuestionRejectsAMissingAttachment(): void
    {
        $errors = (new QuoteRequestSubmission())->validate([[
            'name' => 'Reference material',
            'questions' => [[
                'id' => 91,
                'label' => 'Existing brand files',
                'field_type' => 'file',
                'is_required' => 1,
                'options' => [],
            ]],
        ]], [], []);

        $this->assertSame('Existing brand files is required.', $errors['files.91']);
    }

    private function service(string $name): int
    {
        return (int) (new ServiceModel())->insert([
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'name' => $name,
            'summary' => 'A focused design service.',
            'description' => 'A complete and carefully managed design engagement.',
            'starting_price' => '900.00',
            'currency_code' => 'USD',
            'show_price' => 1,
            'status' => 'published',
            'sort_order' => 0,
        ]);
    }

    private function question(string $label): int
    {
        $groupId = (int) (new QuoteQuestionGroupModel())->insert([
            'name' => 'Project direction',
            'description' => 'Context for the project.',
            'is_active' => 1,
            'sort_order' => 0,
        ]);

        return (int) (new QuoteQuestionModel())->insert([
            'group_id' => $groupId,
            'label' => $label,
            'field_type' => 'textarea',
            'is_required' => 1,
            'help_text' => null,
            'placeholder' => null,
            'is_active' => 1,
            'sort_order' => 0,
        ]);
    }

    /** @return array<string, mixed> */
    private function requestInput(int $questionId): array
    {
        return [
            csrf_token() => csrf_hash(),
            'answers' => [$questionId => 'Build trust with a warmer visual system.'],
            'name' => 'Avery Client',
            'company' => 'Avery Studio',
            'email' => 'avery@example.com',
            'phone' => '',
            'preferred_contact' => 'email',
            'project_summary' => 'We are preparing a new consumer brand and need a complete visual direction.',
            'desired_completion_date' => '',
            'budget_range' => '$1,500-$3,000',
            'additional_notes' => '',
            'consent' => '1',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-quote-tester-' . bin2hex(random_bytes(3)),
            'email' => $group . '-quote-' . bin2hex(random_bytes(3)) . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
