# Database & ORM

PitouFW provides a lightweight ORM (Object-Relational Mapping) system built on top of PDO for MySQL. This document covers database configuration, entity management, migrations, and advanced database operations.

## Overview

The database system in PitouFW consists of:

- **DB Class** - PDO-based database connection management
- **Entity System** - Active Record pattern for database operations
- **Migrations** - Database schema management with Phinx
- **Query Builder** - Fluent interface for complex queries

### Key Features

- **PDO-based** - Secure, prepared statements by default
- **Active Record Pattern** - Entities map to database tables
- **Automatic CRUD** - Create, Read, Update, Delete operations
- **Migration Support** - Version-controlled schema changes
- **Type Safety** - Strong typing with PHP 8.3 features

## Database Configuration

### Environment Setup

Configure your database connection in the `.env` file:

```env
# Database Configuration
DB_HOST=localhost          # Database host
DB_NAME=pitoufw           # Database name
DB_USER=your_username     # Database username
DB_PASS=your_password     # Database password
```

For Docker development:
```env
DB_HOST=db                # Docker service name
DB_NAME=pitoufw
DB_USER=docker
DB_PASS=secret
```

### Database Connection

The `DB` class manages the database connection using the singleton pattern:

```php
use PitouFW\Core\DB;

// Get database connection (PDO instance)
$pdo = DB::get();

// Execute raw queries
$stmt = $pdo->prepare("SELECT * FROM users WHERE active = ?");
$stmt->execute([1]);
$results = $stmt->fetchAll();
```

### Connection Features

- **UTF-8 Support** - Automatic UTF-8 character set configuration
- **Error Handling** - Graceful connection error management
- **Single Connection** - One connection per request for efficiency
- **Prepared Statements** - All queries use prepared statements

## Entity System

### Entity Base Class

All database entities extend the `Entity` base class:

```php
use PitouFW\Core\Entity;

class Product extends Entity {
    protected string $name = '';
    protected float $price = 0.0;
    protected int $category_id = 0;
    
    public static function getTableName(): string {
        return 'products';
    }
    
    // Getters and setters...
}
```

### Entity Properties

- **Protected Properties** - Map to database columns
- **Type Declarations** - PHP 8.3 type safety
- **Default Values** - Initialize with sensible defaults
- **Naming Convention** - Snake_case for database, camelCase for methods

### Required Methods

Every entity must implement:

```php
public static function getTableName(): string {
    return 'table_name';  // Database table name
}
```

## CRUD Operations

### Creating Records

```php
// Create new entity
$user = new User();
$user->setEmail('user@example.com')
     ->setPasswd(password_hash('password', PASSWORD_DEFAULT))
     ->setAdmin(0)
     ->save();

// Get the auto-generated ID
$userId = $user->getId();
```

### Reading Records

```php
// Find by ID
$user = User::findOne('id', 123);

// Find by email
$user = User::findOne('email', 'user@example.com');

// Find all records
$allUsers = User::findAll();

// Find with conditions
$activeUsers = User::findAll('active = 1');

// Check if record exists
if (User::exists('email', 'test@example.com')) {
    // User exists
}
```

### Updating Records

```php
// Load existing record
$user = User::findOne('id', 123);

// Modify properties
$user->setEmail('newemail@example.com')
     ->setAdmin(1);

// Save changes
$user->save();
```

### Deleting Records

```php
// Delete by entity
$user = User::findOne('id', 123);
$user->delete();

// Delete by criteria
User::deleteWhere('active = 0');
```

## Entity Methods

### Core Methods

```php
class Entity {
    // Persistence
    public function save(): self;                    // Insert or update
    public function delete(): bool;                  // Delete record
    
    // Finding
    public static function findOne(string $column, $value): ?static;
    public static function findAll(?string $condition = null): array;
    public static function exists(string $column, $value): bool;
    
    // Utility
    public function getId(): int;                    // Get primary key
    public function setId(int $id): self;           // Set primary key
    public static function getTableName(): string;  // Table name (abstract)
}
```

### Example Entity Implementation

