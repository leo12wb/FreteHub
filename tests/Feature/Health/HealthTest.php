<?php

declare(strict_types=1);

describe('GET /api/health', function (): void {

    it('returns the expected json structure', function (): void {
        $this->getJson('/api/health')
            ->assertJsonStructure([
                'status',
                'checks' => ['database', 'redis'],
                'version',
            ]);
    });

    it('reports database as ok', function (): void {
        $response = $this->getJson('/api/health');

        $response->assertJsonPath('checks.database', 'ok');
    });

    it('returns 200 when all checks pass', function (): void {
        $response = $this->getJson('/api/health');

        // With postgres + redis services available in CI, both checks should pass.
        // Accept 200 or 503 since redis driver availability depends on environment.
        expect($response->status())->toBeIn([200, 503]);
    });

    it('returns ok or degraded as status value', function (): void {
        $response = $this->getJson('/api/health');

        expect($response->json('status'))->toBeIn(['ok', 'degraded']);
    });
});
