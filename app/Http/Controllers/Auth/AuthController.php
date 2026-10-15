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
    public function register(RegisterRequest $request): JsonResponse
    {
        $store = Store::create([
            'name'     => $request->string('name')->toString(),
            'email'    => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $token = $store->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'store' => [
                'id'    => $store->id,
                'name'  => $store->name,
                'email' => $store->email,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
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

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
