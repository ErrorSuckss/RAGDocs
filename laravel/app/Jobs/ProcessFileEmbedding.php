<?php

namespace App\Jobs;

use App\Enums\FileStatus;
use App\Events\FileStatusUpdated;
use App\Models\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessFileEmbedding implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public File $file)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            $this->file->update(['status' => FileStatus::Processing]);

            broadcast(new FileStatusUpdated($this->file));

            $response = Http::timeout(600)
                ->attach('file', Storage::disk('public')->get($this->file->file_path), $this->file->name)
                ->post('http://127.0.0.1:8001/upload',[
                    'file_id' => $this->file->id,
                    'file_name' => $this->file->name,
                ]);

            $response->throw();

        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            $this->file->update(['status' => FileStatus::Failed]);
            broadcast(new FileStatusUpdated($this->file));
            return;
        }
    }

}
