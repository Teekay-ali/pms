<?php

namespace Database\Seeders;

use App\Models\DiscussionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiscussionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'General',       'color' => '#6366f1', 'description' => 'General discussions'],
            ['name' => 'Announcements', 'color' => '#f59e0b', 'description' => 'Important announcements'],
            ['name' => 'Safety',        'color' => '#ef4444', 'description' => 'Safety topics and alerts'],
            ['name' => 'Technical',     'color' => '#3b82f6', 'description' => 'Technical questions'],
            ['name' => 'HR & Admin',    'color' => '#8b5cf6', 'description' => 'HR and admin topics'],
            ['name' => 'Site Updates',  'color' => '#10b981', 'description' => 'Site progress updates'],
        ];

        foreach ($categories as $cat) {
            DiscussionCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [...$cat, 'slug' => Str::slug($cat['name'])]
            );
        }
    }
}
