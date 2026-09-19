<?php

use App\Libraries\PortfolioImageUpload;
use App\Models\MediaAssetModel;
use App\Models\PortfolioProjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/** @internal */
final class PortfolioTest extends CIUnitTestCase
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

    public function testPublicWorkShowsOnlyPublishedProjectsAndEscapesContent(): void
    {
        $mediaId = $this->media('Brand identity display');
        $this->project('Visible', 'published', $mediaId, ['summary' => '<script>alert(1)</script>']);
        $this->project('Hidden', 'draft', $mediaId);
        $this->project('Retired', 'archived', $mediaId);

        $result = $this->get('/work');

        $result->assertOK();
        $result->assertSee('Visible');
        $result->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;');
        $result->assertDontSee('<script>alert(1)</script>');
        $result->assertDontSee('Hidden');
        $result->assertDontSee('Retired');
        $this->get('/work/visible')->assertSee('Brand identity display');
    }

    public function testDraftAndArchivedProjectsAreNotPublic(): void
    {
        $this->project('Hidden', 'draft');
        $this->project('Retired', 'archived');

        foreach (['hidden', 'retired', 'missing'] as $slug) {
            try {
                $this->get('/work/' . $slug);
                $this->fail('Expected the portfolio page to be unavailable.');
            } catch (PageNotFoundException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testOnlyAdminCanManagePortfolio(): void
    {
        $this->get('/admin/portfolio')->assertRedirectTo('/login');
        $this->get('/admin/portfolio/new')->assertRedirectTo('/login');
        $this->actingAs($this->createUser('user'))->get('/admin/portfolio')->assertRedirect();
    }

    public function testAdminCanCreateDraftThenPublishWithReusedImage(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin)->get('/admin/portfolio/new')->assertSee('Save project');
        $input = $this->projectInput();
        $result = $this->actingAs($admin)->post('/admin/portfolio', $input);

        $result->assertRedirectTo('/admin/portfolio');
        $project = (new PortfolioProjectModel())->where('slug', 'identity-refresh')->first();
        $this->assertNotNull($project);
        $this->assertNull($project['featured_media_id']);

        $input = $this->projectInput();
        $input['status'] = 'published';
        $result = $this->actingAs($admin)->post('/admin/portfolio/' . $project['id'], $input);
        $result->assertRedirect();
        $this->assertSame('draft', (new PortfolioProjectModel())->find($project['id'])['status']);

        $mediaId = $this->media('Original project presentation');
        $input = $this->projectInput();
        $input['status'] = 'published';
        $input['is_featured'] = '1';
        $input['featured_media_id'] = (string) $mediaId;
        $result = $this->actingAs($admin)->post('/admin/portfolio/' . $project['id'], $input);

        $result->assertRedirectTo('/admin/portfolio');
        $this->get('/work/identity-refresh')->assertSee('Original project presentation');
        $this->get('/')->assertSee('Identity Refresh');
    }

    public function testFeaturedHomepageExcludesDrafts(): void
    {
        $mediaId = $this->media('Portfolio image');
        $this->project('Visible', 'published', $mediaId, ['is_featured' => 1]);
        $this->project('Hidden', 'draft', $mediaId, ['is_featured' => 1]);

        $this->get('/')->assertSee('Visible');
        $this->get('/')->assertDontSee('Hidden');
    }

    public function testInvalidMediaSelectionAndDuplicateSlugAreRejected(): void
    {
        $admin = $this->createUser('admin');
        $this->project('Identity Refresh', 'draft');

        $this->actingAs($admin)->post('/admin/portfolio', $this->projectInput())->assertRedirect();

        $input = $this->projectInput();
        $input['title'] = 'Another project';
        $input['featured_media_id'] = '999999';
        $this->actingAs($admin)->post('/admin/portfolio', $input)->assertRedirect();
        $this->assertCount(1, (new PortfolioProjectModel())->findAll());
    }

    public function testNonUploadedFileIsRejectedBeforeStorage(): void
    {
        $file = new UploadedFile(__FILE__, 'project.png', 'image/png', filesize(__FILE__), UPLOAD_ERR_OK);
        $this->expectException(InvalidArgumentException::class);

        (new PortfolioImageUpload())->store($file, 'Project image');
    }

    public function testImageStorageUsesGeneratedPublicFilenameAndMediaRecord(): void
    {
        $source = FCPATH . 'assets/images/concept-study.png';
        $file = new class ($source, 'client-name.png', 'image/png', filesize($source), UPLOAD_ERR_OK) extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }

            public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
            {
                if (! is_dir($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }

                return copy($this->getTempName(), $targetPath . $name);
            }
        };

        $id = (new PortfolioImageUpload())->store($file, 'Printed design concepts');
        $media = (new MediaAssetModel())->find($id);
        $storedPath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $media['path']);

        try {
            $this->assertMatchesRegularExpression('~^uploads/portfolio/[a-f0-9]{32}\.png$~', $media['path']);
            $this->assertSame('Printed design concepts', $media['alt_text']);
            $this->assertFileExists($storedPath);
        } finally {
            @unlink($storedPath);
        }
    }

    public function testImageStorageRejectsDisguisedNonImage(): void
    {
        $file = new class (__FILE__, 'disguised.png', 'image/png', filesize(__FILE__), UPLOAD_ERR_OK) extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }
        };
        $this->expectException(InvalidArgumentException::class);

        (new PortfolioImageUpload())->store($file, 'Disguised file');
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

    private function project(string $title, string $status, ?int $mediaId = null, array $overrides = []): array
    {
        $model = new PortfolioProjectModel();
        $id = $model->insert($overrides + [
            'slug' => strtolower(str_replace(' ', '-', $title)),
            'title' => $title,
            'client_name' => null,
            'summary' => 'A focused design project.',
            'challenge' => 'A challenge to solve.',
            'solution' => 'A thoughtful design solution.',
            'project_date' => null,
            'featured_media_id' => $mediaId,
            'status' => $status,
            'is_featured' => 0,
            'sort_order' => 0,
        ]);

        return $model->find($id);
    }

    private function projectInput(): array
    {
        return [
            csrf_token() => csrf_hash(),
            'title' => 'Identity Refresh',
            'client_name' => '',
            'summary' => 'A fresh identity for a growing studio.',
            'challenge' => 'The old system lacked clarity.',
            'solution' => 'We designed a cohesive visual system.',
            'project_date' => '2026-06-01',
            'status' => 'draft',
            'sort_order' => '1',
            'is_featured' => '0',
        ];
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-portfolio-tester',
            'email' => $group . '-portfolio@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
