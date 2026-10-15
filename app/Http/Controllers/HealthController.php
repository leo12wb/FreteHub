<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $db    = $this->checkDatabase();
        $redis = $this->checkRedis();

        $status = ($db && $redis) ? 'ok' : 'degraded';

        return response()->json([
            'status'  => $status,
            'checks'  => [
                'database' => $db    ? 'ok' : 'error',
                'redis'    => $redis ? 'ok' : 'error',
            ],
            'version' => config('app.version', '1.0.0'),
        ], $status === 'ok' ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkRedis(): bool
    {
        try {
            Redis::ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
