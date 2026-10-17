# Amused to Death

Guild management for the World of Warcraft guild Amused to Death. The React SPA talks to a .NET 10 minimal API backed by Neon Postgres. Authentication uses Battle.net OAuth and database-backed sessions.

## Stack

- Frontend: React 19, TypeScript, Vite, Wouter, Material UI
- API: ASP.NET Core 10 minimal API, Dapper, Npgsql
- Database: Neon Postgres
- Production: digest-pinned Docker images; host Nginx and the shared site-deploy helper

The API owns its `/api` prefix. Browser requests use the same-origin relative `/api` path. Local Vite development proxies that prefix to the backend at `http://localhost:5191`.

## Local Development

Use Node.js 24 and the .NET 10 SDK.

```powershell
cd frontend
npm ci
npm run dev
```

Run the API from another terminal with local values in the ignored `backend/appsettings.Development.json`:

```powershell
dotnet run --project backend/AmusedToDeath.Api.csproj --launch-profile http
```

## Validation

```powershell
cd frontend
npm ci
npm run build
npm run lint
cd ..
dotnet build backend/AmusedToDeath.Api.csproj
```

There is no test project or frontend test command. Frontend lint currently has known debt and is advisory in CI; it is not a deployment gate.

## Production Deployment

The site ID is `amused-to-death`; `amusedtodeath.eu` is canonical and `www.amusedtodeath.eu` redirects to it. Containers publish only to `127.0.0.1:18060` (web) and `127.0.0.1:18061` (API). Deployment artifacts and per-host Nginx configs are in `deploy/`.

`.github/workflows/deploy.yml` builds, scans, and smoke-checks the images, then sends a digest-only release bundle to the shared deploy helper. Configure the protected GitHub `production` environment with `DEPLOY_HOST`, `DEPLOY_SSH_KEY`, and `DEPLOY_KNOWN_HOSTS`; set the repository/environment variable `DEPLOY_PUBLIC_CHECKS` explicitly to `true` for normal deployments. See [DEPLOY.md](DEPLOY.md) for DNS, runtime configuration, bridge-network, registry, and migration gates.

API startup does not apply migrations. Review schema changes and run the explicit `--migrate` command as an operator step when a release requires it. Image rollback does not roll back database changes.
