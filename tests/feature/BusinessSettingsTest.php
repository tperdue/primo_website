<?php

use App\Models\BusinessSettingsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Test\AuthenticationTesting;

/** @internal */
final class BusinessSettingsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace = null;

    public function testHomeDisplaysStoredBusinessDetails(): void
    {
        (new BusinessSettingsModel())->update(1, [
            'business_name' => 'North Studio',
            'tagline' => 'Original work for growing brands.',
            'description' => 'We make identities and campaigns.',
            'contact_email' => 'hello@example.com',
        ]);

        $result = $this->get('/');

        $result->assertOK();
        $result->assertSee('North Studio');
        $result->assertSee('Original work for growing brands.');
        $result->assertSee('href="/contact"');
        $result->assertSee('assets/images/hero-concepts.png');
        $result->assertSee('assets/images/concept-study.png');
        $result->assertSee('class="mobile-menu"');
        $result->assertSee('assets/js/nav.js');
        $this->get('/contact')->assertSee('mailto:hello@example.com');
    }

    public function testGuestCannotOpenSettings(): void
    {
        $result = $this->get('/admin/settings');

        $result->assertRedirectTo('/login');
    }

    public function testSettingsModelOnlyPersistsAllowedFields(): void
    {
        $model = new BusinessSettingsModel();
        $model->update(1, [
            'business_name' => 'Changed Studio',
            'id' => 99,
        ]);

        $this->assertSame('Changed Studio', $model->find(1)['business_name']);
        $this->assertNull($model->find(99));
    }

    public function testOrdinaryUserCannotOpenSettings(): void
    {
        $user = $this->createUser('user');

        $result = $this->actingAs($user)->get('/admin/settings');

        $result->assertRedirect();
    }

    public function testAdminCanSaveSettings(): void
    {
        $admin = $this->createUser('admin');

        $result = $this->actingAs($admin)->post('/admin/settings', [
            csrf_token() => csrf_hash(),
            'business_name' => 'Orbit Studio',
            'tagline' => 'Design with direction.',
            'description' => 'Brand and digital design.',
            'contact_email' => 'hello@orbit.example',
            'notification_email' => 'inbox@orbit.example',
        ]);

        $result->assertRedirectTo('/admin/settings');
        $this->assertSame('Orbit Studio', (new BusinessSettingsModel())->find(1)['business_name']);
        $this->assertSame('inbox@orbit.example', (new BusinessSettingsModel())->find(1)['notification_email']);
    }

    public function testInvalidSettingsAreNotSaved(): void
    {
        $admin = $this->createUser('admin');

        $result = $this->actingAs($admin)->post('/admin/settings', [
            csrf_token() => csrf_hash(),
            'business_name' => '   ',
            'tagline' => 'Short',
            'description' => 'Description',
            'contact_email' => 'not-an-email',
        ]);

        $result->assertRedirect();
        $this->assertSame('Your design studio', (new BusinessSettingsModel())->find(1)['business_name']);
    }

    private function createUser(string $group): User
    {
        $provider = auth()->getProvider();
        $provider->save(new User([
            'username' => $group . '-tester',
            'email' => $group . '@example.com',
            'password' => 'StrongExamplePassword123!',
        ]));
        $user = $provider->findById($provider->getInsertID());
        $user->addGroup($group);

        return $user;
    }
}
