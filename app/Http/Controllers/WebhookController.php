<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WebhookRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function store(WebhookRequest $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $store->update([
            'webhook_url'    => $request->string('webhook_url')->toString(),
            'webhook_secret' => $request->string('webhook_secret')->toString(),
        ]);

        return response()->json([
            'message'     => 'Webhook configurado com sucesso.',
            'webhook_url' => $store->webhook_url,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $store->update([
            'webhook_url'    => null,
            'webhook_secret' => null,
        ]);

        return response()->json(['message' => 'Webhook removido com sucesso.']);
    }
}
