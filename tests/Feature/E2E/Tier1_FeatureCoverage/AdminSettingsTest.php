<?php

namespace Tests\Feature\E2E\Tier1_FeatureCoverage;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Manager',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        $this->regularUser = User::create([
            'name' => 'John Customer',
            'email' => 'customer@restaurant.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_view_settings_page(): void
    {
        Setting::create(['key' => 'restaurant_name', 'value' => 'Bella Napoli Ristorante', 'type' => 'string']);

        $response = $this->actingAs($this->adminUser)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('Bella Napoli Ristorante');
    }

    public function test_admin_can_update_restaurant_identity_settings(): void
    {
        Setting::create(['key' => 'restaurant_name', 'value' => 'Old Name', 'type' => 'string']);
        Setting::create(['key' => 'restaurant_tagline', 'value' => 'Old Tagline', 'type' => 'string']);

        $response = $this->actingAs($this->adminUser)->put('/admin/settings', [
            'restaurant_name' => 'Trattoria Romana',
            'restaurant_tagline' => 'Handmade Pasta & Woodfired Pizza',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'key' => 'restaurant_name',
            'value' => 'Trattoria Romana',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'restaurant_tagline',
            'value' => 'Handmade Pasta & Woodfired Pizza',
        ]);
    }

    public function test_admin_can_update_operating_hours_and_contact_info(): void
    {
        Setting::create(['key' => 'restaurant_phone', 'value' => '555-0000', 'type' => 'string']);
        Setting::create(['key' => 'restaurant_email', 'value' => 'old@restaurant.com', 'type' => 'string']);
        Setting::create(['key' => 'restaurant_address', 'value' => '100 Main St', 'type' => 'string']);
        Setting::create(['key' => 'opening_hours', 'value' => 'Mon-Fri 9-5', 'type' => 'string']);

        $payload = [
            'restaurant_phone' => '+1 (555) 789-1234',
            'restaurant_email' => 'info@trattoriaromana.com',
            'restaurant_address' => '742 Evergreen Terrace, Springfield',
            'opening_hours' => 'Mon-Sun: 11:00 AM - 10:00 PM',
        ];

        $response = $this->actingAs($this->adminUser)->put('/admin/settings', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'restaurant_phone', 'value' => '+1 (555) 789-1234']);
        $this->assertDatabaseHas('settings', ['key' => 'restaurant_email', 'value' => 'info@trattoriaromana.com']);
        $this->assertDatabaseHas('settings', ['key' => 'restaurant_address', 'value' => '742 Evergreen Terrace, Springfield']);
        $this->assertDatabaseHas('settings', ['key' => 'opening_hours', 'value' => 'Mon-Sun: 11:00 AM - 10:00 PM']);
    }

    public function test_admin_can_update_currency_and_tax_rate(): void
    {
        Setting::create(['key' => 'currency_symbol', 'value' => '$', 'type' => 'string']);
        Setting::create(['key' => 'tax_rate', 'value' => '8.25', 'type' => 'string']);
        Setting::create(['key' => 'delivery_fee', 'value' => '4.99', 'type' => 'string']);

        $response = $this->actingAs($this->adminUser)->put('/admin/settings', [
            'currency_symbol' => '€',
            'tax_rate' => '10.00',
            'delivery_fee' => '5.50',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'currency_symbol', 'value' => '€']);
        $this->assertDatabaseHas('settings', ['key' => 'tax_rate', 'value' => '10.00']);
        $this->assertDatabaseHas('settings', ['key' => 'delivery_fee', 'value' => '5.50']);
    }

    public function test_admin_settings_are_persisted_in_database(): void
    {
        $response = $this->actingAs($this->adminUser)->put('/admin/settings', [
            'hero_title' => 'Taste the Passion',
            'hero_subtitle' => 'Farm-to-table ingredients every day',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'hero_title', 'value' => 'Taste the Passion']);
        $this->assertDatabaseHas('settings', ['key' => 'hero_subtitle', 'value' => 'Farm-to-table ingredients every day']);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/settings');
        $response->assertRedirect('/');

        $responsePost = $this->actingAs($this->regularUser)->put('/admin/settings', [
            'restaurant_name' => 'Hacked Restaurant',
        ]);
        $responsePost->assertRedirect('/');
        $this->assertDatabaseMissing('settings', ['value' => 'Hacked Restaurant']);
    }
}
