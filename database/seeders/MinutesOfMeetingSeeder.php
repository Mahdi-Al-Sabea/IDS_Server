<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MinutesOfMeeting;
use App\Models\Meeting;

class MinutesOfMeetingSeeder extends Seeder
{
    public function run(): void
    {
        $meetings = Meeting::all();

        if ($meetings->isEmpty()) {
            $this->command->error("You must seed meetings first!");
            return;
        }

        foreach ($meetings->take(10) as $meeting) {
            MinutesOfMeeting::create([
                'meeting_id' => $meeting->id,
                'decisions' => 'Decision summary for meeting #' . $meeting->id,
                'discussedPoints' => 'Points discussed include topic A, topic B, and topic C.',
            ]);
        }
    }
}
