# Amused to Death

Guild management app for the World of Warcraft guild **Amused to Death**. The current app is split into a React frontend and a PHP backend backed by MariaDB/MySQL.

## Tech Stack

- **Frontend:** React, TypeScript, Vite, Wouter, Material UI, MUI Data Grid
- **Backend:** PHP 8, mysqli, Apache rewrite support
- **Database:** MariaDB/MySQL
- **Auth:** Discord OAuth plus token-based bot auth
- **Integrations:** Battle.net profile API, Discord webhooks
- **Containers:** Podman commands are included below for local MariaDB and PHP backend hosting

## App Pages

- `/` — Home dashboard. Public login/apply entry point, and authenticated user's character overview.
- `/apply` — Public recruitment application flow with a multi-step form.
- `/app/:id` — Application detail/edit view, including private auth-token update links.
- `/apps` — Authenticated application list.
- `/characters` — Guild character directory with raiders, socials, mains, and alts.
- `/character/:id` — Character create/edit/detail screen.
- `/raids` — Raid list.
- `/raid/:id` — Raid detail, attendance, payment/cut handling, and admin tools.
- `/bnet` — Admin Battle.net token/status and character ilvl update tools.
- `/log` — Admin activity log.
- `/debug` — Admin session/debug utilities.

## Project Layout

```text
backend/
	actions/          PHP endpoints used by the React app
	core/             Shared auth, database, input, response, and service helpers
	database/init.php Database initialization script
	legacy/           Thin wrappers for the old endpoint names
	secrets.sample.php
frontend/
	public/           Static frontend assets, including WoW class icons
	src/              React app source
	dist/             Production build output after npm run build
```

## Frontend Setup

```powershell
cd frontend
npm install
npm run dev
```

Build for production:

```powershell
cd frontend
npm run build
```

## Backend Secrets

Copy the sample file and fill in real values:

```powershell
Copy-Item backend/secrets.sample.php backend/secrets.php
```

For local containers, the DB settings usually look like this:

```php
$dbservername = 'a2d-db';
$dbusername = 'a2d_user';
$dbpassword = 'a2d_password';
$dbname = 'amused_to_death';
```

`backend/secrets.php` is ignored and should not be committed.

## Local Database With Podman

Create a shared network:

```powershell
podman network create a2d-net
```

Pull MariaDB:

```powershell
podman pull docker.io/library/mariadb:11
```

Start MariaDB:

```powershell
podman run --name a2d-db --network a2d-net -d `
	-e MARIADB_ROOT_PASSWORD=root_password `
	-e MARIADB_DATABASE=amused_to_death `
	-e MARIADB_USER=a2d_user `
	-e MARIADB_PASSWORD=a2d_password `
	-p 3306:3306 `
	-v a2d-db-data:/var/lib/mysql `
	docker.io/library/mariadb:11
```

Initialize tables after the PHP container is running:

```powershell
podman exec -it a2d-php php backend/database/init.php
```

Useful DB commands:

```powershell
podman logs -f a2d-db
podman stop a2d-db
podman start a2d-db
```

## PHP Backend Container With Podman

Build the PHP/Apache image:

```powershell
podman build -t a2d-php -f backend/Containerfile .
```

Run the backend container on the same network as MariaDB:

```powershell
podman run --name a2d-php --network a2d-net -d `
	-p 8000:80 `
	-v ${PWD}:/var/www/html:Z `
	a2d-php
```

The backend is then available at:

```text
http://localhost:8000/backend/actions/data.php?action=me
```

During frontend development, Vite proxies `/backend` to `http://localhost:8000`.

Useful PHP container commands:

```powershell
podman logs -f a2d-php
podman exec -it a2d-php php -v
podman exec -it a2d-php php backend/database/init.php
podman stop a2d-php
podman start a2d-php
```

## Local Development Flow

1. Copy `backend/secrets.sample.php` to `backend/secrets.php` and fill in values.
2. Start MariaDB with Podman.
3. Build and start the PHP backend container.
4. Run `podman exec -it a2d-php php backend/database/init.php` once to create tables.
5. Start the frontend with `npm run dev` from `frontend/`.
6. Open the Vite dev URL and use the app.

## Validation

Frontend build:

```powershell
cd frontend
npm run build
```

PHP syntax check:

```powershell
Get-ChildItem backend -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```
