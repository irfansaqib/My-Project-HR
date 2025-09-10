<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZKTecoService;

class SyncBiometricData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-biometric';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs attendance logs from the ZKTeco biometric device';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting biometric data sync...');

        // IMPORTANT: Replace with your device's actual IP address
        $deviceIp = '192.168.1.201';

        $zkService = new ZKTecoService($deviceIp);
        $result = $zkService->syncAttendanceLogs();

        if (empty($result['errors'])) {
            $this->info("Sync completed successfully. Synced {$result['synced']} log(s).");
        } else {
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }
            $this->error('Sync failed with errors.');
        }

        return Command::SUCCESS;
    }
}