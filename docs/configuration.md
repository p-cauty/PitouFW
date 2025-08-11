# Configuration Guide

PitouFW uses environment-based configuration through `.env` files and PHP configuration files. This document covers all configuration options, environment setup, and best practices for different deployment scenarios.

## Overview

The configuration system in PitouFW consists of:

- **Environment Files** (`.env`) - Environment-specific settings
- **Configuration Files** (`config/`) - Application and framework settings
- **Constants** - Global application constants
- **Route Configuration** (`routes.php`) - URL routing definitions

### Configuration Hierarchy

1. **Environment Variables** (`.env`) - Highest priority
2. **PHP Configuration** (`config/app.php`) - Application defaults
3. **Framework Constants** (`config/config.php`) - System-wide settings
4. **Runtime Constants** - Generated during bootstrap

## Environment Configuration

### Environment File (`.env`)

The `.env` file contains environment-specific configuration:

```env
# Application Environment
ENV_NAME=local                          # Environment name (local, staging, prod)
APP_URL=http://localhost:8080/          # Base application URL
PROD_HOST=localhost                     # Production host
LOGGING=true                            # Enable logging
FORCE_CHRISTMAS=false                   # Enable Christmas theme

# Docker Configuration
APP_PORT=8080                           # Application port
DB_PORT=3307                            # Database port (external)
PMA_PORT=8081                           # phpMyAdmin port

# Database Configuration
DB_HOST=db                              # Database host
DB_NAME=pitoufw                         # Database name
DB_USER=docker                          # Database username
DB_PASS=secret                          # Database password

# Cache Configuration
REDIS_HOST=cache                        # Redis host
REDIS_PORT=6379                         # Redis port
REDIS_PASS=                             # Redis password (empty for none)

# Email Configuration
SMTP_HOST=in-v3.mailjet.com            # SMTP server host
SMTP_PORT=587                           # SMTP server port
SMTP_SECURE=tls                         # SMTP security (tls/ssl)
SMTP_USER=xxxxx                         # SMTP username
SMTP_PASS=xxxxx                         # SMTP password
SMTP_FROM=hello@phpeter.fr              # Default from address

# Email Settings
EMAIL_SEND_AS_DEFAULT="PitouFW <hello@phpeter.fr>"  # Default sender
EMAIL_RENDERING_KEY=xxxxx               # Email rendering key
```

### Environment Types

PitouFW supports multiple environment types:

#### Development (`ENV_NAME=local` or `ENV_NAME=dev`)
```env
ENV_NAME=local
APP_URL=http://localhost:8080/
LOGGING=true
DB_HOST=db                              # For Docker
# OR
DB_HOST=localhost                       # For local development
```

#### Staging (`ENV_NAME=staging` or `ENV_NAME=preprod`)
```env
ENV_NAME=staging
APP_URL=https://staging.yoursite.com/
LOGGING=true
DB_HOST=staging-db.yoursite.com
```

#### Production (`ENV_NAME=prod` or `ENV_NAME=production`)
```env
ENV_NAME=production
APP_URL=https://yoursite.com/
LOGGING=false                           # Disable detailed logging
DB_HOST=prod-db.yoursite.com
```

### Environment Constants

The framework automatically creates constants based on environment:

```php
// Environment detection
const PRODUCTION_ENV = ENV_NAME === 'prod' || ENV_NAME === 'production';
const STAGING_ENV = ENV_NAME === 'preprod' || ENV_NAME === 'staging';
const DEV_ENV = ENV_NAME === 'dev' || ENV_NAME === 'local';

// Request detection
const POST = $_SERVER['REQUEST_METHOD'] === 'POST';

// URL constants
const WEBROOT = '/path/to/webroot/';     // Web root path
```

## Application Configuration

### Main Configuration (`config/config.php`)

The main configuration file loads environment variables and sets up constants:

