<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attachment;
use App\Models\User;
use App\Models\MinutesOfMeeting;

class AttachmentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $minutes = MinutesOfMeeting::all();

        if ($users->isEmpty() || $minutes->isEmpty()) {
            $this->command->error("You must seed users and minutes_of_meetings first!");
            return;
        }

        $sampleFiles = [
            ['fileName' => 'example1.pdf', 'filePath' => 'storage/attachments/example1.pdf'],
            ['fileName' => 'example2.docx', 'filePath' => 'storage/attachments/example2.docx'],
            ['fileName' => 'image1.jpg', 'filePath' => 'storage/attachments/image1.jpg'],
        ];

        foreach ($sampleFiles as $file) {
            Attachment::create([
                'fileName' => $file['fileName'],
                'filePath' => $file['filePath'],
                'uploadedBy' => $users->random()->id,
                'minutes_of_meeting_id' => $minutes->random()->id,
            ]);
        }
    }
}
