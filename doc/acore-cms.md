# ACore CMS

This repository can now run the official `acore-cms` stack through the root `docker compose`.

What was added:
- official `acore-cms` as git submodule under `apps/acore-cms`
- WordPress, nginx, mysql and redis services in `docker-compose.override.yml`
- SOAP enabled in the local `worldserver.conf` so the CMS can talk to AzerothCore

Start the CMS:

```bash
docker compose up -d --build wp-db redis php web.local
./tools/bootstrap-acore-cms.sh
```

Open:

```text
http://localhost:8080
```

Default WordPress admin credentials:
- user: `admin`
- password: `admin`
- email: `admin@example.com`

The bootstrap script:
- creates a dedicated SOAP account with GM level `3`
- configures the AzerothCore plugin database and SOAP settings
- enables website registration
- replaces the default sample page with a portal homepage and registration form

Default bootstrap values:
- SOAP user: `CMSSOAP`
- SOAP password: `CmsSoap123`
- SOAP host: `ac-worldserver`
- SOAP port: `7878`
- Auth DB host: `ac-database`
- Character DB host: `ac-database`
- World DB host: `ac-database`
- DB port: `3306`
- DB user: `root`
- DB password: value of `DOCKER_DB_ROOT_PASSWORD`
- Auth DB name: `acore_auth`
- Character DB name: `acore_characters`
- World DB name: `acore_world`

Useful overrides in root `.env`:
- `ACORE_CMS_URL`
- `ACORE_CMS_HTTP_PORT`
- `ACORE_CMS_HTTPS_PORT`
- `ACORE_CMS_ADMIN_USER`
- `ACORE_CMS_ADMIN_PASSWORD`
- `ACORE_CMS_ADMIN_EMAIL`
- `ACORE_CMS_TITLE`
