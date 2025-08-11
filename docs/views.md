# Views & Templating

PitouFW uses a simple yet powerful templating system that combines PHP templates with a layout-based approach. This document covers view rendering, template organization, data passing, and advanced templating techniques.

## Overview

The view system in PitouFW provides:

- **PHP-based Templates** - Native PHP syntax for maximum flexibility
- **Layout System** - Master layout with content sections
- **Data Binding** - Secure data passing from controllers to views
- **Helper Functions** - Utility functions for common view tasks
- **Internationalization** - Multi-language support with translation functions
- **Security** - Automatic XSS protection and output escaping

### Key Components

- **Controller::renderView()** - Main view rendering method
- **mainView.php** - Master layout template
- **View Templates** - Individual page templates
- **Data Class** - Data passing and management
- **Utils::secure()** - XSS protection
- **Translation System** - i18n support

## View Architecture

### File Structure

Views are organized in a hierarchical structure:

```
app/views/
├── mainView.php              # Master layout
├── home/
│   └── home.php             # Homepage template
├── user/
│   ├── login/
│   │   └── form.php         # Login form
│   ├── register/
│   │   └── form.php         # Registration form
│   └── profile/
│       └── form.php         # Profile form
├── error/
│   ├── 404.php              # Not found page
│   ├── 500.php              # Server error page
│   └── 403.php              # Forbidden page
└── mail/
    ├── _top.php             # Email header
    ├── _btm.php             # Email footer
    └── en/
        ├── default.php      # Default email template
        └── passwd_reset.php # Password reset email
```

### Template Hierarchy

1. **Master Layout** (`mainView.php`) - Contains HTML structure, head, navigation
2. **Content Templates** - Individual page content
3. **Partial Templates** - Reusable components
4. **Email Templates** - Specialized email layouts

## Basic View Rendering

### Controller to View

In your controller, render a view:

```php
<?php
// app/controllers/example.php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;

// Set page data
Data::get()->add('TITLE', 'Example Page');
Data::get()->add('message', 'Hello, World!');
Data::get()->add('users', $users);

// Render view with master layout
Controller::renderView('example/example');

// Render view without layout (for AJAX/API)
Controller::renderView('example/partial', null);
```

### View Template

Create the corresponding view template:

```php
<?php
// app/views/example/example.php
?>
<div class="container">
    <h1><?= $TITLE ?></h1>
    
    <?php if (isset($message)): ?>
        <p class="alert alert-info"><?= $message ?></p>
    <?php endif; ?>
    
    <?php if (!empty($users)): ?>
        <div class="user-list">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <h3><?= $user->getEmail() ?></h3>
                    <p>Registered: <?= $user->getRegTimestamp() ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</div>
```

## Master Layout

### Main Layout Template

The master layout (`mainView.php`) provides the HTML structure:

```php
<?php
use PitouFW\Core\Alert;
use PitouFW\Core\Request;
use function PitouFW\Core\t;
?>
<!doctype html>
<html lang="<?= t()->getAppliedLang() ?>">
<head>
    <title><?= $TITLE ?? 'PitouFW - Default Title' ?></title>
    <meta name="author" content="<?= AUTHOR ?>" />
    <meta name="description" content="<?= $DESC ?? 'Default description' ?>" />
    <meta charset="utf-8" />
    
    <!-- SEO Meta Tags -->
    <?php if (Request::get()->getArg(0) === 'home'): ?>
    <link rel="canonical" href="<?= APP_URL . t()->getAppliedLang() ?>" />
    <?php endif; ?>
    
    <!-- Multilingual Support -->
    <?php foreach (ACCEPTED_LANGUAGES as $lang): ?>
        <?php if (t()->getAppliedLang() !== $lang): ?>
        <link rel="alternate" hreflang="<?= $lang ?>" href="<?= APP_URL . $lang ?>" />
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Open Graph Meta -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $TITLE ?? 'Default Title' ?>" />
    <meta property="og:description" content="<?= $DESC ?? 'Default description' ?>" />
    <meta property="og:url" content="<?= APP_URL ?>" />
    <meta property="og:image" content="<?= APP_URL ?>assets/img/banner.png" />

    <!-- Stylesheets -->
    <link type="text/css" rel="stylesheet" href="<?= CSS ?>bootstrap.min.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="<?= CSS ?>font-awesome_v5.15.1.min.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="<?= CSS ?>style.css" media="screen" />

    <!-- Viewport and Mobile -->
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0" />
    <meta name="format-detection" content="telephone=no" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= IMG ?>icon.png" />
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= WEBROOT ?>">
                <?= NAME ?>
            </a>
            
            <div class="navbar-nav ml-auto">
                <?php if (UserModel::isLogged()): ?>
                    <a class="nav-link" href="<?= WEBROOT ?>user/profile">
                        Profile
                    </a>
                    <a class="nav-link" href="<?= WEBROOT ?>user/logout">
                        Logout
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="<?= WEBROOT ?>user/login">
                        Login
                    </a>
                    <a class="nav-link" href="<?= WEBROOT ?>user/register">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container container-xl">
        <!-- Alert Messages -->
        <?= Alert::handle() ?>
        
        <!-- Page Content -->
        <?php require_once @$appView; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= NAME ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script type="text/javascript" src="<?= JS ?>jquery.min.js"></script>
    <script type="text/javascript" src="<?= JS ?>bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= JS ?>script.js"></script>
</body>
</html>
```

