<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Telemetry status endpoint for the Preferences frontend.
 *
 * Returns independently tracked delivery state for Langfuse and Latitude,
 * heartbeat freshness, and scheduler activity. No credentials are exposed.
 *
 * Deliberately excluded from the telemetry tracing middleware group to
 * prevent recursive observations from status polling.
 */
class TelemetryStatusController extends Controller
{
    public function __construct(
        private readonly ConfigRepository $config,
    ) {}

    public function show(): JsonResponse
    {
        $heartbeat = Cache::get('telemetry.heartbeat.last');

        $langfuseEnabled = (bool) $this->config->get('langfuse.enabled');
        $langfuseConfigured = $langfuseEnabled
            && (string) $this->config->get('langfuse.public_key') !== ''
            && (string) $this->config->get('langfuse.secret_key') !== '';

        $latitudeEnabled = (bool) $this->config->get('latitude.enabled');
        $latitudeConfigured = $latitudeEnabled
            && (string) $this->config->get('latitude.api_key') !== ''
            && (string) $this->config->get('latitude.project_slug') !== '';

        $scheduler = $this->schedulerIsActive();

        $langfuseDelivery = null;
        if ($langfuseEnabled && $langfuseConfigured && is_array($heartbeat)) {
            $langfuseDelivery = [
                'exported' => $heartbeat['exported'] ?? null,
                'error' => $heartbeat['error'] ?? null,
                'at' => $heartbeat['at'] ?? null,
            ];
        }

        $latitudeDelivery = $this->latitudeDeliveryState($latitudeEnabled, $latitudeConfigured);

        return response()->json([
            'scheduler' => $scheduler,
            'heartbeat' => $heartbeat === null ? null : [
                'at' => $heartbeat['at'] ?? null,
                'database' => $heartbeat['database'] ?? null,
                'exported' => $heartbeat['exported'] ?? null,
                'error' => $heartbeat['error'] ?? null,
            ],
            'langfuse' => [
                'enabled' => $langfuseEnabled,
                'configured' => $langfuseConfigured,
                'delivery' => $langfuseDelivery,
            ],
            'latitude' => [
                'enabled' => $latitudeEnabled,
                'configured' => $latitudeConfigured,
                'delivery' => $latitudeDelivery,
            ],
        ]);
    }

    private function schedulerIsActive(): bool
    {
        $pidFile = storage_path('scheduler.pid');

        if (! is_file($pidFile)) {
            return false;
        }

        $pid = trim((string) file_get_contents($pidFile));
        if ($pid === '' || ! ctype_digit($pid)) {
            return false;
        }

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: tasklist lists matching processes; PID appears in output if alive.
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ];
                $process = proc_open("tasklist /FI \"PID eq {$pid}\"", $descriptors, $pipes);

                if ($process === false) {
                    return false;
                }

                $output = stream_get_contents($pipes[1]);
                proc_close($process);

                return str_contains($output, $pid);
            }

            // Unix: ps -o pid= prints only the PID when the process exists.
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = proc_open("ps -p {$pid} -o pid=", $descriptors, $pipes);

            if ($process === false) {
                return false;
            }

            $output = stream_get_contents($pipes[1]);
            proc_close($process);

            return trim($output) !== '';
        } catch (\Throwable) {
            return false;
        }
    }

    private function latitudeDeliveryState(bool $enabled, bool $configured): string
    {
        if (! $enabled) {
            return 'Disabled';
        }

        if (! $configured) {
            return 'Configured, keys missing';
        }

        return 'Connected';
    }
}
