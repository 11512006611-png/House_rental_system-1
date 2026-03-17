# Fix for 419 CSRF Token Error on Login

## Problem
When logging in as admin, you get a "419 | PAGE EXPIRED" error.

## Root Cause
The CSRF token validation is failing because:
1. Session cookie is not being properly set/transmitted
2. APP_URL mismatch with actual domain
3. Session configuration wasn't fully specified in .env

## Solutions Applied

### ✅ 1. Updated APP_URL in .env
```env
# Changed from:
APP_URL=http://localhost/HRS/public

# Changed to:
APP_URL=http://127.0.0.1:8000
```
This matches your actual access URL and fixes domain mismatch issues.

### ✅ 2. Added Session Configuration to .env
```env
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
```
This ensures sessions work properly in development environment without HTTPS.

### ✅ 3. Updated SameSite Cookie Setting
```php
// In config/session.php
'same_site' => 'none',  // Changed from 'lax'
```
This ensures session cookies are sent properly on form submissions.

### ✅ 4. Cleared All Caches
- Application cache
- Config cache  
- Route cache
- View cache
- Removed old session files

## How to Test

### Step 1: Verify Configuration
```bash
cd C:\xampp\htdocs\HRS
php artisan tinker
>>> config('app.url')
>>> config('session.driver')
>>> config('session.secure_cookie')
```

### Step 2: Clear Browser Data
1. Open browser DevTools (F12)
2. Go to Application/Storage tab
3. Delete all cookies for 127.0.0.1:8000
4. Clear localStorage and sessionStorage

### Step 3: Test Login
1. Navigate to `http://127.0.0.1:8000/login`
2. Check that a session cookie is created (DevTools → Application → Cookies)
3. Fill in login form:
   - Role: Admin
   - Email: admin@example.com
   - Password: password
4. Click "Sign In"
5. You should be redirected to dashboard WITHOUT a 419 error

## If Error Persists

### Option A: Create Test User
```bash
php artisan tinker
>>> $user = User::create([
      'name' => 'Test Admin',
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'role' => 'admin',
      'phone' => '1234567890',
      'status' => 'approved'
    ]);
>>> exit
```

Then try logging in with that user:
- Email: admin@test.com
- Password: password
- Role: Admin

### Option B: Enable CSRF Debug
Add this to the beginning of `app/Http/Controllers/AuthController.php`:
```php
public function login(Request $request)
{
    // Debug CSRF token
    \Log::info('CSRF Debug', [
        'token_from_request' => $request->input('_token'),
        'token_from_session' => $request->session()->token(),
        'session_id' => $request->session()->getId(),
        'csrf_match' => $request->input('_token') === $request->session()->token(),
    ]);
    
    // ... rest of login code
}
```

### Option C: Disable CSRF for Testing (Temporary)
Edit `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'login',  // Temporarily allow POST /login without CSRF
];
```
Then test the login. If it works, the issue is definitely CSRF-related.

### Option D: Use Alternative HTTP Method
Try submitting the login form via AJAX with proper header:
```javascript
fetch('/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        role: 'admin',
        email: 'admin@test.com',
        password: 'password'
    })
});
```

## Verification Commands

Run these in PowerShell to verify everything is set up:

```powershell
# Check .env configuration
cd C:\xampp\htdocs\HRS
Select-String "APP_URL|SESSION_" .env

# Verify middleware stack
Select-String "web.*=>" app/Http/Kernel.php

# Check CSRF middleware
Get-Content app/Http/Middleware/VerifyCsrfToken.php | Select-String "except|class"

# Test database connection
php artisan migrate:status

# Check login form
Select-String "@csrf|method.*POST" resources/views/auth/login.blade.php
```

## Files Modified

1. `.env` - APP_URL and SESSION configuration
2. `config/session.php` - SameSite cookie setting
3. `storage/framework/sessions/*` - Cleaned old session files

## Security Notes

⚠️ The `same_site = 'none'` setting requires HTTPS in production.
For production, change to:
```php
'same_site' => 'lax',
'secure' => true,
```

## References

- Laravel CSRF: https://laravel.com/docs/11.x/csrf
- Session Configuration: https://laravel.com/docs/11.x/session
- Authentication: https://laravel.com/docs/11.x/authentication
