# Copilot Instructions

## Project Shape

This repository is a World of Warcraft guild management app: a React single-page
app talking to a .NET 10 minimal API backed by Neon Postgres. It was migrated
from an older PHP page app (the PHP backend and its MySQL database have been
fully replaced).

- `frontend/` is the React/Vite/TypeScript/MUI single-page app.
- `backend/` is the .NET 10 minimal API (assembly/namespace `AmusedToDeath.Api`).
  - `backend/Endpoints/` — one file per route group, each a `MapXxxEndpoints`
    extension registered group-by-group in `Program.cs`.
  - `backend/Data/` — Dapper repositories and the Npgsql connection factory.
  - `backend/Models/` — request/response DTOs.
  - `backend/Security/` — session service, current-user accessor, auth filters,
    input sanitizer, and the per-request session middleware.
  - `backend/Services/` — Discord OAuth, Discord webhooks, Battle.net client.
  - `backend/Configuration/` — strongly-typed `AppOptions` bound from config.
- `frontend/public/` contains static frontend assets, including WoW class icons.
- `migration/` contains the one-time MySQL → Neon Postgres migration scripts.

## Data & Auth Model

- The database is Neon Postgres. Tables: `characters`, `raids`, `attendance`,
  `applications`, `auth`. (The old `log` table and audit-log concept were removed.)
- Data access is Dapper. `DefaultTypeMap.MatchNamesWithUnderscores = true` maps
  snake_case columns (`role_tank`) to PascalCase properties (`RoleTank`).
- Auth is a DB-backed session: a random token in the `auth` table mirrored in an
  HttpOnly `a2d_session` cookie. `SessionMiddleware` resolves it per request into
  `ICurrentUser`. Discord OAuth is the identity provider, isolated in
  `DiscordOAuthService` so it can be swapped for Battle.net later.
- Admin = username in the configured `App:Admins` allowlist.
- There is no API-key / bot auth surface anymore; login is frontend-only via
  `GET /api/auth/discord/login` → callback → session cookie.

## Frontend Conventions

- Use React, TypeScript, Vite, Wouter, Material UI, and MUI Data Grid.
- Prefer native MUI components and patterns over custom UI primitives.
- All API calls go through `frontend/src/api/endpoints.ts`. Backend routes live
  under `/api`.
- `fetch` uses `credentials: "include"` so the session cookie flows.
- Static class icons live at `/images/classes/{classId}.png` from
  `frontend/public/images/classes`.
- During local development, Vite proxies `/api` to `http://localhost:5200`.
- Do not reintroduce a root `/assets` dependency for app-owned images; Vite also
  uses `/assets` for production bundles.

## Backend Conventions

- Keep endpoint files thin: parse inputs, call a repository/service, return a
  result. Business rules (ownership/admin gates) live in the endpoint or service
  layer, not the repositories.
- Repositories only run SQL; they take an `IDbConnectionFactory` and use Dapper.
- Authorization uses the endpoint filters `.RequireAuth()` and `.RequireAdmin()`
  from `Security/AuthFilters.cs`. Failures return `{ "error": "..." }` with the
  matching status code — keep that error shape consistent.
- The API speaks snake_case JSON both directions (global
  `JsonNamingPolicy.SnakeCaseLower`). Request bodies from the frontend must use
  snake_case field names. Override per-property with `[JsonPropertyName]` only
  where the frontend expects a specific name (e.g. attendance rows use
  `raidId`/`characterId` and `character_*`-prefixed fields).
- Sanitize free-text input with `InputSanitizer.Clean` to match the historical
  data treatment (HTML-encoded, tags stripped).
- Config comes from `AppOptions` (the `App` section) plus the `Neon` connection
  string. Never hardcode secrets. `appsettings.Development.json` holds local
  secrets and is gitignored and excluded from publish output.

## Validation Commands

Backend build:

```powershell
dotnet build backend/AmusedToDeath.Api.csproj
```

Run the API locally (Development, reads `appsettings.Development.json`):

```powershell
cd backend
dotnet run --launch-profile http   # listens on http://localhost:5191
```

Frontend production build:

```powershell
cd frontend
npm run build
```

Frontend lint currently has known existing debt. Do not add it as a hard
CI/deploy gate until those issues are fixed:

```powershell
cd frontend
npm run lint
```

## Deployment Pipeline

Deployment is documented in `DEPLOY.md`. In short:

- The API deploys via `.github/workflows/deploy-api.yml` on pushes to `master`
  touching `backend/**`: it publishes a self-contained `linux-x64` build, SCPs it
  to the droplet, and swaps it into `/opt/a2d-api` behind the `a2d-api` systemd
  service (config from `/etc/a2d-api.env`). No containers; the droplet needs no
  .NET runtime.
- nginx serves the SPA and reverse-proxies `/api` to `http://localhost:5200`.
- The old PHP FTP workflow (`.github/workflows/deploy.yml`) is stale and does not
  apply to the .NET backend; the SPA deploy should be handled separately.

## Common Gotchas

- Because JSON is snake_case both ways, a camelCase field in a request body will
  silently fail to bind. Send snake_case (e.g. `role_tank`, not `roleTank`).
- Dapper needs `MatchNamesWithUnderscores` (set in `Program.cs`) for column →
  property mapping; joined queries that alias columns must alias to what the DTO
  expects.
- Postgres identifiers are case-folded unless quoted. The `attendance` columns
  `"characterId"`/`"raidId"` are quoted camelCase in the schema, so SQL must
  quote them.
- If moving frontend public assets, update both source references and
  `frontend/vite.config.ts` if a proxy is involved.
- `appsettings.Development.json` must never ship to the server; the csproj
  excludes it from publish and the systemd service runs in Production.
