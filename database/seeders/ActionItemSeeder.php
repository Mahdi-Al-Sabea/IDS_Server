<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActionItem;
use App\Models\User;
use App\Models\MinutesOfMeeting;

class ActionItemSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $mom = MinutesOfMeeting::all();

        if ($users->isEmpty() || $mom->isEmpty()) {
            $this->command->error("You need to seed users and minutes_of_meetings first.");
            return;
        }

        // Create 5 action items as examples
        for ($i = 1; $i <= 5; $i++) {
            ActionItem::create([
                'description' => "Action item description #$i",
                'status' => $i % 2 == 0 ? 'Completed' : 'Pending',
                'dueDate' => now()->addDays($i),
                'assignedTo' => $users->random()->id,
                'minutes_of_meeting_id' => $mom->random()->id,
            ]);
        }
    }
}
