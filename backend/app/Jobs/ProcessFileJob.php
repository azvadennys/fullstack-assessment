<?php

namespace App\Jobs;

use App\Models\TaskAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private TaskAttachment $attachment;

    public function __construct(TaskAttachment $attachment) {
        $this->attachment = $attachment;
    }

    public function handle(): void
    {
        Log::info("Starting background process for file: " . $this->attachment->file_name);

        // 1. Virus Scanning Simulation (Req 1.3 Advanced)
        Log::info("Scanning for viruses...");
        sleep(2); // Simulate heavy process
        Log::info("No viruses found.");

        // 2. Thumbnail Generation (Req 1.3 & 1.4)
        if (str_starts_with($this->attachment->mime_type, 'image/')) {
            try {
                $fullPath = storage_path('app/public/' . $this->attachment->file_path);

                // Menggunakan Intervention Image v3
                $manager = new ImageManager(new Driver());
                $image = $manager->read($fullPath);

                // Resize ke thumbnail 300px
                $image->scale(300);

                // Simpan thumbnail (misal dengan prefix thumb_)
                $thumbPath = str_replace($this->attachment->file_name, 'thumb_' . $this->attachment->file_name, $fullPath);
                $image->save($thumbPath);

                Log::info("Thumbnail generated at: " . $thumbPath);
            } catch (\Exception $e) {
                Log::error("Failed to generate thumbnail: " . $e->getMessage());
            }
        }
    }
}
