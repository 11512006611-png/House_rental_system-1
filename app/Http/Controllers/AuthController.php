<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'role'     => 'required|in:admin,owner,tenant',
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $existingUser = User::where('email', $credentials['email'])->first();
        if (! $existingUser) {
            return back()->withErrors([
                'account_not_found' => 'Account not found. You are not registered in the system. Please register to continue.',
            ])->onlyInput('email', 'role');
        }

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if ($user->role !== $credentials['role']) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'role' => 'Selected role does not match this account. Please choose the correct role.',
                ])->onlyInput('email', 'role');
            }

            if (! $user->isApproved()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => $user->status === 'rejected'
                        ? 'Your account has been rejected. Please contact the administrator.'
                        : 'Your account is pending admin approval. You will be notified once verified.',
                ])->onlyInput('email', 'role');
            }

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome to Admin Dashboard, ' . $user->name . '!');
            }

            return redirect()->route($user->dashboardRoute())
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email', 'role');
    }

    public function redirectToGoogle(Request $request)
    {
        $validated = $request->validate([
            'role' => 'nullable|in:owner,tenant',
        ]);

        $role = $validated['role'] ?? 'tenant';
        $request->session()->put('google_signup_role', $role);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again.',
            ]);
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google account email is required to continue.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $role = $request->session()->pull('google_signup_role', 'tenant');

            $user = User::create([
                'name'     => $googleUser->getName() ?: 'Google User',
                'email'    => $email,
                'phone'    => null,
                'role'     => in_array($role, ['owner', 'tenant'], true) ? $role : 'tenant',
                'password' => Hash::make(Str::random(40)),
                'status'   => 'pending',
            ]);

            return redirect()->route('login')->with('success', 'Google signup successful! Your account is pending admin approval. You can log in once approved.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        if (! $user->isApproved()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => $user->status === 'rejected'
                    ? 'Your account has been rejected. Please contact the administrator.'
                    : 'Your account is pending admin approval. You will be notified once verified.',
            ]);
        }

        return redirect()->route($user->dashboardRoute())
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:owner,tenant',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status'   => 'pending',
        ]);

        // Don't auto-login; account needs admin approval
        return redirect()->route('login')
            ->with('success', 'Registration successful! Your account is pending admin approval. You will be able to log in once verified.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
