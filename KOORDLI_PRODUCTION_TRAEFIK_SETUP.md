# Koordli — Production Domain & SSL Setup Guide
### Traefik + Docker + Let's Encrypt (Wildcard Subdomains + Custom Domains)

Save this file. Follow it top-to-bottom the day you provision your VPS. Nothing here needs to happen before then — this is purely reference for deployment day.

---

## 0. What This Achieves

- `koordli.com` and every `*.koordli.com` tenant subdomain get automatic, valid HTTPS — zero manual certificate work, ever.
- Tenants on Pro/Enterprise plans can point their own domain (e.g. `app.haywhyevents.com`) at Koordli, and it also gets automatic HTTPS.
- Your Laravel `ResolveTenantByDomain` middleware (already built) does the actual "which tenant is this" lookup — Traefik's only job is routing + SSL.

---

## 1. Prerequisites — Buy/Set Up Before Following This Guide

| # | Item | Notes |
|---|---|---|
| 1 | A VPS (Hostinger, DigitalOcean, etc.) | Any size to start; upgrade later |
| 2 | The domain `koordli.com` purchased | From any registrar |
| 3 | A **free Cloudflare account** | Required for wildcard SSL — see step 2 |
| 4 | Docker + Docker Compose installed on the VPS | `curl -fsSL https://get.docker.com \| sh` |

---

## 2. Point Your Domain Through Cloudflare

Wildcard SSL certificates (`*.koordli.com`) require a **DNS-01 challenge**, which needs your DNS provider's API. Cloudflare is free and Traefik supports it natively — this is why it's required, not optional, for the subdomain part of this setup.

1. Sign up at cloudflare.com (free plan is enough)
2. Add `koordli.com` as a site
3. Cloudflare gives you 2 nameservers (e.g. `bob.ns.cloudflare.com`) — go to your domain registrar and replace the existing nameservers with these
4. Wait for Cloudflare to show "Active" (can take a few hours)
5. In Cloudflare DNS settings, add these records (proxy status: **DNS only / grey cloud**, NOT proxied/orange — Traefik needs to see real traffic directly):

   | Type | Name | Content | Proxy |
   |---|---|---|---|
   | A | `koordli.com` | `YOUR_VPS_IP` | DNS only |
   | A | `*.koordli.com` | `YOUR_VPS_IP` | DNS only |

6. Get a Cloudflare API Token (NOT the Global API Key — tokens are safer):
   - Cloudflare dashboard → My Profile → API Tokens → Create Token
   - Use template **"Edit zone DNS"**, scope it to your `koordli.com` zone only
   - Copy the token — you'll paste it into your `.env` / docker-compose in step 4

---

## 3. Server Directory Structure

SSH into your VPS and set up:

```bash
mkdir -p ~/koordli/letsencrypt
cd ~/koordli
touch letsencrypt/acme.json letsencrypt/acme-custom.json
chmod 600 letsencrypt/acme.json letsencrypt/acme-custom.json
```

(Traefik refuses to write certificates into a file that isn't `600` permissions — this step is not optional, skipping it causes silent SSL failures.)

---

## 4. `docker-compose.yml`

Place this in `~/koordli/docker-compose.yml`. Replace anything in `ALL_CAPS` with your real values.

```yaml
version: "3.8"

services:

  traefik:
    image: traefik:v3.0
    container_name: koordli-traefik
    restart: unless-stopped
    command:
      # Providers
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"

      # Entrypoints
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--entrypoints.web.http.redirections.entrypoint.to=websecure"
      - "--entrypoints.web.http.redirections.entrypoint.scheme=https"

      # Certificate resolver #1 — wildcard *.koordli.com via Cloudflare DNS-01
      - "--certificatesresolvers.wildcard.acme.dnschallenge=true"
      - "--certificatesresolvers.wildcard.acme.dnschallenge.provider=cloudflare"
      - "--certificatesresolvers.wildcard.acme.email=YOUR_EMAIL@example.com"
      - "--certificatesresolvers.wildcard.acme.storage=/letsencrypt/acme.json"

      # Certificate resolver #2 — tenant custom domains via HTTP-01
      # (works for ANY domain pointed at this server, even ones you don't control DNS for)
      - "--certificatesresolvers.customdomains.acme.httpchallenge=true"
      - "--certificatesresolvers.customdomains.acme.httpchallenge.entrypoint=web"
      - "--certificatesresolvers.customdomains.acme.email=YOUR_EMAIL@example.com"
      - "--certificatesresolvers.customdomains.acme.storage=/letsencrypt/acme-custom.json"

    environment:
      - CF_DNS_API_TOKEN=YOUR_CLOUDFLARE_API_TOKEN

    ports:
      - "80:80"
      - "443:443"

    volumes:
      - "/var/run/docker.sock:/var/run/docker.sock:ro"
      - "./letsencrypt:/letsencrypt"

    networks:
      - koordli_network


  koordli-app:
    build: .
    container_name: koordli-app
    restart: unless-stopped
    environment:
      - APP_URL=https://koordli.com
      # ... your other Laravel .env values (DB, mail, etc.) go here or in an env_file
    labels:
      - "traefik.enable=true"

      # Router 1 — main domain + all *.koordli.com subdomains (wildcard cert, instant)
      - "traefik.http.routers.koordli-main.rule=Host(`koordli.com`) || HostRegexp(`{subdomain:[a-z0-9-]+}.koordli.com`)"
      - "traefik.http.routers.koordli-main.entrypoints=websecure"
      - "traefik.http.routers.koordli-main.tls.certresolver=wildcard"
      - "traefik.http.routers.koordli-main.tls.domains[0].main=koordli.com"
      - "traefik.http.routers.koordli-main.tls.domains[0].sans=*.koordli.com"
      - "traefik.http.routers.koordli-main.priority=10"

      # Router 2 — catch-all for tenant custom domains (HTTP-01, cert issued on first real visit)
      - "traefik.http.routers.koordli-custom.rule=HostRegexp(`{host:.+}`)"
      - "traefik.http.routers.koordli-custom.entrypoints=websecure"
      - "traefik.http.routers.koordli-custom.tls.certresolver=customdomains"
      - "traefik.http.routers.koordli-custom.priority=1"

      - "traefik.http.services.koordli-app.loadbalancer.server.port=80"

    networks:
      - koordli_network


networks:
  koordli_network:
    driver: bridge
```

**Why two routers with different priorities:** the `priority=10` router matches `koordli.com` and its subdomains FIRST and uses the instant wildcard cert. Anything that doesn't match (i.e. a tenant's own custom domain like `app.haywhyevents.com`) falls through to the `priority=1` catch-all router, which requests a fresh certificate for that specific domain the moment real traffic hits it.

