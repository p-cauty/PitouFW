# Getting Started with PitouFW

This guide will help you set up and start developing with PitouFW, a lightweight PHP MVC framework.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.3 or higher** with required extensions
- **Composer** for dependency management
- **MySQL 8.0+** or **MariaDB 10.3+**
- **Docker** (optional, but recommended for development)

### Required PHP Extensions

- `curl` - HTTP client functionality
- `iconv` - Character encoding conversion
- `imap` - Email functionality
- `json` - JSON handling
- `openssl` - Cryptographic functions
- `pdo_mysql` - Database connectivity
- `redis` - Cache functionality (optional)
- `zip` - Archive handling

Check your PHP extensions:
```bash
php -m | grep -E "(curl|iconv|imap|json|openssl|pdo_mysql|redis|zip)"
```

## Installation Methods

### Method 1: Docker Development (Recommended)

Docker provides a consistent development environment with all services pre-configured.

1. **Clone and setup:**
   ```bash
   git clone https://github.com/p-cauty/PitouFW.git
   cd PitouFW
   composer install --prefer-dist
   cp example.env .env
   ```

2. **Start Docker services:**
   ```bash
   docker compose up -d
   ```
   
   This starts:
   - Web server on port 8080
   - MySQL database on port 3307
   - Redis cache on port 6379
   - phpMyAdmin on port 8081

3. **Setup database:**
   ```bash
   # Create database and grant permissions
   docker compose exec db mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS pitoufw; GRANT ALL PRIVILEGES ON pitoufw.* TO 'docker'@'%'; FLUSH PRIVILEGES;"
   
   # Run migrations
   docker compose exec web php vendor/bin/phinx migrate
   ```

4. **Verify installation:**
   Open http://localhost:8080/ in your browser

### Method 2: Local Development

For development without Docker, set up your local environment:

1. **Install and setup:**
   ```bash
   git clone https://github.com/p-cauty/PitouFW.git
   cd PitouFW
   composer install --prefer-dist
   cp example.env .env
   ```

2. **Configure database:**
   Edit `.env` file with your local database settings:
   ```env
   DB_HOST=localhost
   DB_NAME=pitoufw
   DB_USER=your_username
   DB_PASS=your_password
   ```

3. **Create database:**
   ```sql
   CREATE DATABASE pitoufw;
   ```

4. **Run migrations:**
   ```bash
   php vendor/bin/phinx migrate
   ```

5. **Start development server:**
   ```bash
   php -S localhost:8000 -t public
   ```

6. **Access application:**
   Open http://localhost:8000/ in your browser

## Configuration

### Environment Configuration

The `.env` file contains all configuration options. Key settings include:

```env
# Application
ENV_NAME=local
APP_URL=http://localhost:8080/
LOGGING=true

# Database
DB_HOST=db
DB_NAME=pitoufw
DB_USER=docker
DB_PASS=secret

# Cache (Redis)
REDIS_HOST=cache
REDIS_PORT=6379

# Email (SMTP)
SMTP_HOST=your-smtp-host
SMTP_USER=your-smtp-user
SMTP_PASS=your-smtp-password
SMTP_FROM=your-email@domain.com
```

### Important Configuration Notes

- **Production vs Development:** Set `ENV_NAME=prod` for production environments
- **Database Configuration:** Use `localhost` for local development, `db` for Docker
- **Email Configuration:** Required for user registration and password reset features
- **Redis Configuration:** Optional but recommended for session storage and caching

## Project Structure Overview

Understanding the project structure is crucial for effective development:

