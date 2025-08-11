# Authentication System

PitouFW provides a comprehensive authentication system with user registration, login, session management, password reset, and account verification. This document covers all aspects of user authentication and authorization.

## Overview

The authentication system in PitouFW includes:

- **User Registration** - Account creation with email verification
- **Login/Logout** - Session-based authentication with "remember me" functionality
- **Password Management** - Secure password hashing and reset functionality
- **Session Management** - Redis-backed session storage with configurable TTL
- **Account Verification** - Email-based account activation
- **Rate Limiting** - Protection against brute force attacks
- **Authorization** - Role-based access control

### Key Components

- **UserModel** - Core authentication logic and session management
- **User Entity** - User data representation and persistence
- **Controllers** - Registration, login, logout, password reset handlers
- **Security Features** - Rate limiting, password validation, CSRF protection

## User Entity

The `User` entity represents user accounts in the system:

```php
use PitouFW\Entity\User;

class User extends Entity {
    protected string $email = '';           // User email (unique)
    protected string $passwd = '';          // Hashed password
    protected int $admin = 0;              // Admin flag (0 = user, 1 = admin)
    protected ?string $reg_timestamp = null; // Registration timestamp
    protected ?string $activated_at = null;  // Account activation timestamp
    
    public static function getTableName(): string {
        return 'user';
    }
    
    // Core methods
    public function getEmail(): string;
    public function setEmail(string $email): self;
    public function getPasswd(): string;
    public function setPasswd(string $passwd): self;
    public function isAdmin(): bool;
    public function setAdmin(int $admin): self;
    
    // Authentication methods
    public function login(int $ttl = UserModel::SESSION_CACHE_TTL_DEFAULT): void;
    public function isTrustable(): bool;
    public function startAccountValidation(): void;
}
```

## UserModel Class

The `UserModel` class provides authentication logic and session management:

### Key Constants

```php
class UserModel {
    // Session configuration
    const SESSION_COOKIE_NAME = 'PTFW_SESSID';
    const SESSION_CACHE_PREFIX = 'session_';
    const SESSION_CACHE_TTL_DEFAULT = 86400;    // 1 day
    const SESSION_CACHE_TTL_LONG = 86400 * 366; // 1 year (remember me)
    
    // Rate limiting constants
    const FORGOT_PASSWD_EMAIL_COOLDOWN_TTL = 300;     // 5 minutes
    const FORGOT_PASSWD_IP_COOLDOWN_ATTEMPTS = 5;     // Max attempts
    const FORGOT_PASSWD_IP_COOLDOWN_BAN = 600;        // 10 minute ban
    
    // Account validation
    const ACCOUNT_VALIDATION_CACHE_TTL = 86400;       // 1 day
}
```

### Core Methods

```php
class UserModel {
    // Authentication state
    public static function isLogged(): bool;
    public static function get(): ?User;
    public static function logout(): void;
    
    // Access control
    public static function rejectGuests(): void;
    public static function rejectUsers(): void;
    
    // Password management
    public static function hashPassword(string $password): string;
    public static function checkPassword(string $password, string $hash): bool;
    public static function validatePassword(string $password): bool;
    
    // Session management
    public static function generateSessionToken(): string;
    public static function hashInfo(string $info): string;
}
```

## User Registration

### Registration Controller

The registration process (`app/controllers/user/register.php`):

