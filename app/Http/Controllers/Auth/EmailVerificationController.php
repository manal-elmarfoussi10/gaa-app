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
            'code' => ['required', 'string', 'size:6'],
        ]);

        // For now, accept any 6-digit code (you'll need to implement actual verification logic)
        // In a real implementation, you'd check against a stored verification code

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
