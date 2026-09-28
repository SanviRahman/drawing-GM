<?php

namespace Tests\Feature\Backoffice;

use App\Models\Admin;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsMenusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run the migrate:fresh command explicitly instead of relying on
     * PendingCommand::__destruct firing when the object is discarded.
     * Both paths are semantically identical; the explicit call simply
     * avoids a quirk of the wasm PHP runtime used for local verification.
     */
    protected function migrateDatabases(): void
    {
        $this->artisan('migrate:fresh', $this->migrateFreshUsing())->run();
    }

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase migrates without seeding, so run the role and
        // permission matrix directly (the seeder needs no console output).
        app(RolePermissionSeeder::class)->run();

        $this->admin = Admin::query()->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Site Settings
    |--------------------------------------------------------------------------
    */

    public function test_settings_index_page_renders(): void
    {
        SiteSetting::create([
            'group_name' => 'branding',
            'setting_key' => 'site.name',
            'setting_value' => 'Budget Painting',
            'value_type' => 'string',
            'is_public' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Site Settings List')
            ->assertSee('site.name');
    }

    public function test_settings_index_returns_table_html_for_ajax(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/settings', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html']);
    }

    public function test_settings_can_be_stored_with_validation(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings', [
                'group_name' => 'branding',
                'setting_key' => 'site.tagline',
                'setting_value' => 'Paint it right',
                'value_type' => 'string',
                'is_public' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('site_settings', ['setting_key' => 'site.tagline']);
    }

    public function test_settings_store_rejects_duplicate_key_and_bad_json(): void
    {
        SiteSetting::create([
            'group_name' => 'branding',
            'setting_key' => 'site.name',
            'setting_value' => 'x',
            'value_type' => 'string',
            'is_public' => false,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings', [
                'group_name' => 'branding',
                'setting_key' => 'site.name',
                'value_type' => 'string',
                'is_public' => '1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('setting_key');

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings', [
                'group_name' => 'consent',
                'setting_key' => 'consent.config',
                'setting_value' => '{invalid json',
                'value_type' => 'json',
                'is_public' => '1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('setting_value');
    }

    public function test_settings_can_be_updated_and_shown(): void
    {
        $setting = SiteSetting::create([
            'group_name' => 'branding',
            'setting_key' => 'site.name',
            'setting_value' => 'Old Name',
            'value_type' => 'string',
            'is_public' => false,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->putJson("/admin/settings/{$setting->id}", [
                'group_name' => 'branding',
                'setting_key' => 'site.name',
                'setting_value' => 'New Name',
                'value_type' => 'string',
                'is_public' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('site_settings', [
            'id' => $setting->id,
            'setting_value' => 'New Name',
            'is_public' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->getJson("/admin/settings/{$setting->id}", ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html']);
    }

    public function test_settings_trash_restore_and_force_delete_flow(): void
    {
        $setting = SiteSetting::create([
            'group_name' => 'branding',
            'setting_key' => 'site.name',
            'setting_value' => 'X',
            'value_type' => 'string',
            'is_public' => false,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/admin/settings/{$setting->id}")
            ->assertOk();

        $this->assertSoftDeleted('site_settings', ['id' => $setting->id]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/settings/trash')
            ->assertOk()
            ->assertSee('site.name');

        $this->actingAs($this->admin, 'admin')
            ->postJson("/admin/settings/restore/{$setting->id}")
            ->assertOk();

        $this->assertDatabaseHas('site_settings', ['id' => $setting->id, 'deleted_at' => null]);

        // Force deletion only accepts trashed records, so trash it again first.
        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/admin/settings/{$setting->id}")
            ->assertOk();

        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/admin/settings/force-delete/{$setting->id}")
            ->assertOk();

        $this->assertDatabaseMissing('site_settings', ['id' => $setting->id]);
    }

    public function test_settings_bulk_actions(): void
    {
        $a = SiteSetting::create(['group_name' => 'g', 'setting_key' => 'k.one', 'setting_value' => '1', 'value_type' => 'string', 'is_public' => false]);
        $b = SiteSetting::create(['group_name' => 'g', 'setting_key' => 'k.two', 'setting_value' => '2', 'value_type' => 'string', 'is_public' => false]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings/multiple-action', ['action' => 'public', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('site_settings', ['id' => $a->id, 'is_public' => true]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings/multiple-action', ['action' => 'private', 'ids' => [$a->id]])
            ->assertOk();

        $this->assertDatabaseHas('site_settings', ['id' => $a->id, 'is_public' => false]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings/multiple-action', ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertSoftDeleted('site_settings', ['id' => $a->id]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings/multiple-action', ['action' => 'restore', 'ids' => [$a->id]])
            ->assertOk();

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/settings/multiple-action', ['action' => 'force_delete', 'ids' => [$b->id]])
            ->assertOk();

        $this->assertDatabaseMissing('site_settings', ['id' => $b->id]);
    }

    public function test_settings_require_authentication(): void
    {
        $this->get('/admin/settings')->assertRedirect();
    }

    /*
    |--------------------------------------------------------------------------
    | Menus
    |--------------------------------------------------------------------------
    */

    public function test_menus_index_page_renders(): void
    {
        Menu::create(['name' => 'Header Primary', 'location' => 'header-primary', 'is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/menus')
            ->assertOk()
            ->assertSee('Header Primary')
            ->assertSee('header-primary');
    }

    public function test_menus_can_be_stored_with_unique_location(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus', [
                'name' => 'Header Top',
                'location' => 'header-top',
                'is_active' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('menus', ['location' => 'header-top']);

        // duplicate location rejected
        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus', [
                'name' => 'Another',
                'location' => 'header-top',
                'is_active' => '1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('location');

        // unknown location rejected
        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus', [
                'name' => 'Bad',
                'location' => 'sidebar-magic',
                'is_active' => '1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('location');
    }

    public function test_menu_items_can_be_created_nested_and_updated(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'footer-legal', 'is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->postJson("/admin/menus/{$menu->id}/items", [
                'parent_id' => '',
                'label' => 'Privacy Policy',
                'link_type' => 'url',
                'url' => '/privacy-policy',
                'icon' => 'fas fa-lock',
                'target' => '_self',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $parent = $menu->items()->first();

        $this->actingAs($this->admin, 'admin')
            ->postJson("/admin/menus/{$menu->id}/items", [
                'parent_id' => $parent->id,
                'label' => 'Child',
                'link_type' => 'text',
                'target' => '_blank',
                'sort_order' => 2,
                'is_active' => '1',
            ])
            ->assertOk();

        $child = MenuItem::query()->where('label', 'Child')->first();

        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame('_blank', $child->target);
        $this->assertNull($child->url, 'Plain text items must not store a url.');

        // update
        $this->actingAs($this->admin, 'admin')
            ->putJson("/admin/menus/{$menu->id}/items/{$child->id}", [
                'parent_id' => '',
                'label' => 'Child Renamed',
                'link_type' => 'url',
                'url' => '/child-page',
                'target' => '_self',
                'sort_order' => 3,
                'is_active' => '0',
            ])
            ->assertOk();

        $this->assertDatabaseHas('menu_items', [
            'id' => $child->id,
            'label' => 'Child Renamed',
            'url' => '/child-page',
            'is_active' => false,
        ]);
    }

    public function test_menu_item_validation_rejects_bad_target_and_cycles(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'footer-services', 'is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->postJson("/admin/menus/{$menu->id}/items", [
                'label' => 'Bad Target',
                'link_type' => 'url',
                'url' => '/x',
                'target' => '_parent',
                'is_active' => '1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target');

        $parent = $menu->items()->create(['label' => 'P', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);
        $child = $menu->items()->create(['parent_id' => $parent->id, 'label' => 'C', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);

        // parent under its own child = cycle
        $this->actingAs($this->admin, 'admin')
            ->putJson("/admin/menus/{$menu->id}/items/{$parent->id}", [
                'parent_id' => $child->id,
                'label' => 'P',
                'link_type' => 'text',
                'target' => '_self',
                'sort_order' => 1,
                'is_active' => '1',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_menu_item_scoped_binding_rejects_foreign_menu_items(): void
    {
        $menuA = Menu::create(['name' => 'A', 'location' => 'header-primary', 'is_active' => true]);
        $menuB = Menu::create(['name' => 'B', 'location' => 'header-top', 'is_active' => true]);

        $itemB = $menuB->items()->create(['label' => 'B item', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->getJson("/admin/menus/{$menuA->id}/items/{$itemB->id}/edit", ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertNotFound();
    }

    public function test_menu_delete_restores_and_cascades_items(): void
    {
        $menu = Menu::create(['name' => 'M', 'location' => 'footer-company', 'is_active' => true]);
        $parent = $menu->items()->create(['label' => 'P', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);
        $child = $menu->items()->create(['parent_id' => $parent->id, 'label' => 'C', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);

        // delete single item cascades descendants
        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/admin/menus/{$menu->id}/items/{$parent->id}")
            ->assertOk();

        $this->assertSoftDeleted('menu_items', ['id' => $child->id]);

        // menu trash cascades items
        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/admin/menus/{$menu->id}")
            ->assertOk();

        $this->assertSoftDeleted('menus', ['id' => $menu->id]);

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/menus/trash')
            ->assertOk()
            ->assertSee('M');

        // restore brings items back
        $this->actingAs($this->admin, 'admin')
            ->postJson("/admin/menus/restore/{$menu->id}")
            ->assertOk();

        $this->assertDatabaseHas('menus', ['id' => $menu->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('menu_items', ['id' => $parent->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('menu_items', ['id' => $child->id, 'deleted_at' => null]);

        // force delete removes menu + items permanently
        $menu->delete();

        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/admin/menus/force-delete/{$menu->id}")
            ->assertOk();

        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
        $this->assertDatabaseMissing('menu_items', ['menu_id' => $menu->id]);
    }

    public function test_menus_bulk_actions(): void
    {
        $a = Menu::create(['name' => 'A', 'location' => 'header-primary', 'is_active' => true]);
        $b = Menu::create(['name' => 'B', 'location' => 'header-top', 'is_active' => true]);
        $a->items()->create(['label' => 'A1', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus/multiple-action', ['action' => 'inactive', 'ids' => [$a->id]])
            ->assertOk();

        $this->assertDatabaseHas('menus', ['id' => $a->id, 'is_active' => false]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus/multiple-action', ['action' => 'active', 'ids' => [$a->id]])
            ->assertOk();

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus/multiple-action', ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk();

        $this->assertSoftDeleted('menus', ['id' => $a->id]);
        $this->assertSoftDeleted('menu_items', ['menu_id' => $a->id]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus/multiple-action', ['action' => 'restore', 'ids' => [$a->id]])
            ->assertOk();

        $this->assertDatabaseHas('menus', ['id' => $a->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('menu_items', ['menu_id' => $a->id, 'deleted_at' => null]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/admin/menus/multiple-action', ['action' => 'force_delete', 'ids' => [$b->id]])
            ->assertOk();

        $this->assertDatabaseMissing('menus', ['id' => $b->id]);
    }

    public function test_menu_show_returns_items_tree_html(): void
    {
        $menu = Menu::create(['name' => 'Header Primary', 'location' => 'header-primary', 'is_active' => true]);
        $parent = $menu->items()->create(['label' => 'Services', 'link_type' => 'text', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);
        $menu->items()->create(['parent_id' => $parent->id, 'label' => 'HDB Painting', 'link_type' => 'url', 'url' => '/services/hdb-painting', 'target' => '_self', 'sort_order' => 1, 'is_active' => true]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson("/admin/menus/{$menu->id}", ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('HDB Painting', $response->json('html'));
        $this->assertStringContainsString('Services', $response->json('html'));
    }
}