```php
<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Router;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

// Redirect logged-in users
UserModel::rejectUsers();

if (POST) {
    if (!empty($_POST['email']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])) {
        // Validate email format
        if (filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            // Check if email already exists
            if (!User::exists('email', $_POST['email'])) {
                // Validate password strength
                if (UserModel::validatePassword($_POST['pass1'])) {
                    // Check password confirmation
                    if ($_POST['pass1'] === $_POST['pass2']) {
                        // Create new user
                        $user = new User();
                        $user->setEmail($_POST['email'])
                             ->setPasswd(UserModel::hashPassword($_POST['pass1']));
                        $uid = $user->save();
                        $user->setId($uid);

                        // Log user in immediately
                        $user->login();
                        
                        // Start email verification if required
                        if (TRUST_NEEDED) {
                            $user->startAccountValidation();
                        }

                        Alert::success('Registration successful!');
                        Router::redirect();
                    } else {
                        Alert::error('Passwords must match');
                    }
                } else {
                    Alert::error('Password does not meet requirements');
                }
            } else {
                Alert::error('Email address already registered');
            }
        } else {
            Alert::error('Invalid email address');
        }
    } else {
        Alert::error('All fields are required');
    }
}

Data::get()->add('TITLE', 'Register');
Controller::renderView('user/register/form');
```

### Registration Form

The registration view (`app/views/user/register/form.php`):

```php
<div class="container">
    <h1><?= $TITLE ?></h1>
    
    <form method="POST" class="user-form">
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   required
                   value="<?= $_POST['email'] ?? '' ?>">
        </div>
        
        <div class="form-group">
            <label for="pass1">Password:</label>
            <input type="password" 
                   id="pass1" 
                   name="pass1" 
                   class="form-control" 
                   required
                   minlength="8">
            <small class="form-text text-muted">
                Minimum 8 characters required
            </small>
        </div>
        
        <div class="form-group">
            <label for="pass2">Confirm Password:</label>
            <input type="password" 
                   id="pass2" 
                   name="pass2" 
                   class="form-control" 
                   required
                   minlength="8">
        </div>
        
        <button type="submit" class="btn btn-primary">Create Account</button>
        
        <p class="mt-3">
            Already have an account? 
            <a href="<?= WEBROOT ?>user/login">Sign in here</a>
        </p>
    </form>
</div>
```

## User Login

### Login Controller

The login process (`app/controllers/user/login.php`):

```php
<?php

use PitouFW\Core\Alert;
use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Core\Router;
use PitouFW\Entity\User;
use PitouFW\Model\UserModel;

// Redirect logged-in users
UserModel::rejectUsers();

if (POST) {
    if (!empty($_POST['email']) && !empty($_POST['pass'])) {
        // Check if user exists
        if (User::exists('email', $_POST['email'])) {
            $user = User::readBy('email', $_POST['email']);

            // Verify password
            if (UserModel::checkPassword($_POST['pass'], $user->getPasswd())) {
                // Check if account is activated
                if ($user->isTrustable()) {
                    // Determine session length
                    $ttl = !empty($_POST['remember']) && $_POST['remember'] === '1' ?
                        UserModel::SESSION_CACHE_TTL_LONG :
                        UserModel::SESSION_CACHE_TTL_DEFAULT;
                    
                    // Log user in
                    $user->login($ttl);

                    Alert::success('Login successful!');
                    Router::redirect();
                } else {
                    Alert::error('Please activate your account first');
                }
            } else {
                Alert::error('Invalid email or password');
            }
        } else {
            Alert::error('Invalid email or password');
        }
    } else {
        Alert::error('Email and password are required');
    }
}

Data::get()->add('TITLE', 'Login');
Controller::renderView('user/login/form');
```

### Login Form

The login view (`app/views/user/login/form.php`):

```php
<div class="container">
    <h1><?= $TITLE ?></h1>
    
    <form method="POST" class="user-form">
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   required
                   value="<?= $_POST['email'] ?? '' ?>">
        </div>
        
        <div class="form-group">
            <label for="pass">Password:</label>
            <input type="password" 
                   id="pass" 
                   name="pass" 
                   class="form-control" 
                   required>
        </div>
        
        <div class="form-check">
            <input type="checkbox" 
                   id="remember" 
                   name="remember" 
                   value="1" 
                   class="form-check-input">
            <label for="remember" class="form-check-label">
                Remember me for 1 year
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary">Sign In</button>
        
        <div class="mt-3">
            <a href="<?= WEBROOT ?>user/forgot-passwd">Forgot your password?</a>
        </div>
        
        <p class="mt-3">
            Don't have an account? 
            <a href="<?= WEBROOT ?>user/register">Create one here</a>
        </p>
    </form>
</div>
```