```
PitouFW/
├── app/                    # Your application code
│   ├── controllers/        # Request handlers
│   │   ├── home.php       # Homepage controller
│   │   ├── user/          # User-related controllers
│   │   └── api/           # API controllers
│   ├── models/            # Business logic
│   │   ├── UserModel.php
│   │   └── ConfigModel.php
│   └── views/             # HTML templates
│       ├── mainView.php   # Main layout
│       ├── home/          # Homepage views
│       └── user/          # User views
├── core/                  # Framework core (don't modify)
│   ├── Router.php         # URL routing
│   ├── Controller.php     # Base controller
│   ├── Entity.php         # Database ORM
│   └── ...               # Other framework classes
├── entities/              # Database entity classes
├── config/               # Configuration files
├── db/                   # Database migrations
├── public/               # Web-accessible files
│   ├── index.php         # Application entry point
│   └── assets/           # CSS, JS, images
├── routes.php            # Route definitions
└── docker-compose.yml    # Docker configuration
```

## Your First Application

Let's create a simple "Hello World" feature to understand the framework:

### 1. Add a Route

Edit `routes.php` to add a new route:

```php
const ROUTES = [
    'home' => 'home',
    'hello' => 'hello',        // Add this line
    'user' => [
        // ... existing user routes
    ],
    'api' => [
        // ... existing API routes
    ]
];
```

### 2. Create a Controller

Create `app/controllers/hello.php`:

```php
<?php

use PitouFW\Core\Controller;
use PitouFW\Core\Data;

// Handle form submission
if (POST) {
    $name = $_POST['name'] ?? 'World';
    Data::get()->add('greeting', "Hello, " . htmlspecialchars($name) . "!");
}

// Set page title
Data::get()->add('TITLE', 'Hello World Example');

// Render the view
Controller::renderView('hello/hello');
```

### 3. Create a View

Create the directory and view file `app/views/hello/hello.php`:

```php
<div class="container">
    <h1><?= $TITLE ?></h1>
    
    <?php if (isset($greeting)): ?>
        <div class="alert alert-success">
            <?= $greeting ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="mt-4">
        <div class="form-group">
            <label for="name">Enter your name:</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control" 
                   placeholder="Your name" 
                   required>
        </div>
        <button type="submit" class="btn btn-primary">Say Hello</button>
    </form>
</div>
```

### 4. Test Your Application

1. If using Docker: http://localhost:8080/hello
2. If using built-in server: http://localhost:8000/hello

You should see a form where you can enter your name and receive a greeting!

## Development Workflow

### Typical Development Process

1. **Plan your feature** - Decide on routes, data flow, and views needed
2. **Add routes** - Update `routes.php` with new URL patterns
3. **Create controllers** - Handle request logic in `app/controllers/`
4. **Create entities** (if needed) - Database models in `entities/`
5. **Create views** - HTML templates in `app/views/`
6. **Test locally** - Use browser or curl to test functionality
7. **Create migrations** (if database changes) - Use Phinx for schema changes

### Debugging Tips

1. **Check logs:**
   ```bash
   # Docker
   docker compose logs web
   
   # Local development
   tail -f log/error.log
   ```

2. **Database debugging:**
   - Use phpMyAdmin at http://localhost:8081/
   - Or connect directly to MySQL

3. **Enable debug mode:**
   Set `LOGGING=true` in `.env` for detailed error reporting

## Next Steps

Now that you have PitouFW running, explore these topics:

- [Architecture Overview](architecture.md) - Understand the MVC pattern in PitouFW
- [Routing System](routing.md) - Learn advanced routing techniques
- [Database & ORM](database.md) - Work with databases and entities
- [Authentication](authentication.md) - Implement user management
- [Core Classes API](api/README.md) - Detailed API documentation

## Common Issues

### Composer Install Fails
If `composer install` fails due to GitHub rate limits:
```bash
composer install --prefer-source --no-interaction
```

### Docker Services Won't Start
Check if ports are already in use:
```bash
docker compose ps
netstat -tulpn | grep :8080
```

### Database Connection Errors
Verify your database credentials in `.env` and ensure the database exists.

### Permission Issues
Ensure the web server can write to the `log/` and `storage/` directories:
```bash
chmod 755 log/ storage/
```

## Getting Help

- Check the [API documentation](api/README.md) for detailed class information
- Review existing controllers in `app/controllers/` for examples
- Look at the core framework code in `core/` for advanced usage patterns
- Create an issue on GitHub for bugs or feature requests