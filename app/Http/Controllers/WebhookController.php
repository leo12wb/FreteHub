<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WebhookRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * @OA\Post(
     *     path="/webhooks",
     *     tags={"Webhooks"},
     *     summary="Configurar webhook da loja",
     *     description="Define a URL e o secret para receber notificações de atualização de rastreamento (assinadas com HMAC-SHA256).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"webhook_url","webhook_secret"},
     *
     *             @OA\Property(property="webhook_url",    type="string", format="url",    example="https://minhaloja.com/webhooks/frete"),
     *             @OA\Property(property="webhook_secret", type="string", minLength=16,    example="meu-secret-super-seguro-32chars")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Webhook configurado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message",     type="string"),
     *             @OA\Property(property="webhook_url", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(WebhookRequest $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $store->update([
            'webhook_url' => $request->string('webhook_url')->toString(),
            'webhook_secret' => $request->string('webhook_secret')->toString(),
        ]);

        return response()->json([
            'message' => 'Webhook configurado com sucesso.',
            'webhook_url' => $store->webhook_url,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/webhooks",
     *     tags={"Webhooks"},
     *     summary="Remover webhook da loja",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Webhook removido"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        /** @var Store $store */
        $store = $request->user();

        $store->update([
            'webhook_url' => null,
            'webhook_secret' => null,
        ]);

        return response()->json(['message' => 'Webhook removido com sucesso.']);
    }
}