```php
<?php

// Define root directory
define('ROOT', str_replace('config', '', __DIR__));

// Load application configuration
require_once __DIR__ . '/app.php';

// Load environment file
$env_file = __DIR__ . '/../.env';
if (!file_exists($env_file)) {
    echo 'Please create a .env file in the root directory.';
    die;
}

// Parse environment variables
$envs = parse_ini_file($env_file);

// Define constants from environment
foreach ($envs as $key => $value) {
    if (str_starts_with($key, '#')) {
        continue; // Skip comments
    }

    if (!defined($key)) {
        // Handle JSON values
        if (json_decode($value) !== null) {
            define($key, json_decode($value, true));
        } else {
            define($key, $value);
        }
    }
}

// Environment-specific constants
const PRODUCTION_ENV = ENV_NAME === 'prod' || ENV_NAME === 'production';
const STAGING_ENV = ENV_NAME === 'preprod' || ENV_NAME === 'staging';
const DEV_ENV = ENV_NAME === 'dev' || ENV_NAME === 'local';

// Request constants
define('POST', isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST');
define('WEBROOT', isset($_SERVER['SCRIPT_NAME']) ? str_replace('index.php', '', $_SERVER['SCRIPT_NAME']) : '');

// Directory constants
const APP = ROOT . 'app/';
const ENTITIES = APP . 'entities/';
const MODELS = APP . 'models/';
const VIEWS = APP . 'views/';
const CONTROLLERS = APP . 'controllers/';
const CORE = ROOT . 'core/';
const STORAGE = ROOT . 'storage/';

// Asset constants
const ASSETS = WEBROOT . 'assets/';
const CSS = ASSETS . 'css/';
const JS = ASSETS . 'js/';
const FONTS = ASSETS . 'fonts/';
const IMG = ASSETS . 'img/';
const VENDORS = ASSETS . 'vendors/';
```

### Application Settings (`config/app.php`)

Application-specific configuration:

```php
<?php

// Application Information
const NAME = 'PitouFW';                 // Application name
const AUTHOR = 'Peter Cauty';           // Application author
const VERSION = '3.0.0';                // Application version

// Features
const TRUST_NEEDED = true;              // Require email verification
const ACCEPTED_LANGUAGES = ['en', 'fr']; // Supported languages

// Security
const INTERNAL_API_KEY = 'your-internal-api-key'; // Internal API authentication

// Pagination
const ITEMS_PER_PAGE = 25;              // Default pagination size

// File Upload
const MAX_UPLOAD_SIZE = 5242880;        // 5MB in bytes
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

// Session Configuration
const SESSION_LIFETIME = 86400;         // 24 hours
const SESSION_REMEMBER_LIFETIME = 31536000; // 1 year

// Email Configuration
const EMAIL_QUEUE_ENABLED = true;       // Enable email queue
const EMAIL_RETRY_ATTEMPTS = 3;         // Email retry attempts
```

## Database Configuration

### Connection Settings

Database configuration is handled through environment variables:

```env
# Basic connection
DB_HOST=localhost
DB_NAME=pitoufw
DB_USER=username
DB_PASS=password

# Docker configuration
DB_HOST=db                              # Docker service name
DB_PORT=3307                            # External port mapping
```

### Migration Configuration (`phinx.php`)

Phinx migration configuration:

```php
<?php

return [
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'port' => 3306,
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'port' => 3306,
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME . '_test',
            'user' => DB_USER,
            'pass' => DB_PASS,
            'port' => 3306,
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
```

## Email Configuration

### SMTP Settings

Email configuration for different providers:

#### Gmail SMTP
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
```

#### Mailjet
```env
SMTP_HOST=in-v3.mailjet.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=your-mailjet-key
SMTP_PASS=your-mailjet-secret
```

#### SendGrid
```env
SMTP_HOST=smtp.sendgrid.net
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=apikey
SMTP_PASS=your-sendgrid-api-key
```

### Email Templates

Configure email template paths and settings:

```php
// In config/app.php
const EMAIL_TEMPLATES_PATH = VIEWS . 'mail/';
const EMAIL_DEFAULT_LAYOUT = '_layout.php';
const EMAIL_DEFAULT_LANGUAGE = 'en';

// Email queue settings
const EMAIL_QUEUE_BATCH_SIZE = 100;     // Process 100 emails per batch
const EMAIL_QUEUE_RETRY_DELAY = 300;    // 5 minutes between retries
```

## Cache Configuration

### Redis Settings

Redis configuration for caching and sessions:

```env
# Basic Redis configuration
REDIS_HOST=cache                        # Redis host
REDIS_PORT=6379                         # Redis port
REDIS_PASS=                             # Redis password (optional)

