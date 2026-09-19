<?php

use App\Libraries\QuoteCart;
use App\Libraries\QuoteQuestionCatalog;
use App\Models\QuestionGroupServiceModel;
use App\Models\QuoteQuestionGroupModel;
use App\Models\QuoteQuestionModel;
use App\Models\QuoteQuestionOptionModel;
use App\Models\ServiceModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class QuoteBuilderTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        (new QuoteCart())->clear();
        service('superglobals')->setGlobalArray('post', []);
    }

    public function testEmptyQuoteInvitesVisitorToBrowseServices(): void
    {
        $result = $this->get('/quote');

        $result->assertOK();
        $result->assertSee('Your quote is empty');
        $result->assertSee('href="/services"');
        $result->assertSee('aria-label="0 selected services"');
    }

    public function testPublishedServicesCanBeAddedOnceAndAppearAcrossPublicNavigation(): void
    {
        $serviceId = $this->service('Brand Identity');

        $this->post('/quote/services/' . $serviceId, [csrf_token() => csrf_hash()])->assertRedirectTo('/quote');
        $cart = new QuoteCart();
        $cart->clear();
        $this->assertTrue($cart->add($serviceId));
        $this->assertTrue($cart->add($serviceId));
        $this->assertSame([$serviceId], $cart->serviceIds());

        $quote = $this->withSession($_SESSION)->get('/quote');
        $quote->assertSee('Brand Identity');
        $quote->assertSee('1 service');
        $this->withSession($_SESSION)->get('/')->assertSee('aria-label="1 selected service"');
        $this->withSession($_SESSION)->get('/services')->assertSee('In quote');
    }

    public function testUnavailableServicesCannotRemainInTheCart(): void
    {
        $publishedId = $this->service('Available');
        $draftId = $this->service('Draft service', 'draft');

        $cart = new QuoteCart();
        $this->assertFalse($cart->add($draftId));
        $this->assertSame([], $cart->serviceIds());
        $this->post('/quote/services/' . $draftId, [csrf_token() => csrf_hash()])->assertRedirectTo('/services');

        $this->assertTrue($cart->add($publishedId));
        (new ServiceModel())->update($publishedId, ['status' => 'archived']);

        $this->assertSame([], $cart->serviceIds());
        $this->withSession($_SESSION)->get('/quote')->assertSee('Your quote is empty');
    }

    public function testBuilderShowsGeneralAndRelevantServiceQuestionsOnly(): void
    {
        $brandId = $this->service('Brand Identity');
        $printId = $this->service('Print Design');
        $this->questionGroup('Business information', 'What is your business name?');
        $brandQuestionId = $this->questionGroup('Brand direction', 'Which direction feels right?', $brandId, 'select');
        $this->questionGroup('Print details', 'What finished size do you need?', $printId);

        (new QuoteCart())->add($brandId);
        $result = $this->withSession($_SESSION)->get('/quote');

        $result->assertSee('Business information');
        $result->assertSee('What is your business name?');
        $result->assertSee('Brand direction');
        $result->assertSee('Which direction feels right?');
        $result->assertDontSee('Print details');
        $result->assertSee('name="answers[' . $brandQuestionId . ']"');
    }

    public function testDraftAnswersAreSanitizedAndSurviveCartChanges(): void
    {
        $brandId = $this->service('Brand Identity');
        $printId = $this->service('Print Design');
        $textQuestionId = $this->questionGroup('Business information', 'What should we call your business?');
        $choiceQuestionId = $this->questionGroup('Brand direction', 'Choose a direction', $brandId, 'select');
        $choice = (new QuoteQuestionOptionModel())->where('question_id', $choiceQuestionId)->first();

        $cart = new QuoteCart();
        $cart->add($brandId);
        $cart->add($printId);
        $submitted = [
            (string) $textQuestionId => '<script>alert(1)</script>',
            (string) $choiceQuestionId => (string) $choice['id'],
            '999999' => 'unknown question',
        ];
        $cart->saveAnswers($submitted, (new QuoteQuestionCatalog())->forServices($cart->serviceIds()));
        $this->withSession($_SESSION)->post('/quote/save', [
            csrf_token() => csrf_hash(),
            'answers' => $submitted,
            'next' => 'quote',
        ])->assertRedirectTo('/quote');

        $answers = $cart->answers();
        $this->assertSame('<script>alert(1)</script>', $answers[$textQuestionId]);
        $this->assertSame((string) $choice['id'], $answers[$choiceQuestionId]);
        $this->assertArrayNotHasKey(999999, $answers);
        $page = $this->withSession($_SESSION)->get('/quote');
        $page->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
        $page->assertDontSee('<script>alert(1)</script>');

        $this->withSession($_SESSION)->post('/quote/remove/' . $brandId, [csrf_token() => csrf_hash()])->assertRedirectTo('/quote');
        $cart->remove($brandId);
        $this->assertSame([$printId], $cart->serviceIds());
        $cart->add($brandId);
        $this->withSession($_SESSION)->get('/quote')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
        $this->assertSame((string) $choice['id'], $cart->answers()[$choiceQuestionId]);
    }

    public function testInvalidChoiceValuesAreDiscarded(): void
    {
        $serviceId = $this->service('Brand Identity');
        $questionId = $this->questionGroup('Brand direction', 'Choose a direction', $serviceId, 'radio');
        $cart = new QuoteCart();
        $cart->add($serviceId);
        $cart->saveAnswers(
            [(string) $questionId => '999999'],
            (new QuoteQuestionCatalog())->forServices($cart->serviceIds()),
        );

        $this->assertSame('', $cart->answers()[$questionId]);
    }

    private function service(string $name, string $status = 'published'): int
    {
        return (int) (new ServiceModel())->insert([
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'name' => $name,
            'summary' => 'A clear service summary.',
            'description' => 'A detailed service description.',
            'starting_price' => null,
            'currency_code' => 'USD',
            'show_price' => 0,
            'status' => $status,
            'sort_order' => 0,
        ]);
    }

    private function questionGroup(string $name, string $label, ?int $serviceId = null, string $type = 'text'): int
    {
        $groupId = (int) (new QuoteQuestionGroupModel())->insert([
            'name' => $name,
            'description' => 'Useful project context.',
            'is_active' => 1,
            'sort_order' => 0,
        ]);
        if ($serviceId !== null) {
            (new QuestionGroupServiceModel())->insert(['group_id' => $groupId, 'service_id' => $serviceId]);
        }
        $questionId = (int) (new QuoteQuestionModel())->insert([
            'group_id' => $groupId,
            'label' => $label,
            'field_type' => $type,
            'is_required' => 1,
            'help_text' => 'A little context helps.',
            'placeholder' => 'Your answer',
            'is_active' => 1,
            'sort_order' => 0,
        ]);
        if (in_array($type, ['select', 'radio', 'checkboxes'], true)) {
            (new QuoteQuestionOptionModel())->insert(['question_id' => $questionId, 'label' => 'Modern', 'sort_order' => 0]);
            (new QuoteQuestionOptionModel())->insert(['question_id' => $questionId, 'label' => 'Classic', 'sort_order' => 1]);
        }

        return $questionId;
    }
}
