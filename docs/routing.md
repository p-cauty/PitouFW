# Routing System

PitouFW uses a simple, file-based routing system that maps URLs to controller files. This document explains how routing works, how to define routes, and advanced routing techniques.

## Overview

The routing system in PitouFW is designed to be simple and predictable. Routes are defined in a single configuration file (`routes.php`) and map directly to controller files in the `app/controllers/` directory.

### Key Features

- **File-based routing** - No complex route definitions
- **Nested route support** - Organize related routes hierarchically
- **Automatic controller resolution** - URLs map directly to controller files
- **RESTful patterns** - Support for standard REST conventions
- **Error handling** - Automatic 404 responses for undefined routes

## Basic Routing

### Route Configuration

Routes are defined in the `routes.php` file as a nested array:

```php
<?php
const ROUTES = [
    'home' => 'home',                    // /home → app/controllers/home.php
    'about' => 'about',                  // /about → app/controllers/about.php
    'contact' => 'contact',              // /contact → app/controllers/contact.php
];
```

### Default Route

The default route (when no path is specified) is automatically mapped to 'home':

- `/` → `/home` → `app/controllers/home.php`
- `http://localhost:8080/` → `app/controllers/home.php`

### Route-to-File Mapping

Each route maps to a corresponding PHP file in the controllers directory:

```
Route: 'products'
URL: /products
File: app/controllers/products.php
```

## Nested Routes

For complex applications, you can organize routes hierarchically:

```php
const ROUTES = [
    'home' => 'home',
    'user' => [
        'register' => 'register',        // /user/register → app/controllers/user/register.php
        'login' => 'login',              // /user/login → app/controllers/user/login.php
        'logout' => 'logout',            // /user/logout → app/controllers/user/logout.php
        'profile' => 'profile',          // /user/profile → app/controllers/user/profile.php
        'forgot-passwd' => 'forgot_passwd', // /user/forgot-passwd → app/controllers/user/forgot_passwd.php
    ],
    'admin' => [
        'dashboard' => 'dashboard',      // /admin/dashboard → app/controllers/admin/dashboard.php
        'users' => [
            'list' => 'list',            // /admin/users/list → app/controllers/admin/users/list.php
            'edit' => 'edit',            // /admin/users/edit → app/controllers/admin/users/edit.php
        ]
    ]
];
```

### Directory Structure

Nested routes require corresponding directory structure:

```
app/controllers/
├── home.php
├── user/
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   └── profile.php
└── admin/
    ├── dashboard.php
    └── users/
        ├── list.php
        └── edit.php
```

## API Routes

API routes follow the same pattern but are typically grouped under an 'api' prefix:

```php
const ROUTES = [
    // ... other routes
    'api' => [
        'version' => 'version',          // /api/version → app/controllers/api/version.php
        'users' => 'users',              // /api/users → app/controllers/api/users.php
        'mailer' => 'mailer',            // /api/mailer → app/controllers/api/mailer.php
    ]
];
```

### API Controller Example

API controllers typically return JSON responses:

```php
<?php
// app/controllers/api/users.php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;
use PitouFW\Entity\User;

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get user list
        $users = User::findAll();
        Data::get()->add('users', $users);
        break;
        
    case 'POST':
        // Create new user
        $user = new User();
        $user->setEmail($_POST['email'])
             ->setName($_POST['name'])
             ->save();
        Data::get()->add('user', $user);
        break;
        
    case 'PUT':
        // Update user
        // Handle PUT data...
        break;
        
    case 'DELETE':
        // Delete user
        // Handle DELETE...
        break;
}

// Render JSON response (no layout)
Controller::renderView('json/json', null);
```

## Router Class

The `Router` class (`core/Router.php`) handles the URL parsing and controller resolution.

### Key Methods

```php
class Router {
    public static function get(): Router
    // Get router instance (singleton)
    
    public function getPathToRequire(): string
    // Get the file path for the current route
    
    public static function redirect(string $path): void
    // Redirect to another route
}
```

### How Routing Works

1. **URL Parsing**: The router extracts path segments from the URL
2. **Route Matching**: Compares path segments against the ROUTES array
3. **Controller Resolution**: Determines the controller file to execute
4. **File Inclusion**: Includes and executes the controller file

### Route Resolution Algorithm

```php
// Example: URL /user/profile
// 1. Parse URL segments: ['user', 'profile']
// 2. Look up in ROUTES array:
//    ROUTES['user'] = [...] (array, continue)
//    ROUTES['user']['profile'] = 'profile' (string, found!)
// 3. Build path: 'user/' + 'profile' = 'user/profile'
// 4. Include: app/controllers/user/profile.php
```

## Request Handling

### Request Class

The `Request` class provides access to URL arguments and parameters:

```php
use PitouFW\Core\Request;

// Get URL argument by position
$arg0 = Request::get()->getArg(0); // First URL segment
$arg1 = Request::get()->getArg(1); // Second URL segment

// Example: /user/profile/edit
// getArg(0) = 'user'
// getArg(1) = 'profile'
// getArg(2) = 'edit'
```

### HTTP Methods

Detect the request method in your controller:

```php
// Check if POST request
if (POST) {
    // Handle form submission
}

// Or check specific methods
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Handle PUT request
}
```

### POST Data

Access form data in controllers:

```php
// Regular form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

// JSON API data (automatically parsed)
// For API routes, JSON input is automatically parsed into $_POST
```

## Advanced Routing Patterns

### RESTful Routes

While PitouFW doesn't enforce REST conventions, you can implement RESTful patterns:

```php
const ROUTES = [
    'api' => [
        'users' => 'users',              // Handle all HTTP methods in one controller
        'posts' => 'posts',
    ]
];
```