```php
<?php

namespace PitouFW\Entity;

use PitouFW\Core\Entity;

class BlogPost extends Entity {
    protected string $title = '';
    protected string $content = '';
    protected string $slug = '';
    protected int $author_id = 0;
    protected int $published = 0;
    protected ?string $created_at = null;
    protected ?string $updated_at = null;
    
    public static function getTableName(): string {
        return 'blog_posts';
    }
    
    // Title
    public function getTitle(): string {
        return $this->title;
    }
    
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }
    
    // Content
    public function getContent(): string {
        return $this->content;
    }
    
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }
    
    // Slug
    public function getSlug(): string {
        return $this->slug;
    }
    
    public function setSlug(string $slug): self {
        $this->slug = $slug;
        return $this;
    }
    
    // Author ID
    public function getAuthorId(): int {
        return $this->author_id;
    }
    
    public function setAuthorId(int $authorId): self {
        $this->author_id = $authorId;
        return $this;
    }
    
    // Published status
    public function isPublished(): bool {
        return (bool) $this->published;
    }
    
    public function setPublished(bool $published): self {
        $this->published = $published ? 1 : 0;
        return $this;
    }
    
    // Timestamps
    public function getCreatedAt(): ?string {
        return $this->created_at;
    }
    
    public function setCreatedAt(?string $createdAt): self {
        $this->created_at = $createdAt;
        return $this;
    }
    
    // Custom methods
    public function generateSlug(): self {
        $this->slug = Utils::slugify($this->title);
        return $this;
    }
    
    public function getAuthor(): ?User {
        return User::findOne('id', $this->author_id);
    }
}
```

## Advanced Queries

### Raw Queries

For complex queries, use the DB class directly:

```php
use PitouFW\Core\DB;

// Complex SELECT with joins
$sql = "
    SELECT u.*, COUNT(p.id) as post_count 
    FROM users u 
    LEFT JOIN blog_posts p ON u.id = p.author_id 
    WHERE u.active = ? 
    GROUP BY u.id 
    ORDER BY post_count DESC
";

$stmt = DB::get()->prepare($sql);
$stmt->execute([1]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Custom Entity Methods

Add custom query methods to your entities:

```php
class User extends Entity {
    // Find active users
    public static function findActive(): array {
        return self::findAll('active = 1');
    }
    
    // Find by email domain
    public static function findByDomain(string $domain): array {
        $sql = "SELECT * FROM " . self::getTableName() . " WHERE email LIKE ?";
        $stmt = DB::get()->prepare($sql);
        $stmt->execute(['%@' . $domain]);
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = self::getFilledObject($row);
        }
        
        return $results;
    }
    
    // Count posts by user
    public function getPostCount(): int {
        $sql = "SELECT COUNT(*) FROM blog_posts WHERE author_id = ?";
        $stmt = DB::get()->prepare($sql);
        $stmt->execute([$this->getId()]);
        return (int) $stmt->fetchColumn();
    }
}
```

## Database Migrations

PitouFW uses Phinx for database migrations, providing version control for your database schema.

### Migration Configuration

Migrations are configured in `phinx.php`:

```php
return [
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds'
    ],
    'environments' => [
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'charset' => 'utf8'
        ]
    ]
];
```

### Creating Migrations

Create a new migration:

```bash
php vendor/bin/phinx create CreateBlogPostsTable
```

This creates a migration file in `db/migrations/`:

```php
<?php

use Phinx\Migration\AbstractMigration;

class CreateBlogPostsTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('blog_posts');
        $table->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('content', 'text')
              ->addColumn('slug', 'string', ['limit' => 255])
              ->addColumn('author_id', 'integer')
              ->addColumn('published', 'boolean', ['default' => false])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', [
                  'default' => 'CURRENT_TIMESTAMP',
                  'update' => 'CURRENT_TIMESTAMP'
              ])
              ->addIndex(['slug'], ['unique' => true])
              ->addIndex(['author_id'])
              ->addForeignKey('author_id', 'users', 'id', [
                  'delete' => 'CASCADE',
                  'update' => 'NO_ACTION'
              ])
              ->create();
    }

    public function down(): void
    {
        $this->table('blog_posts')->drop()->save();
    }
}
```

### Running Migrations

```bash
# Run all pending migrations
php vendor/bin/phinx migrate

# Run migrations in Docker
docker compose exec web php vendor/bin/phinx migrate

# Rollback last migration
php vendor/bin/phinx rollback

# Check migration status
php vendor/bin/phinx status
```

### Migration Best Practices

1. **Always provide rollback** - Implement the `down()` method
2. **Use descriptive names** - Clear migration class names
3. **One feature per migration** - Keep migrations focused
4. **Test rollbacks** - Ensure `down()` methods work correctly
5. **Backup before major changes** - Always backup production data

## Data Seeding

### Creating Seeds

Create database seeds for test data:

```bash
php vendor/bin/phinx seed:create UserSeeder
```

```php
<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'email' => 'admin@example.com',
                'passwd' => password_hash('admin123', PASSWORD_DEFAULT),
                'admin' => 1,
                'activated_at' => date('Y-m-d H:i:s')
            ],
            [
                'email' => 'user@example.com',
                'passwd' => password_hash('user123', PASSWORD_DEFAULT),
                'admin' => 0,
                'activated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->table('users')
             ->insert($data)
             ->save();
    }
}
```

### Running Seeds

```bash
# Run all seeds
php vendor/bin/phinx seed:run

