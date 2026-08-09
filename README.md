# Cookbook

A personal recipe management application built with Symfony 7 and PHP 8.5.
Store, organise, and browse recipes with ingredients, steps, components, and images.
An EasyAdmin-powered admin interface makes it easy to manage content, and an AI image-parsing feature lets you photograph a cookbook page and have Claude extract the recipe automatically.

## Purpose

A self-hosted recipe store and meal-planning tool.
Recipes are served via a JSON API so they can be consumed by other applications such as a meal-planner UI.

## Local Development

### Prerequisites

- Docker and Docker Compose

### Setup

1. Copy the root environment file and set the port the app will run on:

   ```bash
   cp .env.example .env
   ```

2. Build and start the services:

   ```bash
   docker compose up -d --build
   ```

   On first run the entrypoint waits for the database and runs migrations automatically.

3. Open [http://localhost:10000](http://localhost:10000) in your browser (or whichever port you chose in `.env`).

### Running checks

All checks run inside the app container. Start the stack first.

```bash
docker compose run --rm app composer run-tests
```

This runs PHP CodeSniffer, PHPUnit, PHPMD, and PHPLint in sequence.

Individual checks:

```bash
docker compose run --rm app composer run-phpcs
docker compose run --rm app composer run-phpunit
docker compose run --rm app composer run-phpmd
docker compose run --rm app composer run-phplint
```

### Frontend assets

Assets are pre-compiled and committed. To rebuild during development:

```bash
docker compose run --rm app yarn dev          # one-off dev build
docker compose run --rm app yarn watch        # watch mode
docker compose run --rm app yarn build        # production build (writes compiled assets)
```

### Environment variables

Copy `.env.example` to `.env` and fill in the values:

| Variable | Required | Description |
|---|---|---|
| `APP_SECRET` | Yes | Random 32-byte hex string — generate with `openssl rand -hex 32` |
| `DATABASE_URL` | Yes | MySQL connection string |
| `ANTHROPIC_API_KEY` | Yes* | API key for Claude — required if using AI image parsing |
| `MESSENGER_TRANSPORT_DSN` | Yes | Defaults to `doctrine://default?auto_setup=1` (queue stored in DB) |
| `AWS_S3_KEY` | Yes | AWS access key ID |
| `AWS_S3_SECRET` | Yes | AWS secret access key |
| `AWS_S3_REGION` | Yes | S3 bucket region (e.g. `eu-west-2`) |
| `AWS_S3_BUCKET` | Yes | S3 bucket name |

\* AI parsing fails at runtime if omitted, but the rest of the app works without it.

### Deployment

The production image is built from `docker/php/Dockerfile.prod` — it bundles PHP-FPM, Nginx, and compiled assets into a single image.
Two containers are needed, both using the same image tag:

**App container**
- Image: `ghcr.io/aussieveen/cookbook:TAG`
- Port: `80` mapped to your host port
- `CONTAINER_ROLE=app` (or omit — defaults to app)
- All environment variables from the table above
- Restart policy: `unless-stopped`

**Worker container**
- Image: `ghcr.io/aussieveen/cookbook:TAG`
- No port mapping
- `CONTAINER_ROLE=worker`
- All environment variables from the table above
- Restart policy: `unless-stopped`

The worker exits cleanly after one hour to release memory and stale connections.
The Docker restart policy relaunches it immediately — no cron or external supervisor needed.

## Documentation

- [Documentation home](docs/README.md)
- [Features](docs/features/README.md)

## Useful Links

- [Admin interface](http://localhost:10000/admin) (local)
- [OpenAPI docs](http://localhost:10000/api/doc) (local)
- [GitHub Container Registry](https://ghcr.io/aussieveen/cookbook)
