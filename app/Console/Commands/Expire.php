<?php

namespace App\Console\Commands;

use App\Models\Upload;
use App\Models\UploadSession;
use Illuminate\Console\Command;

class Expire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expires uploads and upload sessions that have expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Expiring uploads...');
        $expiredUploads = Upload::expired()->get();

        foreach ($expiredUploads as $upload) {
            $this->info("Deleting expired upload: {$upload->id}");
            // Delete the file from storage
            if (file_exists($upload->getUploadFilePath())) {
                unlink($upload->getUploadFilePath());
            }
            $upload->delete();
        }

        $this->info('Expiring upload sessions...');
        $expiredSessions = UploadSession::expired()->get();

        foreach ($expiredSessions as $session) {
            $this->info("Deleting expired session: {$session->id}");
            $session->delete();
        }

        $this->info('Expiration process complete.');
    }
}
