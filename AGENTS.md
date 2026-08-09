# AGENTS.md

## Repo Brief

Self-hosted recipe management app built with **Symfony 7 / PHP 8.5**, **MySQL**, **Nginx**, **Webpack Encore**, and **Symfony Messenger**.
Recipes are managed through an **EasyAdmin** admin interface and exposed via a versioned **JSON REST API** (`/api/v1`).
AI recipe parsing uses **Anthropic Claude** (vision) to extract structured recipe data from uploaded photos.
Images are stored on **AWS S3** via Flysystem.
The async worker queue uses the Doctrine transport.

Source lives entirely under `app/`.
Docker configuration is at the repo root under `docker/` and `docker-compose.yaml`.

## Read First

- [README.md](README.md)
- [Documentation home](docs/README.md)

## Working Rules

- Preserve existing behaviour unless the user explicitly asks for a functional change.
- Follow the repo's existing patterns before introducing new abstractions.
- Source lives in `app/src/`; templates in `app/templates/`; config in `app/config/`.
- Migrations live in `app/migrations/` — create new ones with `php bin/console make:migration` inside the container; never edit existing migration files.
- Keep documentation updates in sync with meaningful feature, architecture, workflow, command, setup, troubleshooting, or limitation changes.
- Use British English spelling in documentation.

## Commands

All commands run inside the Docker app container unless stated otherwise.

### Safe Checks

```bash
docker compose run --rm app composer run-phpcs      # PHP CodeSniffer lint
docker compose run --rm app composer run-phpmd      # PHPMD mess detection
docker compose run --rm app composer run-phplint    # PHP syntax check
docker compose run --rm app composer run-phpunit    # PHPUnit test suite (requires database_test container)
docker compose run --rm app composer run-tests      # All of the above in sequence
```

### Service Commands

```bash
docker compose up -d --build          # Build images and start all services (app, nginx, database, worker, database_test)
docker compose build app              # Rebuild the app image only
docker compose run --rm app yarn dev  # One-off development asset build (writes public/build/)
docker compose run --rm app yarn watch # Asset watcher (keeps running, writes public/build/ on change)
docker compose run --rm app composer run-coverage  # PHPUnit with HTML coverage report (writes coverage/)
```

### Destructive Or Reset Commands

```bash
docker compose run --rm app yarn build                                   # Production asset build — overwrites public/build/
docker compose run --rm app php bin/console doctrine:migrations:migrate  # Runs pending DB migrations against the configured database
docker compose run --rm app php bin/console make:migration               # Generates a new migration file from entity changes
docker compose down -v                                                   # Stops containers and removes named volumes (drops database data)
```

## Documentation Maintenance

When a significant feature, architecture, workflow, command, setup, troubleshooting, or limitation change is ready for PR, suggest re-running the docs-maintainer skill so `README.md`, `AGENTS.md`, and `docs/` stay in sync.
