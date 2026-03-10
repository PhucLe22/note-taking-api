.PHONY: setup install key migrate seed serve test test-auth test-note test-tag fresh refresh db-reset help

# ─── Setup (first time) ───────────────────────────────
setup: install key migrate seed ## Full first-time setup

install: ## Install dependencies
	composer install

key: ## Generate app key
	php artisan key:generate

# ─── Database ─────────────────────────────────────────
migrate: ## Run migrations
	php artisan migrate

seed: ## Seed demo data
	php artisan db:seed

fresh: ## Drop all tables & re-migrate & seed
	php artisan migrate:fresh --seed

refresh: ## Rollback & re-migrate & seed
	php artisan migrate:refresh --seed

# ─── Server ───────────────────────────────────────────
serve: ## Start dev server at localhost:8000
	php artisan serve

# ─── Tests ────────────────────────────────────────────
test: ## Run all tests
	php artisan test

test-auth: ## Run auth tests
	php artisan test --filter AuthTest

test-note: ## Run note tests
	php artisan test --filter NoteTest

test-tag: ## Run tag tests
	php artisan test --filter TagTest

# ─── Utilities ────────────────────────────────────────
cache-clear: ## Clear all caches
	php artisan config:clear && php artisan cache:clear && php artisan route:clear

routes: ## List all routes
	php artisan route:list --path=api

# ─── Help ─────────────────────────────────────────────
help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'