## Session Management

### Session Storage

PitouFW uses Redis for session storage with the following benefits:

- **Performance** - Fast session retrieval
- **Scalability** - Shared sessions across multiple servers
- **Security** - Sessions stored separately from web server
- **TTL Support** - Automatic session expiration

### Session Workflow

1. **Login** - Generate unique session token
2. **Storage** - Store user ID in Redis with session token as key
3. **Cookie** - Set secure cookie with session token
4. **Validation** - Check Redis for valid session on each request
5. **Expiration** - Automatic cleanup via Redis TTL

### Session Configuration

```php
// Session cookie configuration
const SESSION_COOKIE_NAME = 'PTFW_SESSID';

// Session TTL options
const SESSION_CACHE_TTL_DEFAULT = 86400;     // 1 day
const SESSION_CACHE_TTL_LONG = 86400 * 366; // 1 year (remember me)

// Redis key prefix
const SESSION_CACHE_PREFIX = 'session_';
```

### Custom Session Methods

```php
class UserModel {
    // Check if user is logged in
    public static function isLogged(): bool {
        if (self::$user !== null) {
            return true;
        }

        if (isset($_COOKIE[self::SESSION_COOKIE_NAME])) {
            $uid = self::getCachedValue();
            return User::exists('id', $uid) && $uid !== false;
        }

        return false;
    }
    
    // Get current logged-in user
    public static function get(): ?User {
        if (self::$user === null && self::isLogged()) {
            $uid = self::getCachedValue();
            self::$user = User::findOne('id', $uid);
        }
        
        return self::$user;
    }
    
    // Logout user
    public static function logout(): void {
        if (isset($_COOKIE[self::SESSION_COOKIE_NAME])) {
            $redis = new Redis();
            $cache_key = self::SESSION_CACHE_PREFIX . $_COOKIE[self::SESSION_COOKIE_NAME];
            $redis->del($cache_key);
            
            setcookie(self::SESSION_COOKIE_NAME, '', time() - 3600, '/');
        }
        
        self::$user = null;
    }
}
```

## Password Management

### Password Hashing

PitouFW uses PHP's built-in password hashing functions:

```php
class UserModel {
    // Hash password using PHP's password_hash()
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Verify password using PHP's password_verify()
    public static function checkPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    // Validate password strength
    public static function validatePassword(string $password): bool {
        // Minimum 8 characters
        if (strlen($password) < 8) {
            return false;
        }
        
        // Add additional validation rules as needed
        // - Must contain uppercase letter
        // - Must contain lowercase letter
        // - Must contain number
        // - Must contain special character
        
        return true;
    }
}
```

### Password Reset

The password reset flow:

1. **Request Reset** - User enters email address
2. **Rate Limiting** - Check for recent reset requests
3. **Generate Token** - Create secure reset token
4. **Send Email** - Email reset link to user
5. **Validate Token** - User clicks link, token validated
6. **Reset Password** - User enters new password
7. **Update Account** - Hash and save new password

#### Password Reset Controller

```php
// app/controllers/user/forgot_passwd.php
if (POST) {
    $email = $_POST['email'] ?? '';
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (User::exists('email', $email)) {
            // Check rate limiting
            if (!UserModel::isEmailCooldownActive($email)) {
                $user = User::readBy('email', $email);
                $resetToken = UserModel::generatePasswordResetToken($user);
                
                // Send reset email
                UserModel::sendPasswordResetEmail($user, $resetToken);
                
                // Set rate limiting
                UserModel::setEmailCooldown($email);
                
                Alert::success('Password reset email sent');
            } else {
                Alert::error('Please wait before requesting another reset');
            }
        } else {
            // Don't reveal whether email exists
            Alert::success('If the email exists, a reset link has been sent');
        }
    } else {
        Alert::error('Invalid email address');
    }
}
```

