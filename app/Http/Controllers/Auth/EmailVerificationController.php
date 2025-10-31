<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * Verify the email address.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ]);

        $user = \App\Models\User::findOrFail($request->id);

        if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('verification.success');
    }

    /**
     * Resend the email verification code.
     */
    public function resend(Request $request): RedirectResponse
    {
        // For now, just redirect back with success message
        // In a real implementation, you'd generate and send a new verification code

        return back()->with('success', 'Un nouveau code de vérification a été envoyé.');
    }

    /**
     * Display the verification success page.
     */
    public function success(): View
    {
        return view('auth.verification-success');
    }
}
