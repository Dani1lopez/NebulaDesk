<?php

namespace NebulaDesk\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * Resend the email verification notification
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Tu email ya está verificado.',
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email de verificación enviado correctamente.',
        ], 200);
    }

    /**
     * Mark the user's email as verified
     * 
     * @param Request $request
     * @param string $id
     * @param string $hash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = \App\Models\User::findOrFail($id);

        // Verify the hash matches
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect('http://localhost:3000/dashboard?verification=failed');
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return redirect('http://localhost:3000/dashboard?verification=already-verified');
        }

        // Mark as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect('http://localhost:3000/dashboard?verification=success');
    }
}
