<?php

use Illuminate\Http\Request;

class NewPasswordController extends Controller
{
    public function create(Request $request, ?string $token = null)
    {
        // token can come from the route param
        $token = $token ?? $request->route('token');

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'), // prefill if present
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}