<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $room = Room::create([
            'roomname' => 'Conference Room A',
            'capacity' => 20,
            'floor' => 11,
        ]);

        $room->features()->attach([1, 2]);

        $room = Room::create([
            'roomname' => 'Meeting Room B',
            'capacity' => 10,
            'floor' => -1,
        ]);

        $room->features()->attach([2, 3]);
        
    }
}
