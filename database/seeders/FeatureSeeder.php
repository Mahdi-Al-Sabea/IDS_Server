<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feature;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Feature::create([
            'title' => 'Projector',
            'description' => 'High-definition projector for presentations.',
        ]);

        Feature::create([
            'title' => 'Whiteboard',
            'description' => 'Large whiteboard for brainstorming sessions.',
        ]);

        Feature::create([
            'title' => 'Video Conferencing',
            'description' => 'Equipment for remote meetings and video calls.',
        ]);

        Feature::create([
            'title' => 'Audio System',
            'description' => 'High-quality audio system for clear sound.',
        ]);
        Feature::create([
            'title' => 'Air Conditioning',
            'description' => 'Climate control for comfort during meetings.',
        ]);
    }
}
