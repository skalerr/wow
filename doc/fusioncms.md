# FusionCMS

This repository can run `FusionCMS` alongside the AzerothCore auth/world stack through the root `docker compose`.

Start the website:

```bash
docker compose -f docker-compose.yml -f docker-compose.fusioncms.yml up -d --build --remove-orphans fusion-db fusion-php fusion-web
./tools/bootstrap-fusioncms.sh
```

Open:

```text
http://localhost:8080
```

Default local values in this setup:
- CMS DB host: `fusion-db`
- CMS DB name: `fusioncms`
- Auth DB host: `ac-database`
- Character DB host: `ac-database`
- World DB host: `ac-database`
- SOAP host: `ac-worldserver`
- SOAP port: `7878`
- SOAP user: `CMSSOAP`
- SOAP password: `CmsSoap123`
- Owner account: `ADMIN`

Useful overrides in root `.env`:
- `FUSIONCMS_URL`
- `FUSIONCMS_HTTP_PORT`
- `FUSIONCMS_TITLE`
- `FUSIONCMS_SERVER_NAME`
- `FUSIONCMS_REALMLIST`
- `FUSIONCMS_REALM_NAME`
- `FUSIONCMS_OWNER_ACCOUNT`
- `FUSIONCMS_SECURITY_CODE`
- `FUSIONCMS_SOAP_USER`
- `FUSIONCMS_SOAP_PASSWORD`
