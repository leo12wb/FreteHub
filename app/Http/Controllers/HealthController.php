<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/health",
     *     tags={"Health"},
     *     summary="Verificar saúde da aplicação",
     *     description="Checa a conectividade com o banco de dados e o Redis.",
     *     @OA\Response(
     *         response=200,
     *         description="Aplicação saudável",
     *         @OA\JsonContent(
     *             @OA\Property(property="status",  type="string", example="ok"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="checks",  type="object",
     *                 @OA\Property(property="database", type="string", example="ok"),
     *                 @OA\Property(property="redis",    type="string", example="ok")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=503, description="Aplicação degradada")
     * )
     */
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
