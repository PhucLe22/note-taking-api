# Note Taking API

A RESTful API built with **Laravel 11** + **Laravel Sanctum** following clean architecture principles.

## Tech Stack

- Laravel 11
- Laravel Sanctum (authentication)
- MySQL
- PHPUnit (feature tests)

## Architecture

```
Request → Controller → Service → Repository → Model
              ↓
         Policy (authorization)
              ↓
         Resource (response)
```

| Layer | Responsibility |
|---|---|
| Controller | HTTP request/response only |
| Service | Business logic, orchestration |
| Repository | Database queries |
| Policy | Authorization rules |
| Request | Validation |
| Resource | Response transformation |

## Folder Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── NoteController.php
│   │   └── TagController.php
│   ├── Requests/
│   │   ├── Auth/  (RegisterRequest, LoginRequest)
│   │   ├── Note/  (StoreNoteRequest, UpdateNoteRequest)
│   │   └── Tag/   (StoreTagRequest)
│   └── Resources/ (NoteResource, TagResource)
├── Models/         (Note, Tag)
├── Services/       (AuthService, NoteService, TagService)
├── Repositories/
│   ├── Interfaces/ (NoteRepositoryInterface, TagRepositoryInterface)
│   ├── NoteRepository.php
│   └── TagRepository.php
├── Policies/       (NotePolicy)
├── Providers/      (AppServiceProvider)
└── Exceptions/     (Handler)
database/
├── migrations/
├── factories/
└── seeders/        (DatabaseSeeder)
routes/             (api.php)
tests/Feature/      (AuthTest, NoteTest, TagTest)
```

## Getting Started

```bash
# 1. Clone & install
git clone <repo-url>
cd note-taking-api
composer install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Configure database in .env
#    DB_DATABASE=note_taking_api
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Run migrations & seed demo data
php artisan migrate --seed

# 5. Start the server
php artisan serve
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run by file
php artisan test --filter AuthTest
php artisan test --filter NoteTest
php artisan test --filter TagTest
```

## API Endpoints

Base URL: `/api/v1`

### Auth (public — throttle: 10 req/min)
| Method | Endpoint | Description |
|---|---|---|
| POST | `/register` | Register (name, email, password, password_confirmation) |
| POST | `/login` | Login (email, password) → returns token |

### Auth (protected)
| Method | Endpoint | Description |
|---|---|---|
| POST | `/logout` | Revoke current token |

### Notes (protected — throttle: 60 req/min)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/notes` | List notes (paginated, 10/page) |
| POST | `/notes` | Create note (title, content?, tags[]?) |
| GET | `/notes/{id}` | Get note (owner only) |
| PUT | `/notes/{id}` | Update note (owner only) |
| DELETE | `/notes/{id}` | Soft delete note (owner only) |
| PATCH | `/notes/{id}/restore` | Restore deleted note |
| GET | `/notes/search?q=` | Search by title/content (paginated) |

### Tags (protected — throttle: 60 req/min)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/tags` | List user's tags |
| POST | `/tags` | Create tag (name) |
| DELETE | `/tags/{id}` | Delete tag (owner only) |

## Response Format

**Single resource:**
```json
{
  "data": {
    "id": 1,
    "title": "My Note",
    "content": "...",
    "tags": [{ "id": 1, "name": "work" }],
    "created_at": "2024-01-01T00:00:00.000Z"
  },
  "success": true,
  "message": "Success"
}
```

**Collection:**
```json
{
  "data": [...],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "total": 25, "per_page": 10 }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Resource not found."
}
```

## Environment Files

| File | Purpose |
|---|---|
| `.env.example` | Template — committed to git |
| `.env` | Active local config — gitignored |
| `.env.local` | Local overrides with `FRONTEND_URL` |

### CORS & Frontend Connection

Pre-configured for common FE dev servers:
- `localhost:3000` (React/Next.js)
- `localhost:5173` (Vite)

Set `CORS_ALLOWED_ORIGINS` and `SANCTUM_STATEFUL_DOMAINS` in `.env` to match your FE.

## Demo Account

After running `php artisan migrate --seed`:

```
Email:    demo@example.com
Password: password
```

## Git Commit Convention

```
feat: add note search endpoint
fix: note policy authorization
refactor: move tag sync logic to service
test: add note restore test
chore: add index on user_id created_at
```
