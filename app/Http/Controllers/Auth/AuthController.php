<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     summary="Registrar nova loja",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *
     *             @OA\Property(property="name",                  type="string",  example="Minha Loja"),
     *             @OA\Property(property="email",                 type="string",  format="email", example="loja@exemplo.com"),
     *             @OA\Property(property="password",              type="string",  example="senha123"),
     *             @OA\Property(property="password_confirmation", type="string",  example="senha123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Loja criada com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="store", type="object",
     *                 @OA\Property(property="id",    type="integer"),
     *                 @OA\Property(property="name",  type="string"),
     *                 @OA\Property(property="email", type="string")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $store = Store::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $token = $store->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'email' => $store->email,
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Login da loja",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email",    type="string", format="email", example="loja@exemplo.com"),
     *             @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token gerado",
     *
     *         @OA\JsonContent(@OA\Property(property="token", type="string"))
     *     ),
     *
     *     @OA\Response(response=422, description="Credenciais inválidas")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        /** @var Store $store */
        $store = Auth::user();
        $token = $store->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Auth"},
     *     summary="Revogar token atual",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Logout realizado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
