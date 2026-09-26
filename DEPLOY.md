# Deployment

The stack is a React SPA (`frontend/`) plus a .NET 10 minimal API (`backend/`,
assembly `AmusedToDeath.Api`) backed by Neon Postgres. The API runs directly on
the droplet under systemd — no containers. The SPA is static files served by
nginx, which also reverse-proxies `/api` to the API process.

## Topology

```
                    ┌────────────────────────── droplet ──────────────────────────┐
browser ── https ──►│ nginx                                                        │
                    │   / ............ serves the SPA (frontend/dist)              │
                    │   /api .......... reverse-proxy ─► http://localhost:5200 ────┼─► a2d-api (systemd)
                    └──────────────────────────────────────────────────────────────┘
                                                                       │
                                                                       └─ Neon Postgres (SSL)
```

## One-time server setup

The API is deployed as a **self-contained** `linux-x64` build, so the droplet
does **not** need the .NET runtime installed.

1. Install the systemd unit:

   ```bash
   sudo cp a2d-api.service /etc/systemd/system/a2d-api.service
   ```

2. Create the environment file from the sample, fill in real values, and lock
   it down. Secrets live only here — never in git or the unit file:

   ```bash
   sudo cp a2d-api.env.sample /etc/a2d-api.env
   sudo nano /etc/a2d-api.env          # fill in real values
   sudo chown root:root /etc/a2d-api.env
   sudo chmod 600 /etc/a2d-api.env
   ```

   Keys the API binds (double underscore maps to nested config):
   - `ConnectionStrings__Neon`
   - `App__FrontendBaseUrl`
   - `App__CorsOrigins__0`
   - `App__Admins__0`, `App__Admins__1`, ...
   - `App__Discord__ClientId`, `App__Discord__ClientSecret`, `App__Discord__RedirectUri`
   - `App__BattleNet__ClientId`, `App__BattleNet__ClientSecret`, `App__BattleNet__Region`
   - `App__Webhooks__Announcement`, `App__Webhooks__Recruitment`

3. Enable the service (the deploy workflow does the first `start` once files land):

   ```bash
   sudo systemctl daemon-reload
   sudo systemctl enable a2d-api
   ```

4. Point nginx at the API. Serve the SPA and reverse-proxy `/api`:

   ```nginx
   # SPA
   location / {
       try_files $uri $uri/ /index.html;
   }

   # .NET API
   location /api/ {
       proxy_pass http://localhost:5200;
       proxy_http_version 1.1;
       proxy_set_header Host $host;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header X-Forwarded-Proto $scheme;
       proxy_set_header Upgrade $http_upgrade;
       proxy_set_header Connection "upgrade";
   }
   ```

   Reload nginx: `sudo nginx -t && sudo systemctl reload nginx`.

## API deployment (automated)

`.github/workflows/deploy-api.yml` runs on pushes to `master` that touch
`backend/**` (or via manual dispatch). It:

1. Publishes `backend/AmusedToDeath.Api.csproj` self-contained for `linux-x64`.
2. SCPs the output to `/opt/a2d-api-staging`.
3. Over SSH: stops `a2d-api`, swaps staging into `/opt/a2d-api` (keeping the
   previous build at `/opt/a2d-api-old` for rollback), marks the binary
   executable, and starts the service.

Required GitHub repository secrets:

| Secret | Purpose |
| --- | --- |
| `DO_HOST` | Droplet host/IP |
| `DO_USERNAME` | SSH user |
| `DO_SSH_KEY` | SSH private key for that user |

The SSH user needs passwordless `sudo systemctl stop/start a2d-api` and write
access to `/opt/a2d-api*`.

## API deployment (manual)

If you need to deploy by hand:

```bash
# From a machine with the .NET 10 SDK
dotnet publish backend/AmusedToDeath.Api.csproj -c Release -r linux-x64 --self-contained true -o ./publish

# Copy to the droplet
scp -r publish/* user@host:/opt/a2d-api-staging/

# On the droplet
sudo systemctl stop a2d-api || true
rm -rf /opt/a2d-api-old
[ -d /opt/a2d-api ] && mv /opt/a2d-api /opt/a2d-api-old
mv /opt/a2d-api-staging /opt/a2d-api
chmod +x /opt/a2d-api/AmusedToDeath.Api
sudo systemctl start a2d-api
```

## Frontend deployment

The SPA is built with `npm run build` (output in `frontend/dist/`) and served
by nginx from the web root `/var/www/www.amusedtodeath.eu`. This is automated by
`.github/workflows/deploy.yml` (frontend-only): on pushes to `master` touching
`frontend/**`, it builds the SPA, SCPs it to
`/var/www/www.amusedtodeath.eu-staging`, then swaps it into the live root
(keeping `-old` for rollback).

## Operations

```bash
# Status / logs
sudo systemctl status a2d-api
journalctl -u a2d-api -f

# Restart after an env change
sudo systemctl restart a2d-api

# Roll back to the previous build
sudo systemctl stop a2d-api
rm -rf /opt/a2d-api
mv /opt/a2d-api-old /opt/a2d-api
sudo systemctl start a2d-api
```

## Notes

- The API listens on `http://localhost:5200` (set in `a2d-api.service`); it is
  not exposed publicly — nginx is the only thing that talks to it.
- `ASPNETCORE_ENVIRONMENT=Production` means `appsettings.Development.json` is not
  read on the server (and it is excluded from the publish output anyway).
- Rotate the Neon password and update `ConnectionStrings__Neon` in
  `/etc/a2d-api.env`, then `sudo systemctl restart a2d-api`.
