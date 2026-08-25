<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_pages_index(): void
    {
        Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => 'Welcome to our restaurant story.',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/pages');

        $response->assertStatus(200);
        $response->assertSee('About Us');
        $response->assertSee('about-us');
    }

    public function test_admin_can_create_new_page(): void
    {
        $payload = [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'content' => '<p>Your privacy is important to us.</p>',
            'meta_title' => 'Privacy Policy - Restaurant',
            'meta_description' => 'Learn how we handle your personal information.',
            'is_published' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post('/admin/pages', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'meta_title' => 'Privacy Policy - Restaurant',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_view_page_edit_form(): void
    {
        $page = Page::create([
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'content' => 'Read our terms.',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get("/admin/pages/{$page->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('terms-of-service');
    }

    public function test_admin_can_update_existing_page(): void
    {
        $page = Page::create([
            'title' => 'Contact Us',
            'slug' => 'contact-us',
            'content' => 'Old contact text.',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/pages/{$page->id}", [
            'title' => 'Get in Touch',
            'slug' => 'contact-us',
            'content' => 'Call us anytime at 555-1234 or visit our downtown location.',
            'is_published' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Get in Touch',
            'content' => 'Call us anytime at 555-1234 or visit our downtown location.',
        ]);
    }

    public function test_admin_can_toggle_page_publication_status(): void
    {
        $page = Page::create([
            'title' => 'Draft Catering Guide',
            'slug' => 'catering-guide',
            'content' => 'Work in progress.',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/admin/pages/{$page->id}", [
            'title' => 'Draft Catering Guide',
            'slug' => 'catering-guide',
            'content' => 'Work in progress.',
            'is_published' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'is_published' => false,
        ]);
    }

    public function test_admin_can_delete_page(): void
    {
        $page = Page::create([
            'title' => 'Seasonal Specials',
            'slug' => 'seasonal-specials',
            'content' => 'Expired seasonal menu.',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/pages/{$page->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('pages', [
            'id' => $page->id,
        ]);
    }
}