## Account Verification

### Email Verification Process

For enhanced security, PitouFW supports email verification:

1. **Registration** - User creates account
2. **Generate Token** - Create verification token
3. **Send Email** - Email verification link
4. **Verify Token** - User clicks link
5. **Activate Account** - Mark account as verified

### Verification Implementation

```php
class User extends Entity {
    // Start account verification process
    public function startAccountValidation(): void {
        $token = UserModel::generateValidationToken($this);
        UserModel::sendValidationEmail($this, $token);
    }
    
    // Check if account is trustable (verified)
    public function isTrustable(): bool {
        return !empty($this->activated_at);
    }
    
    // Activate account
    public function activate(): void {
        $this->activated_at = date('Y-m-d H:i:s');
        $this->save();
    }
}
```

## Access Control

### Authentication Guards

Use authentication guards to protect routes:

```php
// Require login
UserModel::rejectGuests();

// Require logout (login/register pages)
UserModel::rejectUsers();

// Check admin access
if (!UserModel::get()->isAdmin()) {
    Controller::http403Forbidden();
}
```

### Route Protection

Protect controllers with authentication checks:

```php
<?php
// app/controllers/admin/dashboard.php

use PitouFW\Core\Controller;
use PitouFW\Model\UserModel;

// Require login
UserModel::rejectGuests();

// Require admin privileges
$user = UserModel::get();
if (!$user->isAdmin()) {
    Controller::http403Forbidden();
    return;
}

// Admin dashboard logic...
```

### Middleware Pattern

Implement middleware-style authentication:

```php
class AuthMiddleware {
    public static function requireAuth(): void {
        if (!UserModel::isLogged()) {
            Router::redirect('user/login');
        }
    }
    
    public static function requireAdmin(): void {
        self::requireAuth();
        
        if (!UserModel::get()->isAdmin()) {
            Controller::http403Forbidden();
        }
    }
    
    public static function requireGuest(): void {
        if (UserModel::isLogged()) {
            Router::redirect();
        }
    }
}
```

## Rate Limiting

### Brute Force Protection

PitouFW includes built-in rate limiting for authentication endpoints:

```php
class UserModel {
    // Password reset rate limiting
    const FORGOT_PASSWD_EMAIL_COOLDOWN_TTL = 300;     // 5 minutes per email
    const FORGOT_PASSWD_IP_COOLDOWN_ATTEMPTS = 5;     // 5 attempts per IP
    const FORGOT_PASSWD_IP_COOLDOWN_BAN = 600;        // 10 minute ban
    
    // Email resend rate limiting
    const RESEND_EMAIL_UID_COOLDOWN_TTL = 300;        // 5 minutes per user
    const RESEND_EMAIL_IP_COOLDOWN_ATTEMPTS = 5;      // 5 attempts per IP
    
    // Email update rate limiting
    const UPDATE_EMAIL_COOLDOWN_ATTEMPTS = 2;         // 2 attempts per day
    const UPDATE_EMAIL_COOLDOWN_TTL = 86400;          // 1 day
}
```

### Rate Limiting Implementation

```php
// Check if IP is rate limited for password reset
if (UserModel::isIpRateLimited($_SERVER['REMOTE_ADDR'], 'forgot_passwd')) {
    Controller::http429TooManyRequests();
    return;
}

// Check if email has cooldown
if (UserModel::isEmailCooldownActive($email)) {
    Alert::error('Please wait before requesting another reset');
    return;
}

// Process request and set cooldowns
UserModel::setIpAttempt($_SERVER['REMOTE_ADDR'], 'forgot_passwd');
UserModel::setEmailCooldown($email);
```

## Security Best Practices

### Password Security

