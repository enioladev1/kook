<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/enioladev1/kook/main/public/branding/logo.png">
    <img alt="Kook" src="https://raw.githubusercontent.com/enioladev1/kook/main/public/branding/logo-light.png" height="72">
  </picture>
</p>

<p align="center">
  A self-hosted, open-source webhook infrastructure platform: receive, verify, and reliably deliver webhooks on your own infrastructure.
</p>

---

Kook receives webhooks on your behalf, verifies provider signatures, forwards them to your application, and retries failed deliveries - all running on infrastructure you control, not a third-party SaaS.

## Features

- **Transparent relay mode** - forward incoming webhooks to your application byte-for-byte, original payload and headers preserved.
- **Managed verification mode** - pick a provider, drop in its webhook secret, and Kook verifies the signature before forwarding to your application.
- **Built-in provider support** - Stripe, Paystack, Flutterwave, GitHub, Shopify, and a configurable generic HMAC provider for anything else.
- **Reliable delivery** - failed deliveries retry on an exponential backoff schedule (30s, 2m, 10m, 30m, 1h) with a full per-attempt history, and exhausted retries land in Laravel's `failed_jobs` table.
- **Idempotent ingestion** - duplicate deliveries of the same event (matched by provider event id) are deduplicated instead of reprocessed.
- **Replay** - re-send any successfully verified event on demand.
- **Projects, webhook endpoints, and API keys** - organize webhook endpoints by project, and grant scoped programmatic access via API keys.
- **Append-only audit logs** - every security-relevant action is recorded and cannot be edited or deleted, enforced at both the application and database layer.
- **Configurable outgoing email** - Resend, Postmark, SendByte, or plain SMTP, set up from the dashboard.
- **A small JSON API** authenticated by API key, for listing endpoints/events and triggering replays programmatically.



## Tech stack

- **Backend:** Laravel 13, PHP 8.4, PostgreSQL, Redis (queues, cache, sessions, rate limiting)
- **Frontend:** React, TypeScript, Inertia.js, Tailwind CSS, [HugeIcons](https://hugeicons.com) (free set)
- **Testing:** Pest, Larastan (PHPStan), Pint, ESLint, Prettier



## Self-hosting

The fastest way to run Kook is the prebuilt image - no PHP/Node toolchain, no build step, just Docker.

### Option A: Docker Compose (app + Postgres + Redis together)

```bash
curl -O https://raw.githubusercontent.com/enioladev1/kook/main/docker-compose.yml
curl -o .env.docker https://raw.githubusercontent.com/enioladev1/kook/main/.env.docker.example
```

Fill in the four required values in `.env.docker` (everything else has a sensible production default):

```bash
APP_KEY=            # generate with: docker run --rm --entrypoint php ghcr.io/enioladev1/kook artisan key:generate --show OR run "php artisan key:generate --show" locally
APP_URL=             # your real domain, e.g. https://kook.example.com
DB_PASSWORD=         # a strong password
CORS_ALLOWED_ORIGINS= # must match APP_URL's origin
```

```bash
docker compose --env-file .env.docker up -d
```

That's it - `docker-compose.yml` pulls `ghcr.io/enioladev1/kook:latest` (rebuilt automatically on every push to `main`), runs migrations on every boot (idempotent - already-applied ones are skipped), and starts Postgres and Redis alongside it with persistent volumes.

### Option B: Dokploy

Kook was built with [Dokploy](https://dokploy.com) in mind:

- **Compose deploy** - point a Dokploy "Compose" resource at this repo's `docker-compose.yml`, paste the four required variables into Dokploy's environment panel, deploy. Postgres and Redis are provisioned as part of the same stack.
- **Application + managed databases** - alternatively, deploy `Dockerfile` alone as a Dokploy "Application" (nginx, php-fpm, and the queue worker all run in one container, supervised - see `docker/supervisord.conf`), and point it at separately-provisioned Dokploy Postgres/Redis services via `DB_HOST`/`REDIS_HOST`.

Either way, the app container publishes an internal healthcheck on `/up` - point Dokploy's domain/port config at **80**. The container still runs as a non-root user throughout; nginx is granted the specific Linux capability needed to bind port 80 (`cap_net_bind_service`) rather than running as root.

### Building the image yourself

Don't want to depend on the prebuilt image? `docker-compose.yml` only has `image:`, not `build:` - deliberately, so it can be deployed as this one file alone with no source code alongside it (some deploy tools always pass `--build`, which would otherwise force a failed build attempt with no `Dockerfile` present). If you've cloned the full repo and want to build from source instead, add `build: .` next to `image:` in the `app` service, then run `docker compose --env-file .env.docker up -d --build`.

## Local development

Local dev runs the app natively (`php artisan serve` / `npm run dev`) for instant reloads, with just Postgres and Redis as backing services - `docker-compose.yml` is oriented at full self-hosted deployments and deliberately doesn't publish their ports to the host, so use plain `docker run` for local dev instead:

```bash
docker run -d --name kook-postgres -p 5432:5432 \
  -e POSTGRES_DB=kook -e POSTGRES_USER=kook -e POSTGRES_PASSWORD=kook \
  postgres:17-alpine

docker run -d --name kook-redis -p 6379:6379 redis:7-alpine
```

(Already have Postgres/Redis installed natively? Skip this and just point `.env` at them.)

### Install and run

```bash
git clone https://github.com/enioladev1/kook.git
cd kook

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
npm run build   # or `npm run dev` for hot reloading

php artisan serve
```

In a **second terminal**, run the queue worker - required, not optional, since webhook verification and delivery happen entirely in the background:

```bash
php artisan queue:work
```

Visit `http://localhost:8000`, register an account, then create a project and a webhook endpoint to get its ingest URL.

## Security notes

- Passwords are hashed with Laravel's defaults; sessions are Redis-backed, `HttpOnly`, and `SameSite=Lax`.
- CORS has no wildcard origin - configure `CORS_ALLOWED_ORIGINS` explicitly (`config/cors.php`).
- Every resource access is checked against the requester's ownership chain (see `app/Policies`); cross-tenant lookups return 404, not 403, to avoid confirming a resource exists.
- Webhook destination URLs are validated to reject private/reserved network addresses (`app/Rules/PublicHttpUrl.php`), preventing the delivery feature from being used for SSRF against your own infrastructure.
- Provider secrets, the platform's outgoing signing secret, and outgoing email credentials are stored with Laravel's `encrypted` cast; API keys store only a SHA-256 hash, the plaintext is shown once at creation and never persisted.
- Audit logs are append-only at two layers: a Postgres trigger rejects `UPDATE`/`DELETE` even for the owning role, and the `AuditLog` model throws before a query is even attempted.
- Rate limiting is applied to authentication, webhook ingestion (per endpoint), replay actions, and the JSON API (per key).
- The Docker image runs as a non-root user throughout; `.env`, `storage/`, and application source are never inside the web server's document root (`public/` only).



## License

MIT