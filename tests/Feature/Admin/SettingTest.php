<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Restaurant & Platform Settings');
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $settingsData = [
            'site_name' => 'Trattoria Bella',
            'site_tagline' => 'Fresh & Authentic Pasta',
            'contact_email' => 'contact@trattoriabella.com',
            'contact_phone' => '+1 (555) 999-8888',
            'contact_address' => '77 Pasta Lane, Rome',
            'opening_hours' => 'Tue-Sun: 12:00 PM - 10:00 PM',
            'currency_symbol' => '€',
            'currency_code' => 'EUR',
            'tax_rate' => '10.5',
            'delivery_fee' => '3.50',
            'minimum_order_amount' => '20.00',
            'enable_pickup' => '1',
            'enable_delivery' => '1',
            'cod_enabled' => '1',
        ];

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), $settingsData);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('Trattoria Bella', Setting::get('site_name'));
        $this->assertEquals('Trattoria Bella', Setting::get('restaurant_name'));
        $this->assertEquals('€', Setting::get('currency_symbol'));
        $this->assertEquals(10.5, Setting::get('tax_rate'));
        $this->assertEquals(3.50, Setting::get('delivery_fee'));
        $this->assertTrue(Setting::get('enable_pickup'));
    }
}
