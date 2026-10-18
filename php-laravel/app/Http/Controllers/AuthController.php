<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'E-mail ou senha inválidos.'], 401);
        }

        $expiresAt = now()->addHours(8);
        $token = $user->createToken('mycare-web', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $accessToken = $request->user()->currentAccessToken();

        if ($accessToken) {
            $accessToken->delete();
        }

        return response()->json(['message' => 'Sessão encerrada.']);
    }
}
