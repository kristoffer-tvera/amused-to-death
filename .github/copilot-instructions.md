# Copilot Instructions

## Project Shape

This repository is a World of Warcraft guild management app that was migrated from a PHP page app to a React frontend plus a PHP backend.

- `frontend/` is the React/Vite/TypeScript/MUI single-page app.
- `backend/` is the PHP API/backend surface.
- `backend/actions/` contains grouped endpoint scripts used by the frontend.
- `backend/core/` contains shared database, auth, input, response, and service helpers.
- `backend/database/init.php` initializes the database schema.
- `backend/legacy/` contains compatibility wrappers for old endpoint names.
- `frontend/public/` contains static frontend assets, including WoW class icons.
- The old root-level PHP pages and root `assets/` folder have been removed.

## Frontend Conventions

- Use React, TypeScript, Vite, Wouter, Material UI, and MUI Data Grid.
- Prefer native MUI components and patterns over custom UI primitives.
- API calls should go through `frontend/src/api/endpoints.ts`.
- Backend routes are expected under `/backend/actions`.
- Static class icons live at `/images/classes/{classId}.png` from `frontend/public/images/classes`.
- During local development, Vite proxies `/backend` to `http://localhost:8000`.
- Do not reintroduce a root `/assets` dependency for app-owned images; Vite also uses `/assets` for production bundles.

## Backend Conventions

- PHP backend files should include `backend/core/bootstrap.php` rather than manually requiring individual helpers.
- Keep shared business logic in `backend/core/Services.php` where practical.
- Keep action files in `backend/actions/` thin: parse inputs, call services, return JSON/redirects.
- Use `backend_db()` and `backend_tables()` from bootstrap instead of directly reaching for database globals.
- Use helper accessors from bootstrap for config values when adding one avoids implicit global warnings.
- `backend/secrets.php` is generated locally/deployment-side and must not be committed.
- Update `backend/secrets.sample.php` whenever a new required secret/config variable is introduced.

## Validation Commands

Frontend production build:

```powershell
cd frontend
npm run build
```

Frontend lint currently has known existing debt. Do not add it as a hard CI/deploy gate until those issues are fixed:

```powershell
cd frontend
npm run lint
```

Backend PHP syntax check:

```powershell
Get-ChildItem backend -Recurse -Filter *.php | Where-Object { $_.Name -ne 'secrets.php' } | ForEach-Object { php -l $_.FullName }
```

## Deployment Pipeline

Deployment is defined in `.github/workflows/deploy.yml`.

- Pull requests should validate build/lint-style checks without creating secrets or deploying.
- Pushes to `master` generate `backend/secrets.php` from the `PHP` GitHub secret.
- The workflow builds `frontend/dist`, then deploys a clean artifact made from `backend/` plus `frontend/dist/`.
- FTP deploy target is configured in the workflow. Avoid changing server details unless explicitly requested.

## Local Containers

- README contains Podman commands for MariaDB and PHP/Apache.
- The PHP container is built from `backend/Containerfile` at repository root context.
- Local backend URL example: `http://localhost:8000/backend/actions/data.php?action=me`.

## Common Gotchas

- Intelephense may not infer variables from `backend/secrets.php`; prefer bootstrap helper functions over direct implicit globals in action files.
- If moving frontend public assets, update both source references and `frontend/vite.config.ts` if a proxy was involved.
- Vite build output under `frontend/dist/assets` is unrelated to any source asset folder.
- Keep old PHP compatibility only in `backend/legacy/`; do not recreate old root-level PHP pages unless explicitly requested.
- `npm run build` is the current frontend deploy gate; `npm run lint` should be fixed separately before enforcing it in CI.
