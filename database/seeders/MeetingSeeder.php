<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Room;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $rooms = Room::all();

        if ($users->count() < 2 || $rooms->count() < 1) {
            $this->command->error("You need at least 2 users and 1 room seeded before running MeetingSeeder.");
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            $organizer = $users->random();
            $room = $rooms->random();

            $meeting = Meeting::create([
                'room_id' => $room->id,
                'organizer_id' => $organizer->id,
                'title' => "Demo Meeting #$i",
                'description' => "This is a demo meeting description for meeting #$i.",
                'startsAt' => now()->addDays($i)->setHour(9),
                'endsAt' => now()->addDays($i)->setHour(10),
                'status' => 'booked',
            ]);

            $attendees = $users->where('id', '!=', $organizer->id)->random(min(2, $users->count() - 1))->pluck('id')->toArray();
            $meeting->attendees()->sync($attendees);

            $meeting->agendas()->createMany([
                ['description' => "Agenda 1 for meeting #$i"],
                ['description' => "Agenda 2 for meeting #$i"],
            ]);
        }
    }
}
