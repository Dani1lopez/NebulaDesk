<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Handle forgot password request
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Send password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always return generic message to prevent email enumeration
        return response()->json([
            'message' => 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.',
        ], 200);
    }

    /**
     * Handle password reset
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();

                // Invalidate all existing tokens for this user
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Tu contraseña ha sido restablecida correctamente. Ya puedes iniciar sesión.',
            ], 200);
        }

        return response()->json([
            'message' => 'El enlace de recuperación es inválido o ha expirado.',
            'error' => 'invalid_token'
        ], 400);
    }
}
