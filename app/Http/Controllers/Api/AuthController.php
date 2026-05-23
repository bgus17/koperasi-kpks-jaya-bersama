<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActorAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        // Blokir admin dari login via API — admin harus menggunakan panel blade
        if ($user->hasEffectiveRole(User::ROLE_ADMIN)) {
            throw ValidationException::withMessages([
                'email' => ['Akun administrator harus login melalui panel admin.'],
            ]);
        }

        $token = $user->createToken($validated['device_name'] ?? 'api-client')->plainTextToken;

        return response()->json([
            'success' => true,
            'token_type' => 'Bearer',
            'token' => $token,
            'actor' => ActorAccessService::actorPayload($user),
            'menus' => ActorAccessService::menusForUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'actor' => ActorAccessService::actorPayload($user),
            'menus' => ActorAccessService::menusForUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil dicabut.',
        ]);
    }
}
