<?php

use App\Libraries\QuoteQuestionCatalog;
use App\Models\QuestionGroupServiceModel;
use App\Models\QuoteQuestionGroupModel;
use App\Models\QuoteQuestionModel;
use App\Models\QuoteQuestionOptionModel;
use App\Models\ServiceModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class QuoteQuestionsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        auth('session')->logout();
    }

    public function testOnlyAdminsCanManageQuoteQuestions(): void
    {
        $this->get('/admin/question-groups')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/question-groups')->assertRedirect();
    }

    public function testAdminCanCreateAssignedGroupAndChoiceQuestion(): void
    {
        $brandServiceId = $this->service('Brand Identity');
        $printServiceId = $this->service('Print Design');
        $webServiceId = $this->service('Web Design');
        $admin = $this->createUser('admin');

        $groupInput = $this->groupInput();
        $groupInput['service_ids'] = [(string) $brandServiceId, (string) $printServiceId];
        $this->actingAs($admin)->post('/admin/question-groups', $groupInput)->assertRedirect();

        $group = (new QuoteQuestionGroupModel())->where('name', 'Brand information')->first();
        $this->assertNotNull($group);
        $this->assertSame(
            [$brandServiceId, $printServiceId],
            array_map('intval', array_column(
                (new QuestionGroupServiceModel())->where('group_id', $group['id'])->orderBy('service_id')->findAll(),
                'service_id',
            )),
        );

        $questionInput = $this->questionInput();
        $this->actingAs($admin)->post('/admin/question-groups/' . $group['id'] . '/questions', $questionInput)
            ->assertRedirectTo('/admin/question-groups/' . $group['id'] . '/edit');

        $question = (new QuoteQuestionModel())->where('group_id', $group['id'])->first();
        $this->assertSame('select', $question['field_type']);
        $this->assertSame(1, (int) $question['is_required']);
        $this->assertSame(
            ['Modern', 'Classic', 'Playful'],
            array_column((new QuoteQuestionOptionModel())->where('question_id', $question['id'])->orderBy('sort_order')->findAll(), 'label'),
        );

        $brandCatalog = (new QuoteQuestionCatalog())->forServices([$brandServiceId]);
        $this->assertSame('Brand information', $brandCatalog[0]['name']);
        $this->assertSame('What visual direction fits best?', $brandCatalog[0]['questions'][0]['label']);
        $this->assertSame('Modern', $brandCatalog[0]['questions'][0]['options'][0]['label']);
        $this->assertSame('Brand information', (new QuoteQuestionCatalog())->forServices([$printServiceId])[0]['name']);
        $this->assertSame([], (new QuoteQuestionCatalog())->forServices([$webServiceId]));
    }

    public function testGeneralGroupsApplyWithoutAServiceAssignment(): void
    {
        $serviceId = $this->service('Packaging');
        $groupId = (int) (new QuoteQuestionGroupModel())->insert([
            'name' => 'Project timing',
            'description' => null,
            'is_active' => 1,
            'sort_order' => 1,
        ]);
        (new QuoteQuestionModel())->insert([
            'group_id' => $groupId,
            'label' => 'When do you need the work?',
            'field_type' => 'date',
            'is_required' => 1,
            'help_text' => null,
            'placeholder' => null,
            'is_active' => 1,
            'sort_order' => 0,
        ]);

        $this->assertSame('Project timing', (new QuoteQuestionCatalog())->forServices([])[0]['name']);
        $this->assertSame('Project timing', (new QuoteQuestionCatalog())->forServices([$serviceId])[0]['name']);
    }

    public function testInvalidAssignmentsAndChoiceConfigurationAreRejected(): void
    {
        $admin = $this->createUser('admin');
        $groupInput = $this->groupInput();
        $groupInput['service_ids'] = ['999999'];
        $this->actingAs($admin)->post('/admin/question-groups', $groupInput)->assertRedirect();
        $this->assertSame(0, (new QuoteQuestionGroupModel())->countAllResults());

        $groupId = (int) (new QuoteQuestionGroupModel())->insert([
            'name' => 'Business information',
            'description' => null,
            'is_active' => 1,
            'sort_order' => 0,
        ]);
        $questionInput = $this->questionInput();
        $questionInput['options'] = "Only one";
        $this->actingAs($admin)->post('/admin/question-groups/' . $groupId . '/questions', $questionInput)->assertRedirect();
        $this->assertSame(0, (new QuoteQuestionModel())->countAllResults());
    }

    public function testChangingAChoiceQuestionToTextClearsOptionsAndInactiveQuestionsAreExcluded(): void
    {
        $groupId = (int) (new QuoteQuestionGroupModel())->insert([
            'name' => 'Audience',
            'description' => null,
            'is_active' => 1,
            'sort_order' => 0,
        ]);
        $questionId = (int) (new QuoteQuestionModel())->insert([
            'group_id' => $groupId,
            'label' => 'Choose an audience',
            'field_type' => 'radio',
            'is_required' => 0,
            'help_text' => null,
            'placeholder' => null,
            'is_active' => 1,
            'sort_order' => 0,
        ]);
        (new QuoteQuestionOptionModel())->insert(['question_id' => $questionId, 'label' => 'Consumer', 'sort_order' => 0]);
        (new QuoteQuestionOptionModel())->insert(['question_id' => $questionId, 'label' => 'Business', 'sort_order' => 1]);

        $input = $this->questionInput();
        $input['label'] = 'Describe the target audience';
        $input['field_type'] = 'text';
        $input['is_active'] = '0';
        $input['options'] = "Ignored\nValues";
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->post('/admin/question-groups/' . $groupId . '/questions/' . $questionId, $input)
            ->assertRedirectTo('/admin/question-groups/' . $groupId . '/edit');

        $question = (new QuoteQuestionModel())->find($questionId);
        $this->assertSame('text', $question['field_type']);
        $this->assertSame(0, (int) $question['is_active']);
        $this->assertSame(0, (new QuoteQuestionOptionModel())->where('question_id', $questionId)->countAllResults());
        $this->assertSame([], (new QuoteQuestionCatalog())->forServices([]));
    }

    private function service(string $name): int
    {
        return (int) (new ServiceModel())->insert([
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'name' => $name,
            'summary' => 'A clear service summary.',
            'description' => 'A detailed service description.',
            'starting_price' => null,
            'currency_code' => 'USD',
            'show_price' => 0,
            'status' => 'published',
            'sort_order' => 0,
        ]);
    }

    /** @return array<string, mixed> */
    private function groupInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'name' => 'Brand information',
            'description' => 'Help us understand the desired brand direction.',
            'is_active' => '1',
            'sort_order' => '2',
        ];
    }

    /** @return array<string, mixed> */
    private function questionInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'label' => 'What visual direction fits best?',
            'field_type' => 'select',
            'help_text' => 'Choose the closest option.',
            'placeholder' => 'Select a direction',
            'options' => "Modern\nClassic\nPlayful",
            'is_required' => '1',
            'is_active' => '1',
            'sort_order' => '3',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $suffix = bin2hex(random_bytes(3));
        $provider->save(new User([
            'username' => $group . '-questions-' . $suffix,
            'email' => $group . '-questions-' . $suffix . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
