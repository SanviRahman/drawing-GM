<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_configuration_tables_can_store_records(): void
    {
        $setting = SiteSetting::create([
            'group_name' => 'branding',
            'setting_key' => 'site.name',
            'setting_value' => 'Drawing GM',
            'value_type' => 'string',
            'is_public' => true,
        ]);

        $menu = Menu::create([
            'name' => 'Primary navigation',
            'location' => 'header-primary',
        ]);

        $parent = $menu->items()->create([
            'label' => 'Services',
            'link_type' => 'url',
            'url' => '/services',
        ]);

        $child = $parent->children()->create([
            'menu_id' => $menu->id,
            'label' => 'Painting',
            'link_type' => 'url',
            'url' => '/services/painting',
        ]);

        $this->assertTrue($setting->is_public);
        $this->assertTrue($menu->is_active);
        $this->assertTrue($parent->is($child->parent));
        $this->assertTrue($menu->is($child->menu));
        $this->assertCount(1, $menu->rootItems);
    }

    public function test_menu_item_can_have_a_polymorphic_linkable_model(): void
    {
        $menu = Menu::create([
            'name' => 'Footer navigation',
            'location' => 'footer-company',
        ]);

        $setting = SiteSetting::create([
            'group_name' => 'branding',
            'setting_key' => 'site.name',
            'value_type' => 'string',
        ]);

        $item = $menu->items()->create([
            'label' => 'Brand settings',
            'link_type' => 'model',
            'linkable_type' => $setting->getMorphClass(),
            'linkable_id' => $setting->getKey(),
        ]);

        $this->assertTrue($setting->is($item->linkable));
    }

    public function test_site_setting_registers_required_single_file_media_collections(): void
    {
        $collections = collect((new SiteSetting())->getRegisteredMediaCollections());

        $this->assertEqualsCanonicalizing(
            ['site_logo', 'site_favicon', 'default_hero'],
            $collections->pluck('name')->all(),
        );
        $this->assertTrue($collections->every(fn ($collection) => $collection->singleFile));
    }
}