---

## 5. Deploy

```bash
cd ~/koordli
docker compose up -d
docker compose logs -f traefik   # watch for certificate issuance, confirm no errors
```

Visit `https://koordli.com` — should load with a valid padlock immediately (wildcard cert covers it).

Set a tenant's subdomain in Domain Settings (e.g. `haywhy`), visit `https://haywhy.koordli.com` — should also load instantly with valid HTTPS, no waiting, since it's covered by the same wildcard cert.

---

## 6. Testing a Tenant's Custom Domain (End-to-End)

1. Tenant goes to **Domain Settings** in their dashboard, adds `app.haywhyevents.com`
2. Tenant (or you, if testing) adds these two DNS records at the tenant's own registrar:
   - **CNAME**: `app.haywhyevents.com` → `koordli.com`
   - **TXT**: `_koordli-verify.app.haywhyevents.com` → the verification token shown on the Domain Settings page
3. Wait for DNS propagation (a few minutes to a few hours — use `https://dnschecker.org` to confirm it's live globally)
4. Tenant clicks **"Verify Now"** in Koordli — this checks both records via `dns_get_record()` and marks the domain `verified`
5. Visit `https://app.haywhyevents.com` for the first time — Traefik's catch-all router sees a new Host header it doesn't have a cert for yet, performs an HTTP-01 challenge (this requires the CNAME to already be correctly resolving to your server — which it will be, since step 4 already confirmed that), and Let's Encrypt issues a certificate automatically. This first request may take a few extra seconds while the cert is issued; every request after that is instant.

---

## 7. Ongoing Maintenance / Things to Remember

- **Certificates auto-renew.** Traefik handles this in the background for both resolvers — nothing for you to do manually, ever.
- **`koordli:recheck-domains`** (already built into the Laravel app) runs daily via the scheduler and re-verifies every tenant custom domain's DNS — make sure your production server actually has a real cron entry running the Laravel scheduler:
  ```bash
  * * * * * cd /path-to-your-app && php artisan schedule:run >> /dev/null 2>&1
  ```
  Without this cron line, `koordli:process-subscriptions` AND `koordli:recheck-domains` never run in production, even though they're correctly scheduled in `routes/console.php`.
- **If a tenant's DNS breaks later** (they remove the CNAME, change hosting, etc.), the daily recheck will flip their `domain_status` to `failed` — the Laravel app already handles this gracefully (falls back to their subdomain), but the SSL certificate for that abandoned domain will simply stop renewing and eventually expire on its own — no action needed on your end.
- **Backup `letsencrypt/acme.json` and `acme-custom.json`** periodically. If you lose these files, Traefik has to re-issue every certificate from scratch (fine for you, more disruptive if you have many tenant custom domains built up).
- **Cloudflare proxy status must stay "DNS only" (grey cloud)** for `koordli.com` and `*.koordli.com`. If you ever turn on the orange-cloud proxy, Traefik will see Cloudflare's IP instead of real visitor traffic, and both the wildcard cert renewal and tenant custom domain SSL issuance will break.

---

## 8. Quick Troubleshooting Reference

| Symptom | Likely Cause | Fix |
|---|---|---|
| `koordli.com` doesn't load at all | DNS not pointed at VPS yet, or ports 80/443 blocked by firewall | Check `dig koordli.com`, check `ufw status` / cloud firewall rules |
| Subdomain works but shows "not secure" | Wildcard cert not yet issued | Check `docker compose logs traefik` for ACME/Cloudflare API errors |
| Tenant custom domain never gets SSL | Their CNAME isn't actually resolving to your server yet | Re-check with `dig app.theirdomain.com` before expecting Traefik to succeed |
| Cloudflare API errors in Traefik logs | Wrong/expired API token, or token scoped to wrong zone | Regenerate token, confirm it's scoped to the `koordli.com` zone |
| `koordli:recheck-domains` never seems to run | Production cron for `schedule:run` was never set up | Add the cron line from Section 7 |

---

*This document reflects the Koordli Phase 11 domain/white-label architecture as built. The application-layer code (feature flags, tenant resolution middleware, Domain Settings UI, verification service) is already complete and requires no changes to work with this infrastructure setup.*