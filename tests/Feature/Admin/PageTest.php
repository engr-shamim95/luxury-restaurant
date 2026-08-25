<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_pages_list(): void
    {
        $admin = User::factory()->admin()->create();
        Page::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.pages.index'));

        $response->assertStatus(200);
        $response->assertSee('CMS Pages Manager');
    }

    public function test_admin_can_create_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Catering & Events',
            'slug' => 'catering-events',
            'content' => '<p>We cater weddings and corporate gatherings.</p>',
            'meta_title' => 'Catering by Bella',
            'meta_description' => 'Custom catering packages',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', [
            'title' => 'Catering & Events',
            'slug' => 'catering-events',
            'is_published' => 1,
        ]);
    }

    public function test_admin_can_update_page(): void
    {
        $admin = User::factory()->admin()->create();
        $page = Page::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'title' => 'Updated Title',
            'slug' => $page->slug,
            'content' => '<p>Updated content here.</p>',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_page(): void
    {
        $admin = User::factory()->admin()->create();
        $page = Page::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.pages.destroy', $page));

        $response->assertRedirect(route('admin.pages.index'));
        $this->assertDatabaseMissing('pages', [
            'id' => $page->id,
        ]);
    }
}