1. **Strong Hashing** - Use `password_hash()` with `PASSWORD_DEFAULT`
2. **Password Requirements** - Enforce minimum length and complexity
3. **No Plain Text** - Never store passwords in plain text
4. **Hash Verification** - Always use `password_verify()` for checking

### Session Security

1. **Secure Cookies** - Use secure, HTTP-only cookies
2. **Session Regeneration** - Regenerate session ID after login
3. **TTL Management** - Implement appropriate session timeouts
4. **Redis Storage** - Store sessions in Redis, not filesystem

### Input Validation

1. **Email Validation** - Use `filter_var()` with `FILTER_VALIDATE_EMAIL`
2. **Password Validation** - Implement strong password requirements
3. **CSRF Protection** - Include CSRF tokens in forms
4. **Rate Limiting** - Protect against brute force attacks

### Information Disclosure

1. **Generic Error Messages** - Don't reveal whether emails exist
2. **Timing Attacks** - Use consistent response times
3. **User Enumeration** - Prevent user enumeration via registration/login

## Advanced Features

### Two-Factor Authentication

Extend the authentication system with 2FA:

```php
class User extends Entity {
    protected string $two_factor_secret = '';
    protected int $two_factor_enabled = 0;
    
    public function enableTwoFactor(): string {
        $secret = TwoFactorAuth::generateSecret();
        $this->two_factor_secret = $secret;
        $this->two_factor_enabled = 1;
        $this->save();
        
        return $secret;
    }
    
    public function verifyTwoFactor(string $code): bool {
        return TwoFactorAuth::verify($this->two_factor_secret, $code);
    }
}
```

### Remember Me Tokens

Implement secure "remember me" functionality:

```php
class User extends Entity {
    public function createRememberToken(): string {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        
        // Store hash in database
        $this->remember_token = $hash;
        $this->save();
        
        return $token;
    }
    
    public function validateRememberToken(string $token): bool {
        $hash = hash('sha256', $token);
        return hash_equals($this->remember_token, $hash);
    }
}
```

### Social Login Integration

Add OAuth integration for social login:

```php
class SocialAuth {
    public static function loginWithGoogle(string $token): ?User {
        $userInfo = GoogleAuth::validateToken($token);
        
        if ($userInfo) {
            $user = User::findOne('email', $userInfo['email']);
            
            if (!$user) {
                // Create new user from social login
                $user = new User();
                $user->setEmail($userInfo['email'])
                     ->setActivatedAt(date('Y-m-d H:i:s'))
                     ->save();
            }
            
            return $user;
        }
        
        return null;
    }
}
```

## Testing Authentication

### Manual Testing

Test the authentication system manually:

1. **Registration Flow**
   - Create account with valid email
   - Test password validation
   - Check email verification (if enabled)

2. **Login Flow**
   - Login with correct credentials
   - Test "remember me" functionality
   - Verify session persistence

3. **Password Reset**
   - Request password reset
   - Check email delivery
   - Complete reset process

4. **Rate Limiting**
   - Test multiple failed login attempts
   - Verify IP-based rate limiting
   - Test email cooldowns

### Security Testing

1. **SQL Injection** - Test with malicious input
2. **XSS Prevention** - Verify output escaping
3. **CSRF Protection** - Test form submissions without tokens
4. **Session Hijacking** - Test session security

## Troubleshooting

### Common Issues

1. **Login not working**
   - Check database connection
   - Verify password hashing
   - Check Redis connectivity

2. **Sessions not persisting**
   - Verify Redis configuration
   - Check cookie settings
   - Ensure proper domain/path settings

3. **Email verification not working**
   - Check SMTP configuration
   - Verify email template rendering
   - Check spam/junk folders

4. **Rate limiting too aggressive**
   - Adjust rate limiting constants
   - Clear Redis rate limiting keys
   - Check IP detection logic

### Debugging Tips

1. **Enable error reporting** in development
2. **Check Redis keys** for session data
3. **Log authentication events** for debugging
4. **Test with different browsers** to isolate cookie issues