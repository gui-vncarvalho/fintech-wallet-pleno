<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Faz o registro do usuário e cria a carteira zerada.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $user->wallet()->create(['balance' => 0]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user->load('wallet'),
            'token' => $token,
        ], 201);
    }

    /**
     * Efetua o login e cria um token de acesso.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => $user->load('wallet'),
            'token' => $token,
        ]);
    }

    /**
     * Efetua o logout e apaga o token de acesso.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
