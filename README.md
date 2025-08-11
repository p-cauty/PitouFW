# PitouFW

A personal PHP 8.3 MVC framework for developers who didn't want a framework in the first place.

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-3.0.0-orange.svg)](composer.json)

## Overview

PitouFW is a lightweight, efficient PHP framework that provides essential MVC architecture without the bloat. Built by Peter Cauty, it's designed for developers who prefer simplicity and control over their codebase while still having access to modern framework features.

### Key Features

- **Lightweight MVC Architecture** - Clean separation of concerns without unnecessary complexity
- **Simple Routing System** - File-based routing configuration that's easy to understand
- **Database ORM** - Entity-based database abstraction with PDO
- **User Authentication** - Built-in user management and session handling
- **Email System** - Integrated PHPMailer for reliable email delivery
- **Internationalization** - Multi-language support with PHP-i18n
- **Redis Caching** - Optional Redis support for improved performance
- **Security Features** - Built-in encryption, CSRF protection, and secure data handling
- **Logging System** - Comprehensive error and activity logging
- **Docker Support** - Full Docker development environment included

## Quick Start

### Requirements

- PHP 8.3+ with extensions: curl, iconv, imap, json, openssl, pdo_mysql, redis, zip
- MySQL 8.0+
- Composer
- Docker (optional, but recommended for development)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/p-cauty/PitouFW.git
   cd PitouFW
   ```

2. **Install dependencies:**
   ```bash
   composer install --prefer-dist
   ```

3. **Setup environment:**
   ```bash
   cp example.env .env
   ```
   Edit `.env` to configure your database and other settings.

### Development with Docker (Recommended)

1. **Start Docker services:**
   ```bash
   docker compose up -d
   ```

2. **Setup database:**
   ```bash
   # Create database and permissions
   docker compose exec db mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS pitoufw; GRANT ALL PRIVILEGES ON pitoufw.* TO 'docker'@'%'; FLUSH PRIVILEGES;"
   
   # Run migrations
   docker compose exec web php vendor/bin/phinx migrate
   ```

3. **Access your application:**
   - Web Application: http://localhost:8080/
   - phpMyAdmin: http://localhost:8081/ (user: docker, password: secret)

### Development without Docker

1. **Configure your database** in `.env` file
2. **Run migrations:**
   ```bash
   php vendor/bin/phinx migrate
   ```
3. **Start built-in server:**
   ```bash
   php -S localhost:8000 -t public
   ```
4. **Access your application:** http://localhost:8000/

## Project Structure

```
PitouFW/
├── app/                    # Application layer
│   ├── controllers/        # Route controllers
│   ├── models/            # Business logic models
│   └── views/             # View templates
├── core/                  # Framework core classes
│   ├── Router.php         # URL routing system
│   ├── Controller.php     # Base controller
│   ├── Entity.php         # Database entity base class
│   ├── DB.php            # Database abstraction
│   └── ...               # Other core classes
├── config/               # Configuration files
├── public/               # Web root directory
│   ├── index.php         # Application entry point
│   └── assets/           # Static assets (CSS, JS, images)
├── db/                   # Database migrations
├── entities/             # Data entity classes
├── .docker/              # Docker configuration
├── routes.php            # Route definitions
└── docker-compose.yml    # Docker services
```

## Basic Usage

### Creating a Controller

Create a new controller in `app/controllers/`:

```php
<?php
// app/controllers/example.php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;

// Handle form submission
if (POST) {
    // Process POST data
    $name = $_POST['name'] ?? '';
    Data::get()->add('message', "Hello, $name!");
}

// Set page data
Data::get()->add('TITLE', 'Example Page');

// Render view
Controller::renderView('example/example');
```

### Creating a View

Create a corresponding view in `app/views/`:

```php
<?php
// app/views/example/example.php
?>
<h1><?= $TITLE ?></h1>

<?php if (isset($message)): ?>
    <p><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="name" placeholder="Enter your name" required>
    <button type="submit">Submit</button>
</form>
```

### Adding Routes

Add routes to `routes.php`:

```php
const ROUTES = [
    'home' => 'home',
    'example' => 'example',
    'user' => [
        'register' => 'register',
        'login' => 'login',
        // ... other user routes
    ]
];
```

### Working with Entities

Create an entity class:

```php
<?php
// entities/Example.php

namespace PitouFW\Entity;

use PitouFW\Core\Entity;

class Example extends Entity {
    protected string $name = '';
    protected string $email = '';
    
    public static function getTableName(): string {
        return 'examples';
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }
}
```

Use the entity in your controller:

```php
use PitouFW\Entity\Example;

// Create new record
$example = new Example();
$example->setName('John Doe')
        ->setEmail('john@example.com')
        ->save();

// Find existing record
$example = Example::findOne('email', 'john@example.com');
```

## Configuration

### Environment Variables

Key configuration options in `.env`:

- **APP_URL** - Base URL of your application
- **DB_HOST**, **DB_NAME**, **DB_USER**, **DB_PASS** - Database connection
- **SMTP_HOST**, **SMTP_USER**, **SMTP_PASS** - Email configuration
- **REDIS_HOST**, **REDIS_PORT** - Redis cache configuration

### Database Migrations

Create migrations using Phinx:

```bash
# Create a new migration
php vendor/bin/phinx create CreateExampleTable

# Run migrations
php vendor/bin/phinx migrate

# Rollback migrations
php vendor/bin/phinx rollback
```

## API Endpoints

PitouFW includes built-in API endpoints:

- `GET /api/version` - Returns application version information
- `POST /api/mailer` - Internal email sending endpoint

## Security Features

- **CSRF Protection** - Automatic CSRF token validation
- **XSS Prevention** - Automatic output escaping with `Utils::secure()`
- **SQL Injection Protection** - PDO prepared statements
- **Session Security** - Secure session configuration
- **Password Hashing** - Built-in secure password handling

## Development Tools

### Docker Services

- **Web Server** - Apache with PHP 8.3
- **MySQL 8.0** - Database server
- **Redis 7.0** - Caching server
- **phpMyAdmin** - Database management interface

### Logging

Access application logs:

```bash
# View web server logs
docker compose logs web

# View all service logs
docker compose logs
```

## Testing

While PitouFW doesn't include a formal testing framework, you can test your application manually:

1. **Home page:** `curl http://localhost:8080/`
2. **User registration:** `curl http://localhost:8080/user/register`
3. **API endpoints:** `curl http://localhost:8080/api/version`

## Documentation

For detailed documentation, see the `docs/` directory:

- [Getting Started](docs/getting-started.md)
- [Architecture Overview](docs/architecture.md)
- [Core Classes API](docs/api/README.md)
- [Routing System](docs/routing.md)
- [Database & ORM](docs/database.md)
- [Authentication](docs/authentication.md)
- [Views & Templating](docs/views.md)
- [Configuration](docs/configuration.md)

## Contributing

This is a personal framework, but contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

**Peter Cauty**
- Website: https://peter.cauty.fr
- Email: peter@cauty.fr

---

*PitouFW - For developers who didn't want a framework in the first place.*