### Layout Variables

The layout uses these common variables:

- `$TITLE` - Page title
- `$DESC` - Meta description
- `$appView` - Path to content template
- `AUTHOR` - Site author (from config)
- `NAME` - Site name (from config)
- `APP_URL` - Base application URL

## Data Passing

### Data Class

The `Data` class manages data passed from controllers to views:

```php
use PitouFW\Core\Data;

// Add single value
Data::get()->add('title', 'Page Title');

// Add multiple values
Data::get()->add('user', $user);
Data::get()->add('posts', $posts);
Data::get()->add('config', [
    'theme' => 'dark',
    'language' => 'en'
]);

// In view, variables are automatically available:
// $title, $user, $posts, $config
```

### Data Security

Data is automatically secured against XSS attacks:

```php
// In controller
Data::get()->add('userInput', $_POST['comment']);

// In view - automatically escaped for non-API routes
<?= $userInput ?> // Safe - HTML entities encoded

// For API routes - raw data preserved
// JSON output uses raw data without escaping
```

### Variable Extraction

The framework automatically extracts data variables:

```php
// In controller
Data::get()->add('username', 'john_doe');
Data::get()->add('email', 'john@example.com');

// In view - variables available directly
<p>Welcome, <?= $username ?>!</p>
<p>Your email: <?= $email ?></p>
```

## Common View Patterns

### Form Templates

Standard form structure:

```php
<?php
// app/views/user/profile/form.php
?>
<div class="container">
    <h1><?= $TITLE ?></h1>
    
    <form method="POST" class="user-form">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control" 
                   value="<?= $user->getName() ?>"
                   required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   value="<?= $user->getEmail() ?>"
                   required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            Update Profile
        </button>
        
        <a href="<?= WEBROOT ?>user/profile" class="btn btn-secondary">
            Cancel
        </a>
    </form>
</div>
```

### List Templates

Display collections of data:

```php
<?php
// app/views/blog/list.php
?>
<div class="container">
    <h1><?= $TITLE ?></h1>
    
    <?php if (!empty($posts)): ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?= WEBROOT ?>blog/post/<?= $post->getId() ?>">
                                    <?= $post->getTitle() ?>
                                </a>
                            </h5>
                            <p class="card-text">
                                <?= substr($post->getContent(), 0, 150) ?>...
                            </p>
                            <small class="text-muted">
                                By <?= $post->getAuthor()->getName() ?> 
                                on <?= date('M j, Y', strtotime($post->getCreatedAt())) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            No posts found.
        </div>
    <?php endif; ?>
</div>
```

### Detail Templates

Show individual records:

```php
<?php
// app/views/blog/detail.php
?>
<div class="container">
    <article>
        <header>
            <h1><?= $post->getTitle() ?></h1>
            <div class="meta">
                <span class="author">By <?= $post->getAuthor()->getName() ?></span>
                <span class="date"><?= date('F j, Y', strtotime($post->getCreatedAt())) ?></span>
            </div>
        </header>
        
        <div class="content">
            <?= nl2br($post->getContent()) ?>
        </div>
        
        <footer class="mt-4">
            <a href="<?= WEBROOT ?>blog" class="btn btn-secondary">
                Back to Blog
            </a>
            
            <?php if (UserModel::isLogged() && UserModel::get()->getId() === $post->getAuthorId()): ?>
                <a href="<?= WEBROOT ?>blog/edit/<?= $post->getId() ?>" class="btn btn-primary">
                    Edit Post
                </a>
            <?php endif; ?>
        </footer>
    </article>
</div>
```

## Partial Templates

### Including Partials

Break templates into reusable components:

```php
<?php
// app/views/partials/user_card.php
?>
<div class="user-card">
    <div class="avatar">
        <img src="<?= $user->getAvatar() ?: IMG . 'default-avatar.png' ?>" 
             alt="<?= $user->getName() ?>">
    </div>
    <div class="info">
        <h4><?= $user->getName() ?></h4>
        <p><?= $user->getEmail() ?></p>
        <small>Member since <?= date('Y', strtotime($user->getRegTimestamp())) ?></small>
    </div>
</div>
```

```php
<?php
// app/views/users/list.php
?>
<div class="users-grid">
    <?php foreach ($users as $user): ?>
        <?php 
        // Make user available to partial
        $user = $user;
        include VIEWS . 'partials/user_card.php'; 
        ?>
    <?php endforeach; ?>
</div>
```

### Partial Helper Function

Create a helper for including partials:

```php
// Add to a helper file
function render_partial(string $partial, array $data = []): void {
    extract($data);
    include VIEWS . 'partials/' . $partial . '.php';
}

// Usage in templates
<?php foreach ($users as $user): ?>
    <?php render_partial('user_card', ['user' => $user]); ?>
<?php endforeach; ?>
```

## Alert System

### Flash Messages

PitouFW includes a built-in alert system:

```php
use PitouFW\Core\Alert;

// In controller
Alert::success('User created successfully!');
Alert::error('Invalid email address');
Alert::warning('Please verify your email');
Alert::info('Welcome to PitouFW!');

// In layout (mainView.php)
<?= Alert::handle() ?>
```

### Alert Implementation

The alert system automatically renders Bootstrap-style alerts:

```php
// Example output
<div class="alert alert-success alert-dismissible fade show" role="alert">
    User created successfully!
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
```

## Internationalization

### Translation Functions

PitouFW supports multi-language templates:

```php
use function PitouFW\Core\t;

// In templates
<h1><?= t()->get('page.title') ?></h1>
<p><?= t()->get('welcome.message', ['name' => $user->getName()]) ?></p>

// Language constants (if using constant-based i18n)
<h1><?= L::page_title ?></h1>
<p><?= L::welcome_message ?></p>
```

### Language Detection

The framework automatically detects and applies language:

```php
// Current language
$currentLang = t()->getAppliedLang();

// Alternate language links
<?php foreach (ACCEPTED_LANGUAGES as $lang): ?>
    <?php if (t()->getAppliedLang() !== $lang): ?>
        <link rel="alternate" hreflang="<?= $lang ?>" 
              href="<?= APP_URL . $lang ?>" />
    <?php endif; ?>
<?php endforeach; ?>
```

## Helper Functions

### URL Helpers

Common URL generation functions:

```php
// Base URL helper
function webroot(string $path = ''): string {
    return WEBROOT . $path;
}

// Usage in templates
<a href="<?= webroot('user/profile') ?>">Profile</a>
<img src="<?= webroot('assets/img/logo.png') ?>" alt="Logo">
```

### Asset Helpers

Generate asset URLs:

```php
// Asset constants from config
<?= CSS ?>bootstrap.min.css     // /assets/css/bootstrap.min.css
<?= JS ?>script.js              // /assets/js/script.js
<?= IMG ?>logo.png              // /assets/img/logo.png
<?= FONTS ?>roboto.woff2        // /assets/fonts/roboto.woff2
```

### Date/Time Helpers

Format dates and times:

```php
// Create helper functions
function format_date(string $date, string $format = 'M j, Y'): string {
    return date($format, strtotime($date));
}

function time_ago(string $date): string {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

// Usage
<time><?= format_date($post->getCreatedAt()) ?></time>
<span class="ago"><?= time_ago($post->getCreatedAt()) ?></span>
```

## API Views

### JSON Responses

For API endpoints, render JSON without layout:

```php
// app/controllers/api/users.php
use PitouFW\Core\Controller;
use PitouFW\Core\Data;

// Add data
Data::get()->add('status', 'success');
Data::get()->add('users', $users);

// Render JSON (no layout)
Controller::renderView('json/json', null);
```

### JSON Template

Create a JSON template:

```php
<?php
// app/views/json/json.php
header('Content-Type: application/json');

// Get all data
$data = Data::get()->getData();

// Output JSON
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

### API Error Responses

Handle API errors:

```php
// In controller
if ($error) {
    Data::get()->add('status', 'error');
    Data::get()->add('message', 'Invalid request');
    Data::get()->add('code', 400);
    Controller::renderView('json/error', null);
    return;
}
```

```php
<?php
// app/views/json/error.php
header('Content-Type: application/json');
http_response_code($code ?? 500);