# Run specific seed
php vendor/bin/phinx seed:run -s UserSeeder
```

## Relationships

While PitouFW doesn't have built-in relationship management, you can implement relationships manually:

### One-to-Many Relationship

```php
class User extends Entity {
    // Get user's blog posts
    public function getPosts(): array {
        return BlogPost::findAll('author_id = ' . $this->getId());
    }
}

class BlogPost extends Entity {
    // Get post author
    public function getAuthor(): ?User {
        return User::findOne('id', $this->author_id);
    }
}
```

### Many-to-Many Relationship

```php
class User extends Entity {
    // Get user's roles through user_roles junction table
    public function getRoles(): array {
        $sql = "
            SELECT r.* FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = ?
        ";
        $stmt = DB::get()->prepare($sql);
        $stmt->execute([$this->getId()]);
        
        $roles = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = Role::getFilledObject($row);
        }
        
        return $roles;
    }
}
```

## Performance Optimization

### Query Optimization

1. **Use indexes** - Add database indexes for frequently queried columns
2. **Limit results** - Use LIMIT in queries when appropriate
3. **Avoid N+1 queries** - Load related data efficiently
4. **Use specific columns** - SELECT only needed columns

### Caching

Implement caching for frequently accessed data:

```php
use PitouFW\Core\Redis;

class User extends Entity {
    public static function findOneWithCache(string $column, $value): ?self {
        $cacheKey = "user:{$column}:{$value}";
        
        // Try cache first
        if (Redis::get()->exists($cacheKey)) {
            $data = unserialize(Redis::get()->get($cacheKey));
            return self::getFilledObject($data);
        }
        
        // Load from database
        $user = self::findOne($column, $value);
        if ($user) {
            // Cache for 1 hour
            Redis::get()->setex($cacheKey, 3600, serialize($user->toArray()));
        }
        
        return $user;
    }
}
```

## Security Considerations

### SQL Injection Prevention

PitouFW automatically uses prepared statements:

```php
// Safe - uses prepared statements
$user = User::findOne('email', $email);

// Safe - PDO prepared statement
$stmt = DB::get()->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### Data Validation

Implement validation in entity setters:

```php
class User extends Entity {
    public function setEmail(string $email): self {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        $this->email = $email;
        return $this;
    }
    
    public function setPasswd(string $password): self {
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }
        
        // Hash password automatically
        $this->passwd = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }
}
```

### Input Sanitization

Always sanitize input data:

```php
// In controllers
$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Handle invalid email
}
```

## Troubleshooting

### Common Issues

1. **Connection errors**
   - Check database credentials in `.env`
   - Ensure database server is running
   - Verify network connectivity

2. **Migration failures**
   - Check SQL syntax in migration files
   - Ensure database user has sufficient permissions
   - Review migration dependencies

3. **Entity not saving**
   - Verify table name matches `getTableName()` return value
   - Check property names match database columns
   - Ensure required fields are set

4. **Query errors**
   - Enable error reporting with `PDO::ATTR_ERRMODE`
   - Use `var_dump($stmt->errorInfo())` for debugging
   - Check SQL syntax and parameter binding

### Debugging Database Issues

Enable detailed database error reporting:

```php
// In development, add to your entity save method:
private function create(bool $show_errors = true): int {
    // ... existing code ...
    
    $req = DB::get()->prepare("INSERT INTO $table_name ($columns) VALUES ($qms)");
    $req->execute($values);
    
    if ($show_errors) {
        var_dump($req->errorInfo());
    }
    
    return DB::get()->lastInsertId();
}
```

## Best Practices

### Entity Design

1. **Single responsibility** - One entity per database table
2. **Meaningful names** - Use descriptive property and method names
3. **Type safety** - Use PHP 8.3 type declarations
4. **Validation** - Implement validation in setters
5. **Documentation** - Add PHPDoc comments for complex methods

### Database Design

1. **Normalization** - Follow database normalization principles
2. **Indexes** - Add indexes for foreign keys and frequent queries
3. **Constraints** - Use foreign key constraints for data integrity
4. **Naming conventions** - Consistent table and column naming

### Performance

1. **Lazy loading** - Load related data only when needed
2. **Query optimization** - Use EXPLAIN to analyze query performance
3. **Connection pooling** - Consider connection pooling for high-traffic applications
4. **Caching strategy** - Implement appropriate caching for read-heavy operations

### Security

1. **Input validation** - Always validate and sanitize input
2. **Prepared statements** - Never use string concatenation for queries
3. **Least privilege** - Use database users with minimal required permissions
4. **Regular updates** - Keep database software updated