# Advanced Redis settings
REDIS_DATABASE=0                        # Redis database number
REDIS_PREFIX=pitoufw_                   # Key prefix
REDIS_TIMEOUT=5                         # Connection timeout
```

### Cache Settings

```php
// In config/app.php
const CACHE_ENABLED = true;             // Enable caching
const CACHE_DEFAULT_TTL = 3600;         // Default cache time (1 hour)
const CACHE_SESSION_TTL = 86400;        // Session cache time (24 hours)

// Cache key prefixes
const CACHE_PREFIX_SESSION = 'session_';
const CACHE_PREFIX_USER = 'user_';
const CACHE_PREFIX_CONFIG = 'config_';
```

## Routing Configuration

### Route Definition (`routes.php`)

Define your application routes:

```php
<?php

const ROUTES = [
    // Public routes
    'home' => 'home',
    'about' => 'about',
    'contact' => 'contact',
    
    // User routes
    'user' => [
        'register' => 'register',
        'login' => 'login',
        'logout' => 'logout',
        'profile' => 'profile',
        'forgot-passwd' => 'forgot_passwd',
        'passwd-reset' => 'passwd_reset',
        'confirm' => 'confirm',
        'resend' => 'resend',
        'unsubscribe' => 'unsubscribe'
    ],
    
    // Admin routes
    'admin' => [
        'dashboard' => 'dashboard',
        'users' => [
            'list' => 'list',
            'edit' => 'edit',
            'delete' => 'delete'
        ],
        'settings' => 'settings'
    ],
    
    // API routes
    'api' => [
        'version' => 'version',
        'users' => 'users',
        'mailer' => 'mailer'
    ],
    
    // Cron jobs
    'cron' => 'cron'
];
```

### Route Groups

Organize related routes into groups:

```php
// User management routes
const USER_ROUTES = [
    'profile' => 'profile',
    'settings' => 'settings',
    'password' => 'password'
];

// Admin routes
const ADMIN_ROUTES = [
    'dashboard' => 'dashboard',
    'users' => USER_MANAGEMENT_ROUTES,
    'reports' => 'reports'
];

// Main routes
const ROUTES = [
    'home' => 'home',
    'user' => USER_ROUTES,
    'admin' => ADMIN_ROUTES
];
```

## Security Configuration

### Authentication Settings

```php
// In config/app.php
const PASSWORD_MIN_LENGTH = 8;          // Minimum password length
const PASSWORD_REQUIRE_UPPER = true;    // Require uppercase letter
const PASSWORD_REQUIRE_LOWER = true;    // Require lowercase letter
const PASSWORD_REQUIRE_NUMBER = true;   // Require number
const PASSWORD_REQUIRE_SPECIAL = false; // Require special character

// Session security
const SESSION_SECURE = true;            // HTTPS only cookies
const SESSION_HTTPONLY = true;          // HTTP only cookies
const SESSION_SAMESITE = 'Strict';      // SameSite cookie attribute

// CSRF protection
const CSRF_TOKEN_LENGTH = 32;           // CSRF token length
const CSRF_TOKEN_LIFETIME = 3600;       // CSRF token lifetime (1 hour)
```

### Rate Limiting

```php
// Rate limiting configuration
const RATE_LIMIT_ENABLED = true;        // Enable rate limiting
const RATE_LIMIT_WINDOW = 3600;         // Rate limit window (1 hour)

// Login rate limiting
const LOGIN_MAX_ATTEMPTS = 5;           // Max login attempts
const LOGIN_LOCKOUT_DURATION = 900;     // 15 minutes lockout

// Password reset rate limiting
const FORGOT_PASSWD_MAX_ATTEMPTS = 3;   // Max reset attempts per hour
const FORGOT_PASSWD_COOLDOWN = 300;     // 5 minutes between requests
```

## Internationalization Configuration

### Language Settings

```php
// In config/app.php
const DEFAULT_LANGUAGE = 'en';          // Default language
const ACCEPTED_LANGUAGES = ['en', 'fr', 'es']; // Supported languages
const LANGUAGE_AUTO_DETECT = true;      // Auto-detect browser language

// Translation settings
const TRANSLATION_PATH = ROOT . 'lang/'; // Translation files path
const TRANSLATION_CACHE = true;         // Cache translations
const TRANSLATION_FALLBACK = 'en';      // Fallback language
```

### Translation File Structure

```
lang/
├── en/
│   ├── common.php              # Common translations
│   ├── auth.php                # Authentication messages
│   └── validation.php          # Validation messages
├── fr/
│   ├── common.php
│   ├── auth.php
│   └── validation.php
└── es/
    ├── common.php
    ├── auth.php
    └── validation.php
