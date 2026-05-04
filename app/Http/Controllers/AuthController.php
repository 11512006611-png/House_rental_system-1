<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'role'     => 'required|in:admin,owner,tenant',
                'email'    => 'required|email',
                'password' => 'required',
                'intended_house_id' => 'nullable|integer|exists:houses,id',
            ]);

            $invalidCredentialsMessage = 'Wrong email or password. Please try again.';

            $intendedHouseId = $credentials['intended_house_id'] ?? null;

            $existingUser = User::where('email', $credentials['email'])->first();
            if (! $existingUser) {
                return back()->withErrors([
                    'email' => $invalidCredentialsMessage,
                ])->onlyInput('email', 'role', 'intended_house_id');
            }

            if (! Hash::check($credentials['password'], (string) $existingUser->password)) {
                return back()->withErrors([
                    'password' => $invalidCredentialsMessage,
                ])->onlyInput('email', 'role', 'intended_house_id');
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
                    ])->onlyInput('email', 'role', 'intended_house_id');
                }

                if (! $user->isApproved()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => $user->status === 'rejected'
                            ? 'Your account has been rejected. Please contact the administrator.'
                            : 'Your account is pending admin approval. You will be notified once verified.',
                    ])->onlyInput('email', 'role', 'intended_house_id');
                }

                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard')
                        ->with('success', 'Welcome to Admin Dashboard, ' . $user->name . '!');
                }

                if ($user->isTenant() && $intendedHouseId) {
                    $houseIsAvailable = House::where('id', $intendedHouseId)
                        ->where('status', 'available')
                        ->exists();

                    if ($houseIsAvailable) {
                        return redirect()->route('tenant.dashboard', ['selected_inspection_house' => $intendedHouseId])
                            ->with('success', 'Welcome back, ' . $user->name . '! Continue your inspection request below.');
                    }
                }

                return redirect()->route($user->dashboardRoute())
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            return back()->withErrors([
                'email' => $invalidCredentialsMessage,
            ])->onlyInput('email', 'role', 'intended_house_id');
        } catch (Throwable $e) {
            Log::warning('Login failed because the database is unavailable.', [
                'email' => $request->input('email'),
                'role' => $request->input('role'),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Login is temporarily unavailable because the database connection is down. Please try again once MySQL is running.',
            ])->onlyInput('email', 'role', 'intended_house_id');
        }
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
            $baseUsername = $googleUser->getName() ?: (explode('@', $email)[0] ?? 'user');

            $user = User::create([
                'name'     => $googleUser->getName() ?: 'Google User',
                'username' => $this->generateUniqueUsername($baseUsername),
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
        $normalizedEmail = strtolower(trim((string) $request->input('email')));
        $rawPhone = (string) $request->input('phone');
        $digitsOnly = preg_replace('/\D+/', '', $rawPhone ?? '') ?: '';

        // Normalize Bhutan phone numbers so duplicates are consistently detected.
        if (Str::startsWith($digitsOnly, '975') && strlen($digitsOnly) > 8) {
            $digitsOnly = substr($digitsOnly, 3);
        }

        $normalizedPhone = $digitsOnly !== '' ? $digitsOnly : null;

        $request->merge([
            'email' => $normalizedEmail,
            'phone' => $normalizedPhone,
        ]);

        $eighteenYearsAgo = now()->subYears(18)->format('Y-m-d');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')],
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => ['nullable', 'regex:/^\d{8}$/', Rule::unique('users', 'phone')],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . $eighteenYearsAgo],
            'role'     => 'required|in:owner,tenant',
            'current_address' => 'nullable|string|max:500|required_if:role,owner',
            'password' => ['required', 'confirmed', Password::min(8)],
        ],
        [
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old to register.',
            'username.unique' => 'An account with these details already exists. Please log in instead of creating a new account.',
            'email.unique' => 'An account with these details already exists. Please log in instead of creating a new account.',
            'phone.unique' => 'An account with these details already exists. Please log in instead of creating a new account.',
            'phone.regex' => 'Please enter a valid Bhutan phone number (8 digits, for example: 17123456).',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.required' => 'Please enter a password of at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match. Please re-enter both password fields.',
        ],
        [
            'name' => 'full name',
            'date_of_birth' => 'date of birth',
            'current_address' => 'current address',
        ]);

        try {
            $accountRole = in_array($validated['role'], ['owner', 'tenant'], true) ? $validated['role'] : 'tenant';
            $accountStatus = 'pending';

            User::create([
                'name'     => $validated['name'],
                'username' => $validated['username'] ?? $this->generateUniqueUsername($validated['name']),
                'email'    => $validated['email'],
                'phone'    => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
                'role'     => $accountRole,
                'current_address' => $accountRole === 'tenant'
                    ? null
                    : ($validated['current_address'] ?? null),
                'password' => Hash::make($validated['password']),
                'status'   => $accountStatus,
            ]);
        } catch (QueryException $e) {
            $message = strtolower($e->getMessage());

            if (str_contains($message, 'email')) {
                return back()->withErrors([
                    'email' => 'An account with these details already exists. Please log in instead of creating a new account.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            if (str_contains($message, 'phone')) {
                return back()->withErrors([
                    'phone' => 'An account with these details already exists. Please log in instead of creating a new account.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            if (str_contains($message, 'username')) {
                return back()->withErrors([
                    'username' => 'An account with these details already exists. Please log in instead of creating a new account.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            return back()->withErrors([
                'email' => 'An account with these details already exists. Please log in instead of creating a new account.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

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

    private function generateUniqueUsername(string $source): string
    {
        $base = preg_replace('/[^a-z0-9_]+/i', '_', strtolower(trim($source))) ?: 'user';
        $base = trim($base, '_');
        $base = $base === '' ? 'user' : $base;
        $base = substr($base, 0, 80);

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $suffix = '_' . $counter;
            $username = substr($base, 0, 80 - strlen($suffix)) . $suffix;
            $counter++;
        }

        return $username;
    }

    // Password Reset Methods
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function directResetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ], [
            'email.exists' => 'No account found with this email address.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, (string) $user->password)) {
                return back()->withErrors([
                    'password' => 'This password is already used by your account. Please create a different one.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            $passwordUsedByAnotherUser = User::query()
                ->where('id', '!=', $user->id)
                ->whereNotNull('password')
                ->get(['password'])
                ->contains(function ($otherUser) use ($request) {
                    return Hash::check($request->password, (string) $otherUser->password);
                });

            if ($passwordUsedByAnotherUser) {
                return back()->withErrors([
                    'password' => 'This password is already used by another account. Please create a different one.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->save();

            return redirect()->route('login')->with('status', 'Your password has been reset successfully. You can now log in with your new password.');
        }

        return back()->withErrors(['email' => 'Unable to reset password. Please try again.']);
    }
}
