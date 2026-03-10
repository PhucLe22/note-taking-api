# Note Taking API — Project Rules

## Architecture

- **Framework:** Laravel 12 (API-only)
- **Auth:** Laravel Sanctum (token-based)
- **Database:** PostgreSQL (Neon)
- **Pattern:** Controller → Service → Repository → Model

## Project Structure

```
app/
├── Http/Controllers/Api/   # API controllers (Auth, Note, Tag)
├── Http/Requests/          # Form request validation
├── Http/Resources/         # API resource transformers
├── Models/                 # Eloquent models (User, Note, Tag)
├── Policies/               # Authorization policies
├── Providers/              # Service providers (repository bindings)
├── Repositories/           # Data access layer (with interfaces)
├── Services/               # Business logic layer
bootstrap/app.php           # App bootstrap + exception handling
routes/api.php              # All API routes under /api/v1
config/cors.php             # CORS config (reads from .env)
```

## Conventions

- All API endpoints are prefixed with `/api/v1`
- Responses follow: `{ data, success, message }` for single items; `{ data, links, meta }` for paginated collections
- Use Form Requests for validation, Policies for authorization
- Repository interfaces are bound in `AppServiceProvider`
- Models use `HasFactory` trait for testing
- Notes support soft deletes with restore

## Frontend Integration

### Environment-Based Configuration (never hardcode URLs)

The backend is configured via `.env` to accept frontend connections. When creating a frontend project:

1. **Backend `.env` variables that control frontend access:**
   ```
   SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
   CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173
   CORS_SUPPORTS_CREDENTIALS=true
   ```

2. **Frontend `.env` should define:**
   ```
   VITE_API_BASE_URL=http://localhost:8000/api/v1
   ```
   Never hardcode `http://localhost:8000` in source files. Always read from env.

3. **Auth flow (token-based):**
   - `POST /api/v1/register` → returns `{ token }` → store in localStorage/cookie
   - `POST /api/v1/login` → returns `{ token }` → store in localStorage/cookie
   - All subsequent requests: `Authorization: Bearer <token>`
   - `POST /api/v1/logout` → clears token server-side

4. **CORS:** Configured in `config/cors.php`, reads all values from `.env`. To add a new frontend origin, update `CORS_ALLOWED_ORIGINS` and `SANCTUM_STATEFUL_DOMAINS` in `.env` — no code changes needed.

5. **Adding a new frontend domain (e.g., production):**
   ```
   SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,app.example.com
   CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173,https://app.example.com
   ```

## Commands

- `make setup` — full setup (install, migrate, seed)
- `make serve` — start dev server (port 8000)
- `make test` — run tests
- `make fresh` — reset database
- `php artisan test` — run PHPUnit tests (SQLite in-memory)

## Testing

- Tests use SQLite in-memory (configured in `phpunit.xml`)
- Feature tests in `tests/Feature/` (Auth, Note, Tag)
- Demo account after seeding: `demo@example.com` / `password`
- All 30 tests should pass before pushing changes

## Rules

- Never hardcode URLs, database credentials, or API keys — always use `.env`
- Never commit `.env` files (they contain secrets)
- Always run `php artisan test` before pushing
- Keep CORS origins in `.env`, not in `config/cors.php`
- Tag ownership is validated at the request level (tags.* must belong to the authenticated user)