```

## Logging Configuration

### Log Settings

```php
// In config/app.php
const LOGGING_ENABLED = true;           // Enable logging
const LOG_LEVEL = 'info';               // Log level (debug, info, warning, error)
const LOG_PATH = ROOT . 'log/';         // Log file path
const LOG_MAX_SIZE = 10485760;          // 10MB max log file size
const LOG_ROTATE = true;                // Enable log rotation

// Log channels
const LOG_CHANNELS = [
    'default' => 'app.log',
    'error' => 'error.log',
    'auth' => 'auth.log',
    'api' => 'api.log'
];
```

### Error Reporting

```php
// Error reporting configuration
if (DEV_ENV) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}
```

## Docker Configuration

### Docker Compose (`docker-compose.yml`)

```yaml
version: '3.8'

services:
  web:
    build: .docker/apache
    ports:
      - "${APP_PORT:-8080}:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
      - cache
    environment:
      - ENV_NAME=${ENV_NAME}

  db:
    image: mysql:8.0
    ports:
      - "${DB_PORT:-3307}:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASS}
    volumes:
      - db_data:/var/lib/mysql

  cache:
    image: redis:7.0-alpine
    ports:
      - "${REDIS_PORT:-6379}:6379"
    volumes:
      - cache_data:/data

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "${PMA_PORT:-8081}:80"
    environment:
      PMA_HOST: db
      PMA_USER: ${DB_USER}
      PMA_PASSWORD: ${DB_PASS}
    depends_on:
      - db

volumes:
  db_data:
  cache_data:
```

### Docker Environment Variables

```env
# Docker-specific configuration
COMPOSE_PROJECT_NAME=pitoufw          # Docker compose project name
DOCKER_BUILDKIT=1                     # Enable BuildKit

# Service ports
APP_PORT=8080                         # Web application port
DB_PORT=3307                          # Database port
REDIS_PORT=6379                       # Redis port
PMA_PORT=8081                         # phpMyAdmin port
```

## Environment-Specific Configurations

### Development Environment

```env
# .env.local
ENV_NAME=local
APP_URL=http://localhost:8080/
LOGGING=true

# Database
DB_HOST=db
DB_NAME=pitoufw_dev
DB_USER=docker
DB_PASS=secret

# Cache
REDIS_HOST=cache

# Debug settings
DEBUG_MODE=true
QUERY_LOG=true
```

### Staging Environment

```env
# .env.staging
ENV_NAME=staging
APP_URL=https://staging.yoursite.com/
LOGGING=true

# Database
DB_HOST=staging-db.internal
DB_NAME=pitoufw_staging
DB_USER=staging_user
DB_PASS=secure_staging_password

# Cache
REDIS_HOST=staging-redis.internal

# Email (use test provider)
SMTP_HOST=smtp.mailtrap.io
```

### Production Environment

```env
# .env.production
ENV_NAME=production
APP_URL=https://yoursite.com/
LOGGING=false

# Database
DB_HOST=prod-db.internal
DB_NAME=pitoufw_production
DB_USER=prod_user
DB_PASS=very_secure_production_password

# Cache
REDIS_HOST=prod-redis.internal

# Email (production SMTP)
SMTP_HOST=smtp.sendgrid.net
```

## Best Practices

### Environment Management

1. **Never commit `.env` files** - Add to `.gitignore`
2. **Use `.env.example`** - Provide template for other developers
3. **Environment-specific files** - Use separate files for different environments
4. **Secure sensitive data** - Use environment variables for secrets

### Configuration Organization

1. **Group related settings** - Organize by feature or component
2. **Use descriptive names** - Clear, self-documenting constant names
3. **Provide defaults** - Always have fallback values
4. **Document complex settings** - Add comments for unclear configurations

### Security Considerations

1. **Never hardcode secrets** - Use environment variables
2. **Validate configuration** - Check required settings on startup
3. **Use secure defaults** - Default to secure settings
4. **Audit configuration** - Regularly review configuration files

### Performance Optimization

1. **Cache configuration** - Use opcode caching
2. **Minimize file reads** - Load configuration once
3. **Use constants** - Constants are faster than variables
4. **Optimize database settings** - Tune connection pooling and timeouts