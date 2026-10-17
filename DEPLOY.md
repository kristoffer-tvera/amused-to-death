# Deployment

Production uses the shared Docker Compose droplet contract. This repository builds the application images and submits an immutable release bundle; it does not provision the droplet or change shared infrastructure.

## Site Configuration

- Site/project ID: `amused-to-death`
- Canonical hostname: `amusedtodeath.eu`
- Redirect hostname: `www.amusedtodeath.eu` (separate Nginx file and certificate)
- Published ports: `127.0.0.1:18060` web and `127.0.0.1:18061` API
- Compose bridge: `172.30.60.0/24`, gateway `172.30.60.1`

Before first deployment, the operator must verify that the bridge subnet does not overlap the droplet, VPC, or another project. The API trusts forwarded headers only from `172.30.60.1`; verify the source address observed by the container matches the host Nginx-to-published-port path. If the range must change, update the Compose subnet and API trusted proxy address together, then rebuild.

Host Nginx routes `/api/` to the API without stripping the prefix and all other paths to the web container. `/api` receives a 308 to `/api/`, preserving the query and request method. `www` has its own certificate and redirects to the canonical hostname; its `/healthz` path returns 200 for host readiness checks.

## Workflow

`.github/workflows/deploy.yml` runs build checks on pull requests but publishes/deploys only on `master` or a manual dispatch of `master`. The publishing job requires the protected GitHub environment named `production` and serializes releases for this site. It builds `linux/amd64` images, publishes unique release tags, scans for fixable high/critical vulnerabilities, records SBOM/provenance, and creates a bundle containing only:

```text
compose.yaml
manifest.json
images.env
nginx/
```

`images.env` contains only the web and API image digests. The workflow uploads the bundle as user `deploy` using strict SSH host verification, then invokes only `/usr/local/sbin/site-deploy apply`. In normal mode, an external HTTPS smoke failure triggers a guarded rollback. `DEPLOY_PUBLIC_CHECKS` must explicitly be `true` or `false`; use `true` for normal deployments. Diagnostic `false` skips public DNS smoke checks, not ACME validation.

## Operator Setup

Complete these steps outside this repository:

1. Configure the GitHub `production` environment and require approval/branch restrictions appropriate to production. Add secrets `DEPLOY_HOST` (droplet IPv4 address), `DEPLOY_SSH_KEY`, and `DEPLOY_KNOWN_HOSTS`. Set `DEPLOY_PUBLIC_CHECKS=true`.
2. Authorize the host's registry identity to pull this repository's GHCR packages. Keep registry credentials and SSH material out of workflow output and source control.
3. Create `/etc/sites/amused-to-death/runtime.env` as root-owned mode `0600`, using [deploy/runtime.env.example](deploy/runtime.env.example) as the key list. Required values are the Neon connection string and Battle.net client ID/secret; webhook URLs are optional. Allowlist the droplet at Neon and verify TLS. Register `https://amusedtodeath.eu/api/auth/bnet/callback` with Battle.net.
4. Point A records for both hostnames to the droplet, remove stale AAAA records, and ensure public ports 80/443 reach the shared Nginx listener. DNS changes are manual. The host must keep resolving both names here for HTTP-01 issuance and renewal.
5. Confirm the shared host's Nginx snippets and deployment helper are installed. Initial certificate issuance requires public DNS and reachable port 80. No app TLS keys or DNS credentials belong in the containers.

The Compose resource limits are initial budgets, not capacity guarantees. Measure memory/CPU after cutover. HSTS is intentionally not enabled until HTTPS is stable.

## Schema Changes

Normal API startup never applies migrations. For a release with pending migrations, review the SQL and its compatibility/rollback behavior, take the required database backup, then deliberately run the exact release API image with `/etc/sites/amused-to-death/runtime.env` and `--migrate` before activation. For example, using the API digest from that release's `images.env`:

```bash
docker run --rm \
  --env-file /etc/sites/amused-to-death/runtime.env \
  ghcr.io/OWNER/REPOSITORY-api@sha256:DIGEST \
  --migrate
```

This is an operator gate, not an automated workflow stage. The migration runner is DbUp and records applied scripts. Some historical migrations drop columns or update stored data; inspect every new script before running it. Image rollback does not reverse schema changes or restore data.

## Validation Boundaries

CI runs the frontend production build and configured lint command (lint is advisory because of existing debt), builds the API, publishes/scans both images, validates Compose/manifest agreement, and runs a read-only rootless web-image smoke check. No test project or frontend test command exists. API readiness is checked against the real database/schema during deployment; CI does not create a fixture database or inject production credentials.

A real droplet deployment, certificate issuance, DNS propagation, firewall reachability, observed proxy source address, and rollback behavior have not been verified from this repository workspace.
