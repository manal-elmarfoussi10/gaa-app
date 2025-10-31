<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'company_name' => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'siret' => ['required', 'string', 'max:20'],
            'tva' => ['required', 'string', 'max:20'],
            'garage_type' => ['nullable', 'in:fixe,mobile,both'],
            'known_by' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ]);

        // Create company first
        $company = Company::create([
            'name' => $request->company_name,
            'commercial_name' => $request->commercial_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'siret' => $request->siret,
            'tva' => $request->tva,
            'garage_type' => $request->garage_type,
            'known_by' => $request->known_by,
        ]);

        // Create user (inactive by default, needs approval)
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $company->id,
            'role' => User::ROLE_ADMIN, // Admin role for new registrations
            'is_active' => false, // Inactive until approved by superadmin
        ]);

        event(new Registered($user));

        // Don't log in the user automatically
        // Auth::login($user);

        // Send verification email
        $user->notify(new \App\Notifications\EmailVerificationNotification(
            url: route('verification.verify', [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ])
        ));

        // Redirect to verification page
        return redirect()->route('verification.notice')->with([
            'email' => $request->email,
            'success' => 'Un email de vérification a été envoyé à votre adresse email.'
        ]);
    }
}
