# PitouFW Core Classes API Reference

This document provides comprehensive API documentation for all core classes in the PitouFW framework. Each class is documented with its purpose, methods, properties, and usage examples.

## Overview

PitouFW core classes are located in the `core/` directory and provide the fundamental functionality of the framework. All core classes use the `PitouFW\Core` namespace.

### Core Classes Index

- [Router](#router) - URL routing and controller resolution
- [Controller](#controller) - Base controller functionality
- [Entity](#entity) - Database ORM base class
- [DB](#db) - Database connection management
- [Request](#request) - HTTP request handling
- [Data](#data) - Data passing and management
- [Session](#session) - Session management
- [Alert](#alert) - Flash message system
- [Mailer](#mailer) - Email functionality
- [Utils](#utils) - Utility functions
- [Translator](#translator) - Internationalization
- [Redis](#redis) - Cache management
- [Logger](#logger) - Logging system
- [Crypt](#crypt) - Encryption utilities

---

## Router

**Class:** `PitouFW\Core\Router`  
**File:** `core/Router.php`  
**Purpose:** Handles URL routing and controller resolution based on route configuration.

### Properties

```php
private static ?Router $instance = null;
public static array $controllers = ROUTES;
private string $controller;
```

### Methods

#### `public static function get(): Router`

Returns the singleton instance of the Router.

```php
$router = Router::get();
```

#### `public function getPathToRequire(): string`

Returns the file path to the controller that should handle the current request.

```php
$controllerPath = Router::get()->getPathToRequire();
// Returns: "app/controllers/user/profile.php"
```

#### `public static function redirect(string $path = ''): void`

Redirects to the specified path.

```php
// Redirect to home
Router::redirect();

// Redirect to specific path
Router::redirect('user/login');

// Redirect with query parameters
Router::redirect('search?q=php');
```

#### `private function getControllerName(int $depth, string $path, ?array $sub_controllers): string`

Internal method that recursively resolves the controller name from URL segments.

### Usage Examples

```php
// Basic routing usage
$router = Router::get();
$controllerFile = $router->getPathToRequire();

// Redirect examples
Router::redirect('user/profile');
Router::redirect('api/users');
```

---

## Controller

**Class:** `PitouFW\Core\Controller`  
**File:** `core/Controller.php`  
**Purpose:** Base controller class providing view rendering and HTTP response methods.

### Methods

#### `public static function renderView(string $path, ?string $layout = 'mainView.php'): void`

Renders a view template with optional layout.

**Parameters:**
- `$path` - Path to view template (relative to `app/views/`)
- `$layout` - Layout file to use (null for no layout)

```php
// Render with default layout
Controller::renderView('user/profile');

// Render without layout (API responses)
Controller::renderView('json/response', null);

// Render with custom layout
Controller::renderView('admin/dashboard', 'adminLayout.php');
```

#### `public static function renderApiError(string $message): void`

Renders an API error response with JSON format.

```php
Controller::renderApiError('Invalid request data');
// Outputs: {"status": "error", "message": "Invalid request data"}
```

#### HTTP Status Methods

The Controller class provides magic methods for HTTP status responses:

```php
Controller::http404NotFound();        // 404 Not Found
Controller::http401Unauthorized();    // 401 Unauthorized
Controller::http403Forbidden();       // 403 Forbidden
Controller::http500InternalServerError(); // 500 Internal Server Error
Controller::http429TooManyRequests(); // 429 Too Many Requests
```

### Usage Examples

```php
// In a controller file
use PitouFW\Core\Controller;
use PitouFW\Core\Data;

// Set page data
Data::get()->add('TITLE', 'User Profile');
Data::get()->add('user', $user);

// Render view
Controller::renderView('user/profile');

// Handle errors
if (!$user) {
    Controller::http404NotFound();
    return;
}

// API response
if (Request::get()->getArg(0) === 'api') {
    Controller::renderView('json/user', null);
}
```

---

## Entity

**Class:** `PitouFW\Core\Entity`  
**File:** `core/Entity.php`  
**Purpose:** Abstract base class for database entities using Active Record pattern.

### Properties

```php
protected int $id = 0;
```

### Abstract Methods

#### `public static abstract function getTableName(): string`

Must be implemented by subclasses to return the database table name.

```php
class User extends Entity {
    public static function getTableName(): string {
        return 'users';
    }
}
```

### Methods

#### `public function getId(): int`

Returns the entity's primary key.

```php
$user = User::findOne('email', 'user@example.com');
$userId = $user->getId();
```

#### `public function setId(int $id): Entity`

Sets the entity's primary key.

```php
$user = new User();
$user->setId(123);
```

#### `public function save(): int`

Saves the entity to the database (insert or update).

```php
$user = new User();
$user->setEmail('user@example.com')
     ->setName('John Doe');
$userId = $user->save(); // Returns inserted ID
```

#### `public function delete(): bool`

Deletes the entity from the database.

```php
$user = User::findOne('id', 123);
$success = $user->delete();
```

#### `public static function findOne(string $column, $value): ?static`

Finds a single entity by column value.

```php
$user = User::findOne('email', 'user@example.com');
$user = User::findOne('id', 123);
```

#### `public static function findAll(?string $condition = null, ?array $values = null): array`

Finds multiple entities with optional conditions.

```php
// Find all users
$users = User::findAll();

// Find with condition
$activeUsers = User::findAll('active = ?', [1]);
$adminUsers = User::findAll('role = ? AND active = ?', ['admin', 1]);
```

#### `public static function exists(string $column, $value): bool`

Checks if an entity exists with the given column value.

```php
if (User::exists('email', 'user@example.com')) {
    // User already exists
}
```

#### `public static function deleteWhere(string $condition, array $values = []): int`

Deletes entities matching the condition.

```php
// Delete inactive users
$deletedCount = User::deleteWhere('active = ?', [0]);
```

### Usage Examples

```php
// Create new entity
$user = new User();
$user->setEmail('john@example.com')
     ->setName('John Doe')
     ->setActive(1);
$userId = $user->save();

// Find existing entity
$user = User::findOne('id', $userId);
if ($user) {
    echo $user->getName();
}

// Update entity
$user->setName('John Smith');
$user->save();

// Delete entity
$user->delete();

// Check existence
if (User::exists('email', 'john@example.com')) {
    echo "User exists";
}
```

---

## DB

**Class:** `PitouFW\Core\DB`  
**File:** `core/DB.php`  
**Purpose:** Database connection management using PDO with singleton pattern.

### Properties

```php
private static ?PDO $instance = null;
private static string $db_host = DB_HOST;
private static string $db_name = DB_NAME;
private static string $db_user = DB_USER;
private static string $db_pass = DB_PASS;
```

### Methods

#### `public static function get(): PDO`

Returns the database connection instance.

```php
$pdo = DB::get();
$stmt = $pdo->prepare("SELECT * FROM users WHERE active = ?");
$stmt->execute([1]);
$users = $stmt->fetchAll();
```

### Usage Examples

```php
use PitouFW\Core\DB;

// Get database connection
$db = DB::get();

// Execute query
$stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
$stmt->execute(['admin']);
$adminCount = $stmt->fetchColumn();

// Complex query
$sql = "
    SELECT u.*, COUNT(p.id) as post_count 
    FROM users u 
    LEFT JOIN posts p ON u.id = p.user_id 
    WHERE u.active = ? 
    GROUP BY u.id
";
$stmt = $db->prepare($sql);
$stmt->execute([1]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

---

## Request

**Class:** `PitouFW\Core\Request`  
**File:** `core/Request.php`  
**Purpose:** Handles HTTP request parsing and provides access to URL arguments.

### Properties

```php
private static ?Request $instance = null;
private array $args = [];
```

### Methods

#### `public static function get(): Request`

Returns the singleton instance of the Request.

```php
$request = Request::get();
```

#### `public function getArg(int $index): string`

Returns the URL argument at the specified index.

```php
// URL: /user/profile/edit
$arg0 = Request::get()->getArg(0); // 'user'
$arg1 = Request::get()->getArg(1); // 'profile'
$arg2 = Request::get()->getArg(2); // 'edit'
```

### Usage Examples

```php
use PitouFW\Core\Request;

// Get URL segments
$controller = Request::get()->getArg(0); // First segment
$action = Request::get()->getArg(1);     // Second segment
$id = Request::get()->getArg(2);         // Third segment

// Example usage in controller
$userId = Request::get()->getArg(2);
if ($userId) {
    $user = User::findOne('id', $userId);
}
```

---

## Data

**Class:** `PitouFW\Core\Data`  
**File:** `core/Data.php`  
**Purpose:** Manages data passing from controllers to views.

### Properties

```php
private static ?Data $instance = null;
private array $data = [];
```

### Methods

#### `public static function get(): Data`

Returns the singleton instance of the Data class.

```php
$data = Data::get();
```

#### `public function add(string $key, $value): void`

Adds data to be passed to the view.

```php
Data::get()->add('title', 'Page Title');
Data::get()->add('user', $user);
Data::get()->add('posts', $posts);
```

#### `public function getData(): array`

Returns all data as an array.

```php
$allData = Data::get()->getData();
// Returns: ['title' => 'Page Title', 'user' => $user, 'posts' => $posts]
```

### Usage Examples

```php
use PitouFW\Core\Data;
use PitouFW\Core\Controller;

// Add page data
Data::get()->add('TITLE', 'User Dashboard');
Data::get()->add('user', UserModel::get());
Data::get()->add('stats', [
    'posts' => 10,
    'comments' => 45,
    'likes' => 123
]);

// Render view (data automatically available)
Controller::renderView('user/dashboard');
```

---

## Session

**Class:** `PitouFW\Core\Session`  
**File:** `core/Session.php`  
**Purpose:** Session management and configuration.

### Methods

#### `public static function start(): void`

Starts the session with secure configuration.

```php
Session::start();
```

### Usage Examples

```php
use PitouFW\Core\Session;

// Start session (called in index.php)
Session::start();

// Use standard PHP session functions
$_SESSION['user_id'] = 123;
$userId = $_SESSION['user_id'] ?? null;
```

---

## Alert

**Class:** `PitouFW\Core\Alert`  
**File:** `core/Alert.php`  
**Purpose:** Flash message system for user notifications.

### Methods

#### `public static function success(string $message): void`

Adds a success alert message.

```php
Alert::success('User created successfully!');
```

#### `public static function error(string $message): void`

Adds an error alert message.

```php
Alert::error('Invalid email address');
```

#### `public static function warning(string $message): void`

Adds a warning alert message.

```php
Alert::warning('Please verify your email');
```

#### `public static function info(string $message): void`

Adds an info alert message.

```php
Alert::info('Welcome to PitouFW!');
```

#### `public static function handle(): string`

Renders all alert messages as HTML.

```php
// In layout template
<?= Alert::handle() ?>
```

### Usage Examples

```php
use PitouFW\Core\Alert;
use PitouFW\Core\Router;

// In controller
if ($user->save()) {
    Alert::success('Profile updated successfully!');
    Router::redirect('user/profile');
} else {
    Alert::error('Failed to update profile');
}

// Multiple alerts
Alert::info('Processing your request...');
Alert::warning('This action cannot be undone');
```

---

## Utils

**Class:** `PitouFW\Core\Utils`  
**File:** `core/Utils.php`  
**Purpose:** Utility functions for common operations.

### Methods

#### `public static function fromSnakeCaseToCamelCase(string $string): string`

Converts snake_case to CamelCase.

```php
$camelCase = Utils::fromSnakeCaseToCamelCase('user_name');
// Returns: 'UserName'
```

#### `public static function secure($data)`

Secures data by escaping HTML entities (XSS protection).

```php
$safeData = Utils::secure($_POST);
$safeString = Utils::secure('<script>alert("xss")</script>');
// Returns: '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;'
```

#### `public static function str2hex(string $string): string`

Converts string to hexadecimal representation.

```php
$hex = Utils::str2hex('Hello');
// Returns: '48656c6c6f'
```

#### `public static function hex2str(string $hex): string`

Converts hexadecimal to string.

```php
$string = Utils::hex2str('48656c6c6f');
// Returns: 'Hello'
```

#### `public static function slugify(string $string, string $delimiter = '-'): string`

Creates URL-friendly slug from string.

```php
$slug = Utils::slugify('Hello World! This is a Test');
// Returns: 'hello-world-this-is-a-test'
```

#### `public static function isInternalCall(): bool`

Checks if the request is an internal API call.

```php
if (Utils::isInternalCall()) {
    // Handle internal API request
}
```

### Usage Examples

```php
use PitouFW\Core\Utils;

// Secure user input
$safeTitle = Utils::secure($_POST['title']);

// Create URL slug
$slug = Utils::slugify($article->getTitle());

// Method name generation
$methodName = 'set' . Utils::fromSnakeCaseToCamelCase('user_name');
// $methodName = 'setUserName'
```

---

## Additional Classes

### Mailer

**Purpose:** Email functionality using PHPMailer
**Key Methods:**
- `send()` - Send email
- `addAddress()` - Add recipient
- `setSubject()` - Set email subject

### Redis

**Purpose:** Redis cache management
**Key Methods:**
- `get()` - Get cached value
- `set()` - Set cache value
- `exists()` - Check if key exists

### Logger

**Purpose:** Application logging
**Key Methods:**
- `logError()` - Log error message
- `logInfo()` - Log info message
- `logWarning()` - Log warning message

### Crypt

**Purpose:** Encryption utilities
**Key Methods:**
- `encrypt()` - Encrypt data
- `decrypt()` - Decrypt data
- `hash()` - Generate hash

## Best Practices

### Entity Usage

1. **Always implement getTableName()** - Required for database operations
2. **Use type declarations** - Leverage PHP 8.3 type safety
3. **Implement validation** - Add validation in setters
4. **Use meaningful names** - Clear property and method names

### Controller Patterns

1. **Keep controllers thin** - Move logic to models
2. **Always set page title** - Use `Data::get()->add('TITLE', 'Page Title')`
3. **Handle errors gracefully** - Use HTTP status methods
4. **Validate input** - Check POST data before processing

### Security Considerations

1. **Always escape output** - Use `Utils::secure()` for user data
2. **Validate database input** - Check data before saving
3. **Use prepared statements** - Entity methods use prepared statements automatically
4. **Check authentication** - Verify user permissions in controllers

### Performance Tips

1. **Use singleton patterns** - Core classes use singleton for efficiency
2. **Cache database queries** - Use Redis for frequently accessed data
3. **Minimize database calls** - Load related data efficiently
4. **Use appropriate HTTP status codes** - Help with caching and SEO