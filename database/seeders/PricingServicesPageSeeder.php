<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/** Optional: expose Services in Admin CMS Pages without overwriting existing records. */
class PricingServicesPageSeeder extends Seeder
{
    public function run(): void
    {
        if (! Page::query()->withTrashed()->where('slug', 'services')->exists()) {
            Page::query()->create([
                'title' => 'Our Painting & Home Services',
                'slug' => 'services',
                'excerpt' => 'Explore our currently published services and request a quotation for your project.',
                'template' => 'default',
                'status' => 'published',
                'published_at' => now(),
                'is_homepage' => false,
                'show_header' => true,
                'show_footer' => true,
            ]);
        }
    }
}
