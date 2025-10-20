<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;          // ← ensure this line exists
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        // same message whether email exists or not
        return back()->with('status', __($status));
    }
}