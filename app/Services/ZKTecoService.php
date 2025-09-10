<?php

namespace App\Services;

use App\Models\RawBiometricLog;
use Rats\ZKTeco\Lib\ZKTeco;

class ZKTecoService
{
    protected $zk;

    public function __construct(string $ip, int $port = 4370)
    {
        $this->zk = new ZKTeco($ip, $port);
    }

    public function syncAttendanceLogs(): array
    {
        $logsSynced = 0;
        $errors = [];

        try {
            if ($this->zk->connect()) {
                $this->zk->disableDevice(); // To prevent new punches during sync

                $attendanceLogs = $this->zk->getAttendance();

                foreach ($attendanceLogs as $log) {
                    // The 'uid' from the machine is the employee_id in our system
                    RawBiometricLog::updateOrCreate(
                        [
                            'employee_id' => $log['id'],
                            'log_time' => $log['timestamp']
                        ],
                        [
                            'punch_type' => $log['type'], // 0=check-in, 1=check-out etc.
                            'device_id' => config('app.name') // Or a more specific device ID if you have one
                        ]
                    );
                    $logsSynced++;
                }

                // Optional: Clear logs from the device after successful sync
                // $this->zk->clearAttendance();

                $this->zk->enableDevice();
                $this->zk->disconnect();
            } else {
                $errors[] = 'Failed to connect to the biometric device.';
            }
        } catch (\Exception $e) {
            $errors[] = 'An exception occurred: ' . $e->getMessage();
            // Ensure device is re-enabled in case of error
            if ($this->zk && $this->zk->isConnected()) {
                $this->zk->enableDevice();
                $this->zk->disconnect();
            }
        }
        
        return ['synced' => $logsSynced, 'errors' => $errors];
    }
}