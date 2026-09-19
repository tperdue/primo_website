<?php

use App\Models\MediaAssetModel;
use App\Models\PortfolioMediaModel;
use App\Models\PortfolioProjectModel;
use App\Models\PortfolioServiceModel;
use App\Models\ServiceMediaModel;
use App\Models\ServiceModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class MediaLibraryTest extends CIUnitTestCase
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

    public function testOnlyAdminCanManageMedia(): void
    {
        $this->get('/admin/media')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/media')->assertRedirect();
    }

    public function testAdminCanEditAltTextAndDeleteOnlyUnusedMedia(): void
    {
        $assetId = $this->media('Original description');
        $serviceId = $this->service('Brand systems');
        (new ServiceMediaModel())->insert(['service_id' => $serviceId, 'media_id' => $assetId]);
        $admin = $this->createUser('admin');

        $this->actingAs($admin)->post('/admin/media/' . $assetId, [
            csrf_token() => csrf_hash(),
            'alt_text' => 'Updated accessible description',
        ])->assertRedirectTo('/admin/media/' . $assetId . '/edit');
        $this->assertSame('Updated accessible description', (new MediaAssetModel())->find($assetId)['alt_text']);

        $this->actingAs($admin)->post('/admin/media/' . $assetId . '/delete', [csrf_token() => csrf_hash()])->assertRedirectTo('/admin/media/' . $assetId . '/edit');
        $this->assertNotNull((new MediaAssetModel())->find($assetId));

        (new ServiceMediaModel())->where('service_id', $serviceId)->delete();
        $this->actingAs($admin)->post('/admin/media/' . $assetId . '/delete', [csrf_token() => csrf_hash()])->assertRedirectTo('/admin/media');
        $this->assertNull((new MediaAssetModel())->find($assetId));
    }

    public function testAdminRelationshipsRenderAcrossPublicServiceAndProjectPages(): void
    {
        $featuredId = $this->media('Strategy workshop materials');
        $galleryId = $this->media('Identity applications across packaging');
        $admin = $this->createUser('admin');

        $serviceInput = $this->serviceInput();
        $serviceInput['featured_media_id'] = (string) $featuredId;
        $this->actingAs($admin)->post('/admin/services', $serviceInput)->assertRedirectTo('/admin/services');
        $service = (new ServiceModel())->where('slug', 'brand-strategy')->first();
        $this->assertSame($featuredId, (int) (new ServiceMediaModel())->find($service['id'])['media_id']);

        $projectInput = $this->projectInput();
        $projectInput['featured_media_id'] = (string) $featuredId;
        $projectInput['gallery_media_ids'] = [(string) $featuredId, (string) $galleryId];
        $projectInput['gallery_sort_order'] = [(string) $featuredId => '2', (string) $galleryId => '1'];
        $projectInput['service_ids'] = [(string) $service['id']];
        $this->actingAs($admin)->post('/admin/portfolio', $projectInput)->assertRedirectTo('/admin/portfolio');

        $project = (new PortfolioProjectModel())->where('slug', 'north-star-identity')->first();
        $gallery = (new PortfolioMediaModel())->where('project_id', $project['id'])->orderBy('sort_order')->findAll();
        $this->assertSame($galleryId, (int) $gallery[0]['media_id']);
        $this->assertSame($service['id'], (new PortfolioServiceModel())->where('project_id', $project['id'])->first()['service_id']);

        $servicePage = $this->get('/services/brand-strategy');
        $servicePage->assertSee('Strategy workshop materials');
        $servicePage->assertSee('North Star Identity');

        $projectPage = $this->get('/work/north-star-identity');
        $projectPage->assertSee('Identity applications across packaging');
        $projectPage->assertSee('Brand Strategy');
        $projectPage->assertDontSee('Strategy workshop materials', '.work-gallery-grid');
    }

    public function testInvalidRelationshipIdsAreRejected(): void
    {
        $admin = $this->createUser('admin');
        $input = $this->projectInput();
        $input['gallery_media_ids'] = ['999999'];
        $input['gallery_sort_order'] = ['999999' => '0'];

        $this->actingAs($admin)->post('/admin/portfolio', $input)->assertRedirect();
        $this->assertSame(0, (new PortfolioProjectModel())->countAllResults());
    }

    private function media(string $alt): int
    {
        return (int) (new MediaAssetModel())->insert([
            'path' => 'assets/images/concept-study.png',
            'alt_text' => $alt,
            'mime_type' => 'image/png',
            'byte_size' => 1000,
        ]);
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

    /** @return array<string, string> */
    private function serviceInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'name' => 'Brand Strategy',
            'summary' => 'Direction for a coherent brand.',
            'description' => 'Research, positioning, and practical creative direction.',
            'starting_price' => '',
            'currency_code' => 'USD',
            'show_price' => '0',
            'status' => 'published',
            'sort_order' => '1',
        ];
    }

    /** @return array<string, mixed> */
    private function projectInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'title' => 'North Star Identity',
            'client_name' => 'North Star',
            'summary' => 'A clear identity for a growing company.',
            'challenge' => 'The company needed a focused visual direction.',
            'solution' => 'We created a flexible identity system.',
            'project_date' => '2026-08-01',
            'status' => 'published',
            'sort_order' => '1',
            'is_featured' => '0',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $suffix = bin2hex(random_bytes(3));
        $provider->save(new User([
            'username' => $group . '-media-' . $suffix,
            'email' => $group . '-media-' . $suffix . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
