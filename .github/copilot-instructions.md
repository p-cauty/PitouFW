# PitouFW Development Instructions

PitouFW is a personal PHP 8.3 framework with MVC architecture built by Peter Cauty. It uses Docker with MySQL 8.0, Redis 7.0, and phpMyAdmin for development and provides GitHub Actions for automated build/deploy workflows.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Prerequisites
- PHP 8.3+ with extensions: curl, iconv, imap, json, openssl, pdo_mysql, redis, zip
- Docker with Compose v2 (`docker compose` command, not `docker-compose`)
- Composer

### Bootstrap and Build Process
1. **Install Dependencies**:
   ```bash
   composer install --prefer-dist
   ```
   - Takes ~45 seconds to complete. NEVER CANCEL. Set timeout to 90+ seconds.
   - May fall back to source installs due to GitHub API rate limits - this is normal.
   - If prompted for GitHub token, you can skip by pressing Enter to use source installs.

2. **Setup Environment**:
   ```bash
   cp example.env .env
   ```
   - The .env file is pre-configured for Docker development.
   - Database credentials: user=docker, password=secret, database=pitoufw.

3. **Start Docker Services**:
   ```bash
   docker compose up -d
   ```
   - Takes ~7 seconds for startup after initial build. NEVER CANCEL. Set timeout to 120+ seconds.
   - First-time build takes ~2 minutes. NEVER CANCEL. Set timeout to 300+ seconds.
   - Redis PHP extension installation fails in Docker but this doesn't prevent the application from running.

4. **Setup Database**:
   ```bash
   # Create database and grant permissions (required only once)
   docker compose exec db mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS pitoufw; GRANT ALL PRIVILEGES ON pitoufw.* TO 'docker'@'%'; FLUSH PRIVILEGES;"
   
   # Run migrations
   docker compose exec web php vendor/bin/phinx migrate
   ```
   - Database setup is required only once. Migration takes ~1 second. NEVER CANCEL. Set timeout to 30+ seconds.

### Running the Application

**Docker Development (Recommended)**:
- Web Application: http://localhost:8080/
- phpMyAdmin: http://localhost:8081/ (user: docker, password: secret)
- Redis cache available at redis://cache:6379 (within Docker network)

**Alternative PHP Built-in Server** (without database):
```bash
php -S localhost:8000 -t public
```
- Available at http://localhost:8000/
- Use for quick testing without Docker overhead.

### Testing and Validation
- **No formal testing framework** exists in this repository.
- **No linting tools** are configured.
- **Manual testing approach**: Always test these scenarios after changes:
  1. Home page loads: `curl http://localhost:8080/`
  2. User registration page: `curl http://localhost:8080/user/register`
  3. Database connectivity works via web interface
  4. Docker services are healthy: `docker compose ps`

### Build and Deploy Workflows
- **Build**: Automated via `.github/workflows/build.yml` on releases
- **Deploy**: Automated via `.github/workflows/deploy.yml` with manual trigger
- **Build Artifacts**: Created in `release.tar.gz` excluding development files
- **Deploy Process**: Uses SSH to copy and extract builds to production server

## Common Tasks and Important Information

### Known Issues and Limitations
- **API Version Endpoint** (`/api/version`) fails in development due to missing `DEPLOYED_REF` and `DEPLOYED_COMMIT` constants (only available in built releases).
- **Redis Extension** cannot be installed in Docker environment but this doesn't break functionality.
- **Database Permissions** must be manually set up on first run using root MySQL user.

### Configuration Details
- **Environment Config**: All settings in `.env` file based on `example.env`
- **Docker Ports**: 
  - App: 8080
  - Database: 3307 (external), 3306 (internal)
  - phpMyAdmin: 8081
- **Database**: MySQL 8.0 with credentials docker/secret for development

### File Structure Overview
```
/
├── app/             # MVC application code
│   ├── controllers/ # Route handlers
│   ├── models/      # Database models
│   └── views/       # View templates
├── core/            # Framework core classes
├── public/          # Web root with assets
├── config/          # Configuration files
├── db/              # Database migrations and seeds
├── .docker/         # Docker configuration
└── vendor/          # Composer dependencies
```

### Development Workflow
1. Always run `docker compose up -d` before starting development
2. Make code changes in appropriate directories (app/, core/, public/)
3. Test changes via web browser at http://localhost:8080/
4. Check logs with `docker compose logs web` if issues occur
5. Use phpMyAdmin at http://localhost:8081/ for database inspection

### Production Considerations
- Framework designed for production deployment via SSH
- Build process removes development files (.docker/, .git/, composer files)
- Uses Phinx for database migrations in production
- Deployment preserves storage/ directory and .env configuration

### Troubleshooting
- **Services won't start**: Check `docker compose ps` and `docker compose logs`
- **Database connection fails**: Ensure database permissions are set correctly
- **Composer install fails**: Use `--no-interaction` flag or provide GitHub token
- **Application returns 500 errors**: Check PHP error logs in Docker: `docker compose logs web`

## Command Reference

### Frequently Used Commands
```bash
# Development setup
composer install --prefer-dist
cp example.env .env
docker compose up -d

# Database operations  
docker compose exec web php vendor/bin/phinx migrate
docker compose exec db mysql -u root -proot -e "SHOW DATABASES;"

# Service management
docker compose ps
docker compose logs web
docker compose down
docker compose restart web

# Alternative development
php -S localhost:8000 -t public
```

### Timing Expectations
- Composer install: 45 seconds (NEVER CANCEL, timeout: 90+ seconds)
- Docker startup: 7 seconds (NEVER CANCEL, timeout: 120+ seconds)  
- Docker build: 2 minutes first time (NEVER CANCEL, timeout: 300+ seconds)
- Database migration: 1 second (NEVER CANCEL, timeout: 30+ seconds)