# PitouFW Architecture Overview

PitouFW follows a classic Model-View-Controller (MVC) architectural pattern with additional components for modern web development. This document explains the framework's architecture, design principles, and how components interact.

## Design Philosophy

PitouFW is built on the principle of **"simplicity without sacrifice"**. It provides:

- **Minimal Complexity:** Simple, understandable code without unnecessary abstraction
- **Convention over Configuration:** Sensible defaults with the ability to customize when needed
- **Explicit Behavior:** Clear, predictable behavior without hidden magic
- **Developer Experience:** Focus on developer productivity and ease of understanding

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    HTTP Request                              │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                 Entry Point                                 │
│                (public/index.php)                          │
│  • Bootstrap application                                    │
│  • Load configuration                                       │
│  • Initialize services                                      │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                   Router                                    │
│                (core/Router.php)                           │
│  • Parse URL and match routes                              │
│  • Determine controller to execute                         │
│  • Handle 404 errors                                       │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                 Controller                                  │
│            (app/controllers/*.php)                         │
│  • Handle business logic                                   │
│  • Process form data                                       │
│  • Interact with models/entities                          │
│  • Prepare data for views                                 │
└─────────────────────┬───────────────────────────────────────┘
                      │
     ┌────────────────┼────────────────┐
     │                │                │
┌────▼───┐      ┌─────▼─────┐    ┌─────▼─────┐
│ Model  │      │  Entity   │    │   View    │
│        │      │           │    │           │
│Business│◄─────┤ Database  │    │ Templates │
│ Logic  │      │   ORM     │    │   HTML    │
└────────┘      └─────┬─────┘    └───────────┘
                      │
                ┌─────▼─────┐
                │ Database  │
                │  (MySQL)  │
                └───────────┘
```

## Core Components

### 1. Entry Point (`public/index.php`)

The application entry point that:
- Loads Composer autoloader
- Includes configuration files
- Initializes session management
- Sets up error handling
- Processes API request data
- Initializes internationalization
- Checks user authentication status
- Routes requests to appropriate controllers

### 2. Router (`core/Router.php`)

**Responsibility:** URL routing and controller resolution

**Features:**
- File-based route configuration (`routes.php`)
- Nested route support (e.g., `/user/profile`, `/api/version`)
- Automatic 404 handling
- Clean URL parsing

**How it works:**
```php
// routes.php defines the routing structure
const ROUTES = [
    'home' => 'home',
    'user' => [
        'register' => 'register',
        'login' => 'login'
    ]
];

// Router maps URLs to controller files
// /user/login → app/controllers/user/login.php
```

### 3. Controller (`core/Controller.php`)

**Responsibility:** Base controller functionality

**Features:**
- View rendering with layout support
- HTTP status code handling (404, 500, etc.)
- API response formatting
- Data security (XSS protection)

**Controller Pattern:**
```php
// Typical controller structure
use PitouFW\Core\Controller;
use PitouFW\Core\Data;

// Handle POST requests
if (POST) {
    // Process form data
}

// Prepare view data
Data::get()->add('TITLE', 'Page Title');

// Render view
Controller::renderView('template/name');
```

### 4. Entity System (`core/Entity.php`)

**Responsibility:** Database abstraction and ORM functionality

**Features:**
- Active Record pattern
- Automatic getter/setter generation
- CRUD operations
- Query building
- Data validation

**Entity Pattern:**
```php
class User extends Entity {
    protected string $email = '';
    protected string $password = '';
    
    public static function getTableName(): string {
        return 'users';
    }
    
    // Getters and setters automatically available
    // save(), findOne(), findAll(), delete() methods inherited
}
```

### 5. Database Layer (`core/DB.php`)

**Responsibility:** Database connection and query execution

**Features:**
- PDO-based MySQL connection
- Singleton pattern for connection management
- UTF-8 character set configuration
- Connection error handling

### 6. Request Handling (`core/Request.php`)

**Responsibility:** HTTP request processing

**Features:**
- URL argument parsing
- Parameter extraction
- Request method detection
- Clean API for accessing request data

## Data Flow

### Typical Request Lifecycle

1. **Request Arrival**
   - HTTP request hits `public/index.php`
   - Application bootstrap begins

2. **Initialization**
   - Load configuration from `.env`
   - Start session management
   - Initialize internationalization
   - Set up error handling

3. **Authentication Check**
   - Verify user session if logged in
   - Check account status
   - Redirect if account disabled

4. **Routing**
   - Router parses URL path
   - Matches against route configuration
   - Determines controller file to execute

5. **Controller Execution**
   - Include and execute controller file
   - Process POST data if present
   - Interact with models/entities
   - Prepare data for view

6. **View Rendering**
   - Extract and secure view data
   - Include view template
   - Apply main layout (if specified)
   - Output HTML to browser

7. **Response**
   - Send HTTP response to client
   - Log any errors or activities

### Data Flow Example

```php
// URL: /user/profile
// Route: user -> profile -> app/controllers/user/profile.php

// 1. Router maps URL to controller
Router::get()->getPathToRequire(); // Returns path to profile.php

// 2. Controller processes request
if (POST) {
    $user = UserModel::get();
    $user->setName($_POST['name'])->save();
}

// 3. Prepare view data
Data::get()->add('user', UserModel::get());
Data::get()->add('TITLE', 'User Profile');

// 4. Render view
Controller::renderView('user/profile/form');
// Includes: app/views/user/profile/form.php within app/views/mainView.php
```

## Service Layer

### Session Management (`core/Session.php`)

- Secure session configuration
- Session data management
- User state persistence

### Caching (`core/Redis.php`)

- Redis integration for caching
- Session storage (optional)
- Performance optimization

### Email System (`core/Mailer.php`)

- PHPMailer integration
- SMTP configuration
- Email queue management
- Template-based emails

### Logging (`core/Logger.php`)

- Error logging
- Activity tracking
- Debug information
- Configurable log levels

### Security (`core/Crypt.php`, `core/Utils.php`)

- Password hashing
- Data encryption
- XSS prevention
- CSRF protection

### Internationalization (`core/Translator.php`)

- Multi-language support
- PHP-i18n integration
- Locale management
- Translation helpers

## Configuration System

### Environment-based Configuration

PitouFW uses a `.env` file for configuration:

```env
# Application settings
ENV_NAME=local
APP_URL=http://localhost:8080/
LOGGING=true

# Database configuration
DB_HOST=db
DB_NAME=pitoufw
DB_USER=docker
DB_PASS=secret
```

### Configuration Loading

1. Parse `.env` file using `parse_ini_file()`
2. Define constants for each configuration value
3. Set environment-specific constants (PRODUCTION_ENV, DEV_ENV)
4. Define path constants for framework directories

### Constants

Key constants available throughout the application:

```php
// Environment
const PRODUCTION_ENV = true/false;
const DEV_ENV = true/false;

// Paths
const ROOT = '/path/to/project/';
const APP = ROOT . 'app/';
const CORE = ROOT . 'core/';
const VIEWS = APP . 'views/';

// Request
const POST = true/false; // Is POST request
const WEBROOT = '/'; // Web root path
```

## Error Handling

### HTTP Error Handling

- Automatic HTTP status code responses (404, 500, etc.)
- Custom error pages in `app/views/error/`
- API-specific error responses
- Graceful error degradation

### Exception Handling

- Try-catch blocks for critical operations
- Database connection error handling
- Email sending error handling
- Logging of all errors

### Development vs Production

- Development: Detailed error messages and stack traces
- Production: User-friendly error pages and detailed logging

## Security Architecture

### Input Validation

- POST data validation in controllers
- Email format validation
- Required field checking
- Data type validation

### Output Security

- Automatic XSS prevention with `Utils::secure()`
- HTML entity encoding
- Safe data extraction for views

### Authentication & Authorization

- Session-based authentication
- Password hashing with PHP's password functions
- User role checking
- Account status validation

### Database Security

- PDO prepared statements prevent SQL injection
- Parameter binding for all queries
- UTF-8 character set enforcement

## Performance Considerations

### Caching Strategy

- Redis caching for session data
- Opcode caching (recommended)
- Database query optimization

### Database Optimization

- Single database connection per request
- Lazy loading of relationships
- Efficient query patterns in entities

### Asset Management

- Static asset serving through web server
- CSS/JS minification (external tools)
- Image optimization recommendations

## Extensibility

### Adding New Features

1. **Routes:** Add entries to `routes.php`
2. **Controllers:** Create files in `app/controllers/`
3. **Entities:** Create classes in `entities/`
4. **Views:** Create templates in `app/views/`
5. **Models:** Add business logic in `app/models/`

### Framework Extensions

- Core classes are designed for inheritance
- Hook system through controller methods
- Plugin architecture possible through composition

### Third-party Integration

- Composer for package management
- PSR-4 autoloading support
- Easy integration of external libraries

## Best Practices

### Controller Design

- Keep controllers thin
- Handle only HTTP request/response logic
- Delegate business logic to models
- Use appropriate HTTP status codes

### Entity Design

- One entity per database table
- Include validation in entity methods
- Use meaningful property names
- Implement proper relationships

### View Design

- Separate presentation from logic
- Use the main layout for consistent structure
- Implement proper data escaping
- Keep views simple and readable

### Security Best Practices

- Validate all input data
- Use prepared statements for database queries
- Implement proper authentication checks
- Log security-related events
- Keep framework and dependencies updated