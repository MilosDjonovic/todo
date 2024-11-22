<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class RegisterController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validated = $this->validateRegistration($request);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);
        
        event(new Registered($user));
        
        return $this->sendResponse([
            'token' => $user->createToken('MyApp')->plainTextToken,
            'name' => $user->name,
            'message' => 'Please check your email for verification link.'
        ], 'User registered successfully. Verification email sent.');
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken,
            'user' => $user
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        if (!$token) {
            return $this->sendError('Token not provided', [], 401);
        }

        $tokenModel = PersonalAccessToken::findToken($token);
        if (!$tokenModel) {
            return $this->sendError('Invalid token', [], 401);
        }

        $tokenModel->tokenable->tokens()->delete();
        return $this->sendResponse([], 'Successfully logged out');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::find($request->id);
        
        if (!$user || !$this->isValidVerificationHash($user, $request->hash)) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $validated['email'])->first();

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse([], 'Email already verified.');
        }

        $user->sendEmailVerificationNotification();
        return $this->sendResponse([], 'Verification link sent.');
    }

    private function validateRegistration(Request $request): array
    {
        return $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
    }

    private function isValidVerificationHash(User $user, string $hash): bool
    {
        return hash_equals($hash, sha1($user->getEmailForVerification()));
    }
}