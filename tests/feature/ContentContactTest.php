<?php

use App\Models\ContactSubmissionModel;
use App\Models\ContentPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class ContentContactTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        auth('session')->logout();
        service('cache')->clean();
        service('superglobals')->setGlobalArray('post', []);
        service('throttler')->remove('contact-submit-' . hash('sha256', service('request')->getIPAddress()));
    }

    public function testPublishedPageUsesManagedEscapedContentAndSeoMetadata(): void
    {
        $model = new ContentPageModel();
        $about = $model->where('slug', 'about')->first();
        $model->update($about['id'], [
            'title' => 'A studio with purpose',
            'eyebrow' => 'About us',
            'summary' => 'Independent and thoughtful.',
            'body' => '<script>alert(1)</script>',
            'meta_title' => 'Our studio',
            'meta_description' => 'Meet the studio behind the work.',
            'status' => 'published',
        ]);

        $result = $this->get('/about');

        $result->assertOK();
        $result->assertSee('A studio with purpose');
        $result->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
        $result->assertDontSee('<script>alert(1)</script>');
        $result->assertSee('rel="canonical" href="http://localhost:8080/about"');
        $result->assertSee('property="og:description" content="Meet the studio behind the work."');
    }

    public function testDraftLegalPageIsHiddenUntilAdminPublishesIt(): void
    {
        try {
            $this->get('/privacy');
            $this->fail('Expected the draft legal page to be unavailable.');
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }

        $page = (new ContentPageModel())->where('slug', 'privacy')->first();
        $input = $page;
        $input[csrf_token()] = csrf_hash();
        $input['body'] = 'Approved privacy language for this deployment.';
        $input['status'] = 'published';

        $this->actingAs($this->createUser('admin'))->post('/admin/pages/' . $page['id'], $input)->assertRedirectTo('/admin/pages');
        $this->get('/privacy')->assertSee('Approved privacy language for this deployment.');
    }

    public function testContactSubmissionIsValidatedStoredAndEscapedInAdmin(): void
    {
        $input = $this->contactInput();
        $input['message'] = 'I need a campaign system. <script>alert(1)</script>';

        $result = $this->post('/contact', $input);

        $result->assertRedirectTo('/contact');
        $submission = (new ContactSubmissionModel())->first();
        $this->assertSame('new', $submission['status']);
        $this->assertSame('not_configured', $submission['notification_status']);

        $adminResult = $this->actingAs($this->createUser('admin'))->get('/admin/contacts/' . $submission['id']);
        $adminResult->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
        $adminResult->assertDontSee('<script>alert(1)</script>');
    }

    public function testInvalidContactSubmissionIsNotStored(): void
    {
        $input = $this->contactInput();
        $input['email'] = 'not-an-email';
        $input['message'] = 'Too short';

        $this->post('/contact', $input)->assertRedirect();

        $this->assertSame(0, (new ContactSubmissionModel())->countAllResults());
    }

    public function testHoneypotRejectsBotSubmission(): void
    {
        $input = $this->contactInput();
        $input['honeypot'] = 'filled by a bot';

        try {
            $this->post('/contact', $input);
            $this->fail('Expected the honeypot filter to reject the submission.');
        } catch (HoneypotException) {
            $this->assertSame(0, (new ContactSubmissionModel())->countAllResults());
        }
    }

    public function testContactFormHasHoneypotAndRateLimitsRepeatedSubmissions(): void
    {
        $this->get('/contact')->assertSee('name="honeypot"');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $result = $this->post('/contact', $this->contactInput('Attempt ' . $attempt));
            $this->assertSame(302, $result->response()->getStatusCode(), 'Unexpected response for rate-limit attempt ' . $attempt);
        }

        $result = $this->post('/contact', $this->contactInput('Attempt 6'));
        $result->assertStatus(429);
        $result->assertHeader('Retry-After', '900');
        $this->assertSame(5, (new ContactSubmissionModel())->countAllResults());
    }

    public function testOnlyAdminCanManagePagesAndContactInbox(): void
    {
        $this->get('/admin/pages')->assertRedirectTo('/login');
        $this->get('/admin/contacts')->assertRedirectTo('/login');

        $user = $this->createUser('user');
        $this->actingAs($user)->get('/admin/pages')->assertRedirect();
        $this->actingAs($user)->get('/admin/contacts')->assertRedirect();
    }

    public function testAdminCanUpdateContactStatus(): void
    {
        $id = (new ContactSubmissionModel())->insert([
            'name' => 'Avery Client',
            'email' => 'avery@example.com',
            'phone' => null,
            'subject' => 'Identity system',
            'message' => 'We need a flexible identity system for a new venture.',
            'status' => 'new',
            'notification_status' => 'not_configured',
        ]);

        $result = $this->actingAs($this->createUser('admin'))->post('/admin/contacts/' . $id, [
            csrf_token() => csrf_hash(),
            'status' => 'closed',
        ]);

        $result->assertRedirectTo('/admin/contacts/' . $id);
        $this->assertSame('closed', (new ContactSubmissionModel())->find($id)['status']);
    }

    public function testSitemapAndRobotsExposeOnlyPublishedPublicContent(): void
    {
        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOK();
        $sitemap->assertSee('http://localhost:8080/about');
        $sitemap->assertSee('http://localhost:8080/contact');
        $sitemap->assertSee('http://localhost:8080/quote');
        $sitemap->assertDontSee('http://localhost:8080/privacy');

        $robots = $this->get('/robots.txt');
        $robots->assertOK();
        $robots->assertSee('Disallow: /admin/');
        $robots->assertSee('Sitemap: http://localhost:8080/sitemap.xml');
    }

    /** @return array<string, string> */
    private function contactInput(string $subject = 'New brand project'): array
    {
        return [
            csrf_token() => csrf_hash(),
            'name' => 'Avery Client',
            'email' => 'avery@example.com',
            'phone' => '',
            'subject' => $subject,
            'message' => 'We are planning a new visual identity and would like to discuss the project.',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-content-tester-' . bin2hex(random_bytes(3)),
            'email' => $group . '-content-' . bin2hex(random_bytes(3)) . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
