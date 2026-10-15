<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="FreteHub API",
 *     version="1.0.0",
 *     description="API REST de cotação e gestão de fretes multi-transportadora.",
 *     @OA\Contact(name="FreteHub", email="contato@fretehub.dev")
 * )
 *
 * @OA\Server(url="/api/v1", description="Produção")
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token gerado via POST /api/v1/auth/login"
 * )
 *
 * @OA\Tag(name="Auth",      description="Autenticação de lojas")
 * @OA\Tag(name="Quotes",    description="Cotações de frete")
 * @OA\Tag(name="Shipments", description="Envios e rastreamento")
 * @OA\Tag(name="Webhooks",  description="Configuração de webhooks")
 * @OA\Tag(name="Health",    description="Saúde da aplicação")
 */
abstract class Controller
{
    //
}
