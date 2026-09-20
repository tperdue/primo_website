<?php

use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class ServicesTest extends CIUnitTestCase
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

    public function testPublicCatalogShowsOnlyPublishedServices(): void
    {
        $this->service('Visible', 'published');
        $this->service('Hidden', 'draft');
        $this->service('Retired', 'archived');

        $result = $this->get('/services');

        $result->assertOK();
        $result->assertSee('Visible');
        $result->assertDontSee('Hidden');
        $result->assertDontSee('Retired');
        $this->get('/')->assertSee('Visible');
        $this->get('/')->assertDontSee('Hidden');
    }

    public function testDraftAndArchivedDetailPagesAreNotPublic(): void
    {
        $this->service('Hidden', 'draft');
        $this->service('Retired', 'archived');

        foreach (['hidden', 'retired', 'missing'] as $slug) {
            $this->assertPublicPageMissing($slug);
        }
    }

    public function testPublicPriceRequiresVisibility(): void
    {
        $this->service('Visible', 'published', ['starting_price' => '750.00', 'show_price' => 1]);
        $this->service('Private price', 'published', ['starting_price' => '950.00', 'show_price' => 0]);

        $this->get('/services/visible')->assertSee('USD 750.00');
        $this->get('/services/private-price')->assertDontSee('950.00');
    }

    public function testOnlyAdminCanManageServices(): void
    {
        $this->get('/admin/')->assertRedirectTo('/login');
        $this->get('/admin/services')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/services')->assertRedirect();
    }

    public function testAdminOverviewShowsRealServiceCountsAndNavigation(): void
    {
        $this->service('Visible', 'published');
        $this->service('In progress', 'draft');
        $admin = $this->createUser('admin');

        $result = $this->actingAs($admin)->get('/admin/');

        $result->assertOK();
        $result->assertSee('Workspace overview');
        $result->assertSee('Catalog health');
        $result->assertSee('1 published service');
        $result->assertSee('1 service draft');
        $result->assertSee('href="/admin/services"');
        $result->assertSee('href="/admin/settings"');
        $result->assertSee('aria-current="page"');
    }

    public function testAdminCanOpenServiceEditor(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->get('/admin/services/new')->assertSee('Save service');
        $service = $this->service('Visible', 'draft');
        $this->actingAs($admin)->get('/admin/services/' . $service['id'] . '/edit')->assertSee('Visible');
    }

    public function testAdminCanCreateAndPublishService(): void
    {
        $admin = $this->createUser('admin');
        $result = $this->actingAs($admin)->post('/admin/services', $this->serviceInput());

        $result->assertRedirectTo('/admin/services');
        $service = (new ServiceModel())->where('slug', 'brand-identity')->first();
        $this->assertNotNull($service);
        $this->assertSame('published', $service['status']);
        $this->get('/services/brand-identity')->assertSee('Brand Identity');
    }

    public function testAdminCanUnpublishService(): void
    {
        $service = $this->service('Visible', 'published');
        $admin = $this->createUser('admin');
        $input = $this->serviceInput();
        $input['status'] = 'draft';
        $result = $this->actingAs($admin)->post('/admin/services/' . $service['id'], $input);

        $result->assertRedirectTo('/admin/services');
        $this->assertPublicPageMissing('visible');
    }

    public function testInvalidAndDuplicateServicesAreRejected(): void
    {
        $admin = $this->createUser('admin');
        $this->service('Brand Identity', 'draft');
        $result = $this->actingAs($admin)->post('/admin/services', $this->serviceInput());
        $result->assertRedirect();

        $input = $this->serviceInput();
        $input['starting_price'] = '';
        $result = $this->actingAs($admin)->post('/admin/services', $input);
        $result->assertRedirect();
        $input[csrf_token()] = csrf_hash();
        $input['starting_price'] = '100000000.00';
        $result = $this->actingAs($admin)->post('/admin/services', $input);
        $result->assertRedirect();
        $this->assertCount(1, (new ServiceModel())->findAll());
    }

    private function service(string $name, string $status, array $overrides = []): array
    {
        $model = new ServiceModel();
        $id = $model->insert($overrides + [
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

        return $model->find($id);
    }

    private function assertPublicPageMissing(string $slug): void
    {
        try {
            $this->get('/services/' . $slug);
            $this->fail('Expected the service page to be unavailable.');
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }

    private function serviceInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'name' => 'Brand Identity',
            'summary' => 'A cohesive identity for your business.',
            'description' => 'Strategy, identity, and useful brand assets.',
            'starting_price' => '750.00',
            'currency_code' => 'USD',
            'show_price' => '1',
            'status' => 'published',
            'sort_order' => '1',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-service-tester',
            'email' => $group . '-service@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
