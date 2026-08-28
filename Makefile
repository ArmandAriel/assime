ifneq (,$(wildcard Docker\.env))
include Docker\.env
export
endif

APP_NAME ?= app_db
COMPOSE ?= docker compose --env-file .env -f Docker/docker-compose.yaml
APP_SERVICE ?= app
DB_SERVICE ?= database
CMD ?= about
BUNDLE ?=

.PHONY: help build up down restart ps logs app-shell db-shell composer console about cache-clear routes db-list lint-yaml cs-check cs-fix phpstan bundle

help:
	@echo "Available targets:"
	@echo "  make build                  Build Docker images"
	@echo "  make up                     Start containers"
	@echo "  make down                   Stop containers"
	@echo "  make restart                Restart containers"
	@echo "  make ps                     Show containers"
	@echo "  make logs                   Tail container logs"
	@echo "  make app-shell              Open a shell in the app container"
	@echo "  make db-shell               Open psql on $(APP_NAME)"
	@echo "  make composer CMD='install' Run a Composer command"
	@echo "  make console CMD='about'    Run a Symfony console command"
	@echo "  make about                  Show Symfony app info"
	@echo "  make cache-clear            Clear Symfony cache"
	@echo "  make routes                 List registered routes"
	@echo "  make db-list                List PostgreSQL databases"
	@echo "  make lint-yaml              Lint Symfony YAML config files"
	@echo "  make cs-check               Check PHP coding style with PHP CS Fixer"
	@echo "  make cs-fix                 Fix PHP coding style with PHP CS Fixer"
	@echo "  make phpstan                Run PHPStan static analysis"
	@echo "  make migration              Run php bin/console make:migration"
	àecho "  make migrate               Run All doctrine commands"
	@echo "  make bundle BUNDLE='vendor/package' Install a Symfony bundle with Composer"

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart: down up

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

app-shell:
	$(COMPOSE) exec $(APP_SERVICE) sh

db-shell:
	$(COMPOSE) exec $(DB_SERVICE) psql -U symfony_user -d $(APP_NAME)

composer:
	$(COMPOSE) exec $(APP_SERVICE) composer $(CMD)

console:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console $(CMD)

about:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console about

cache-clear:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console cache:clear

routes:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console debug:router

db-list:
	$(COMPOSE) exec $(DB_SERVICE) psql -U symfony_user -d postgres -tAc "SELECT datname FROM pg_database WHERE datistemplate = false ORDER BY datname;"

lint-yaml:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console lint:yaml config

cs-check:
	$(COMPOSE) exec $(APP_SERVICE) php vendor/bin/php-cs-fixer fix --dry-run --diff --verbose

cs-fix:
	$(COMPOSE) exec $(APP_SERVICE) php vendor/bin/php-cs-fixer fix --verbose

phpstan:
	$(COMPOSE) exec $(APP_SERVICE) composer phpstan

bundle:
	@if [ -z "$(BUNDLE)" ]; then echo "Usage: make bundle BUNDLE='vendor/package'"; exit 1; fi
	$(COMPOSE) exec $(APP_SERVICE) composer require $(BUNDLE)

migration:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console make:migration

migrate:
	$(COMPOSE) exec $(APP_SERVICE) php bin/console doctrine:migrations:migrate