```php
// app/controllers/api/users.php
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($id = Request::get()->getArg(2)) {
            // GET /api/users/123 - Show specific user
            $user = User::findOne('id', $id);
        } else {
            // GET /api/users - List all users
            $users = User::findAll();
        }
        break;
        
    case 'POST':
        // POST /api/users - Create user
        break;
        
    case 'PUT':
        // PUT /api/users/123 - Update user
        break;
        
    case 'DELETE':
        // DELETE /api/users/123 - Delete user
        break;
}
```

### Dynamic Routes

Handle dynamic URL segments in your controllers:

```php
// URL: /user/profile/123
// Route: user/profile maps to app/controllers/user/profile.php

// In controller:
$userId = Request::get()->getArg(2); // Gets '123'
$user = User::findOne('id', $userId);
```

### Query Parameters

Access query parameters normally with PHP:

```php
// URL: /search?query=php&category=framework
$query = $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
```

## URL Generation

### Redirect Helper

Use the Router to redirect between routes:

```php
use PitouFW\Core\Router;

// Redirect to another route
Router::redirect('user/login');

// With query parameters
Router::redirect('search?query=' . urlencode($searchTerm));
```

### URL Building

Build URLs manually using the WEBROOT constant:

```php
// In views
$loginUrl = WEBROOT . 'user/login';
$profileUrl = WEBROOT . 'user/profile';

// In HTML
<a href="<?= WEBROOT ?>user/profile">My Profile</a>
```

## Error Handling

### 404 Not Found

When a route doesn't match, the router automatically calls:

```php
Controller::http404NotFound();
```

This renders the `app/views/error/404.php` template or returns a JSON error for API routes.

### Custom Error Pages

Create custom error pages in `app/views/error/`:

- `404.php` - Not Found
- `403.php` - Forbidden  
- `500.php` - Internal Server Error
- `401.php` - Unauthorized
- `429.php` - Too Many Requests

## Route Security

### Authentication Checks

Implement authentication in controllers:

```php
use PitouFW\Model\UserModel;

// Check if user is logged in
if (!UserModel::isLogged()) {
    Controller::http401Unauthorized();
    return;
}

// Check specific permissions
$user = UserModel::get();
if (!$user->hasPermission('admin')) {
    Controller::http403Forbidden();
    return;
}
```

### CSRF Protection

For forms, implement CSRF protection:

```php
// In controller (POST handler)
if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    Controller::http403Forbidden();
    return;
}

// In view
<input type="hidden" name="csrf_token" value="<?= Session::getCsrfToken() ?>">
```

## Performance Considerations

### Route Caching

Routes are defined as constants and loaded once per request. For high-traffic applications, consider:

- Opcode caching (OPcache)
- Route optimization for deeply nested structures
- Minimal route definitions

### Controller Loading

Controllers are only included when needed, providing efficient memory usage.

## Best Practices

### Route Organization

1. **Group related routes** - Use nested arrays for logical grouping
2. **Keep routes flat when possible** - Avoid deep nesting unless necessary
3. **Use descriptive names** - Route names should be clear and meaningful
4. **Follow conventions** - Use consistent naming patterns

### Controller Design

1. **One responsibility per controller** - Each controller should handle one specific feature
2. **Keep controllers thin** - Move business logic to models
3. **Handle HTTP methods appropriately** - Use proper REST patterns for APIs
4. **Validate input** - Always validate and sanitize input data

### URL Design

1. **Use SEO-friendly URLs** - Clear, descriptive paths
2. **Avoid deep nesting** - Keep URLs reasonably short
3. **Use hyphens for multi-word segments** - `/forgot-passwd` not `/forgotpasswd`
4. **Be consistent** - Follow the same patterns throughout your application

### Security

1. **Validate all routes** - Ensure only intended routes are accessible
2. **Implement proper authentication** - Check permissions in controllers
3. **Use HTTPS in production** - Protect sensitive routes
4. **Log route access** - Monitor for suspicious activity

## Examples

### Basic Blog Routes

```php
const ROUTES = [
    'home' => 'home',
    'blog' => [
        'post' => 'post',                // /blog/post (with ID in URL segment)
        'category' => 'category',        // /blog/category
        'archive' => 'archive',          // /blog/archive
    ],
    'admin' => [
        'posts' => [
            'list' => 'list',            // /admin/posts/list
            'create' => 'create',        // /admin/posts/create
            'edit' => 'edit',            // /admin/posts/edit
        ]
    ]
];
```

### E-commerce Routes

```php
const ROUTES = [
    'home' => 'home',
    'products' => [
        'list' => 'list',               // /products/list
        'detail' => 'detail',           // /products/detail
        'search' => 'search',           // /products/search
    ],
    'cart' => [
        'view' => 'view',               // /cart/view
        'add' => 'add',                 // /cart/add
        'remove' => 'remove',           // /cart/remove
        'checkout' => 'checkout',       // /cart/checkout
    ],
    'api' => [
        'products' => 'products',       // /api/products
        'cart' => 'cart',              // /api/cart
    ]
];
```

## Troubleshooting

### Common Issues

1. **404 errors for valid routes**
   - Check route definition in `routes.php`
   - Verify controller file exists in correct location
   - Ensure file permissions allow reading

2. **Routes not working after changes**
   - Routes are cached as constants
   - Restart web server or clear opcode cache

3. **Nested routes not resolving**
   - Check directory structure matches route nesting
   - Verify all intermediate directories exist

4. **API routes returning HTML instead of JSON**
   - Ensure controller calls `renderView('json/json', null)`
   - Check that view template outputs proper JSON

### Debugging Routes

Add debugging to see route resolution:

```php
// In a controller, add:
var_dump(Request::get()->getArg(0));
var_dump(Request::get()->getArg(1));
var_dump($_SERVER['REQUEST_URI']);
```