echo json_encode([
    'status' => $status ?? 'error',
    'message' => $message ?? 'Unknown error',
    'code' => $code ?? 500
], JSON_PRETTY_PRINT);
```

## Email Templates

### Email Layout Structure

Email templates have their own structure:

```
app/views/mail/
├── _top.php                 # Email header
├── _btm.php                 # Email footer
└── en/                      # Language-specific templates
    ├── default.php          # Default email template
    ├── passwd_reset.php     # Password reset email
    └── newmail.php          # New email notification
```

### Email Template Example

```php
<?php
// app/views/mail/en/welcome.php
include VIEWS . 'mail/_top.php';
?>

<h1 style="color: #333;">Welcome to <?= NAME ?>!</h1>

<p>Hello <?= $user->getName() ?>,</p>

<p>Thank you for registering with <?= NAME ?>. Your account has been created successfully.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= $verificationUrl ?>" 
       style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
        Verify Your Email
    </a>
</div>

<p>If you didn't create this account, please ignore this email.</p>

<p>Best regards,<br>The <?= NAME ?> Team</p>

<?php
include VIEWS . 'mail/_btm.php';
?>
```

### Email Header/Footer

```php
<?php
// app/views/mail/_top.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $subject ?? 'Email from ' . NAME ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { border-bottom: 2px solid #007bff; margin-bottom: 20px; }
        .footer { border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><?= NAME ?></h2>
        </div>
```

```php
<?php
// app/views/mail/_btm.php
?>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= NAME ?>. All rights reserved.</p>
            <p><?= APP_URL ?></p>
        </div>
    </div>
</body>
</html>
```

## Advanced Techniques

### Template Inheritance

Implement template inheritance with sections:

```php
<?php
// Base template: app/views/layouts/base.php
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->section('title') ?> - <?= NAME ?></title>
    <?= $this->section('head') ?>
</head>
<body>
    <nav><?= $this->section('navigation') ?></nav>
    <main><?= $this->section('content') ?></main>
    <footer><?= $this->section('footer') ?></footer>
    <?= $this->section('scripts') ?>
</body>
</html>
```

### View Composers

Share data across multiple views:

```php
class ViewComposer {
    public static function compose(array &$data): void {
        // Add global navigation data
        $data['navigation'] = [
            'home' => 'Home',
            'about' => 'About',
            'contact' => 'Contact'
        ];
        
        // Add user data if logged in
        if (UserModel::isLogged()) {
            $data['currentUser'] = UserModel::get();
        }
        
        // Add site-wide settings
        $data['settings'] = ConfigModel::getSettings();
    }
}

// In controller rendering
$data = Data::get()->getData();
ViewComposer::compose($data);
```

### Caching Views

Implement view caching for better performance:

```php
class ViewCache {
    public static function get(string $key): ?string {
        $cacheFile = CACHE . 'views/' . md5($key) . '.html';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            return file_get_contents($cacheFile);
        }
        
        return null;
    }
    
    public static function put(string $key, string $content): void {
        $cacheFile = CACHE . 'views/' . md5($key) . '.html';
        $cacheDir = dirname($cacheFile);
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        file_put_contents($cacheFile, $content);
    }
}
```

## Security Considerations

### XSS Prevention

Always escape output:

```php
// Automatic escaping (non-API routes)
<?= $userInput ?>

// Manual escaping when needed
<?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?>

// Raw output (be careful!)
<?php echo $trustedHtml; ?>
```

### CSRF Protection

Include CSRF tokens in forms:

```php
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
    <!-- form fields -->
</form>
```

### Input Validation

Validate data in templates:

```php
<?php if (isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL)): ?>
    <p>Email: <?= $email ?></p>
<?php endif; ?>
```

## Best Practices

### Template Organization

1. **Logical Structure** - Organize templates by feature/controller
2. **Reusable Partials** - Extract common components
3. **Consistent Naming** - Use clear, descriptive filenames
4. **Separation of Concerns** - Keep logic in controllers

### Performance

1. **Minimize PHP in Views** - Keep templates simple
2. **Cache Static Content** - Use view caching for heavy pages
3. **Optimize Assets** - Minimize CSS/JS files
4. **Lazy Loading** - Load data only when needed

### Maintainability

1. **Document Complex Logic** - Add comments for complex view logic
2. **Use Helpers** - Create helper functions for common tasks
3. **Consistent Formatting** - Follow consistent indentation and style
4. **Version Control** - Track template changes with meaningful commits

### Security

1. **Always Escape Output** - Never trust user input
2. **Validate Data** - Check data types and formats
3. **Use HTTPS** - Protect sensitive forms with HTTPS
4. **CSRF Tokens** - Include tokens in all forms