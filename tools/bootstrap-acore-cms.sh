#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

SOAP_USER="${ACORE_CMS_SOAP_USER:-CMSSOAP}"
SOAP_PASS="${ACORE_CMS_SOAP_PASSWORD:-CmsSoap123}"
DB_ROOT_PASS="${DOCKER_DB_ROOT_PASSWORD:-password}"
REALM_ALIAS="${ACORE_CMS_REALM_ALIAS:-AzerothCore}"
SITE_TITLE="${ACORE_CMS_TITLE:-AzerothCore Realm}"
SITE_TAGLINE="${ACORE_CMS_TAGLINE:-Register and manage your AzerothCore account.}"

read -r SALT_HEX VERIFIER_HEX < <(
  docker compose exec -T php php -d opcache.enable_cli=0 -r '
    $username = strtoupper($argv[1]);
    $password = $argv[2];
    $salt = random_bytes(32);
    $g = gmp_init(7);
    $n = gmp_init("894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7", 16);
    $h1 = sha1($username . ":" . $password, true);
    $h2 = sha1($salt . $h1, true);
    $h2 = gmp_import($h2, 1, GMP_LSW_FIRST);
    $verifier = gmp_powm($g, $h2, $n);
    $verifier = gmp_export($verifier, 1, GMP_LSW_FIRST);
    $verifier = str_pad($verifier, 32, chr(0), STR_PAD_RIGHT);
    echo bin2hex($salt) . " " . bin2hex($verifier) . PHP_EOL;
  ' "$SOAP_USER" "$SOAP_PASS"
)

docker compose exec -T ac-database mysql -uroot "-p${DB_ROOT_PASS}" acore_auth -e "
INSERT INTO account (username, salt, verifier, email, reg_mail, expansion, locale, os)
VALUES (UPPER('${SOAP_USER}'), UNHEX('${SALT_HEX}'), UNHEX('${VERIFIER_HEX}'), '', '', 2, 0, 'Win')
ON DUPLICATE KEY UPDATE
  salt = VALUES(salt),
  verifier = VALUES(verifier),
  expansion = VALUES(expansion),
  os = VALUES(os);

INSERT INTO account_access (id, gmlevel, RealmID, comment)
SELECT id, 3, -1, 'ACore CMS SOAP account'
FROM account
WHERE username = UPPER('${SOAP_USER}')
ON DUPLICATE KEY UPDATE
  gmlevel = VALUES(gmlevel),
  RealmID = VALUES(RealmID),
  comment = VALUES(comment);
"

docker compose exec -T php wp network meta update 1 registration user --allow-root >/dev/null
docker compose exec -T php wp option update blogname "$SITE_TITLE" --allow-root >/dev/null
docker compose exec -T php wp option update blogdescription "$SITE_TAGLINE" --allow-root >/dev/null
docker compose exec -T php wp option update default_role subscriber --allow-root >/dev/null
docker compose exec -T php wp option update alnuar_add_password_fields 1 --allow-root >/dev/null

docker compose exec -T php wp option update acore_realm_alias "$REALM_ALIAS" --allow-root >/dev/null
docker compose exec -T php wp option update acore_soap_host ac-worldserver --allow-root >/dev/null
docker compose exec -T php wp option update acore_soap_port 7878 --allow-root >/dev/null
docker compose exec -T php wp option update acore_soap_user "$SOAP_USER" --allow-root >/dev/null
docker compose exec -T php wp option update acore_soap_pass "$SOAP_PASS" --allow-root >/dev/null

docker compose exec -T php wp option update acore_db_auth_host ac-database --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_auth_port 3306 --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_auth_user root --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_auth_pass "$DB_ROOT_PASS" --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_auth_name acore_auth --allow-root >/dev/null

docker compose exec -T php wp option update acore_db_char_host ac-database --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_char_port 3306 --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_char_user root --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_char_pass "$DB_ROOT_PASS" --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_char_name acore_characters --allow-root >/dev/null

docker compose exec -T php wp option update acore_db_world_host ac-database --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_world_port 3306 --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_world_user root --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_world_pass "$DB_ROOT_PASS" --allow-root >/dev/null
docker compose exec -T php wp option update acore_db_world_name acore_world --allow-root >/dev/null

PAGE_ID="$(docker compose exec -T php wp post list --post_type=page --name=home --field=ID --allow-root | tr -d '\r' | tail -n 1)"

if [[ -z "$PAGE_ID" ]]; then
  PAGE_ID="$(docker compose exec -T php wp post create --post_type=page --post_status=publish --post_title='Home' --post_name=home --post_content='[acore_realm_portal]' --porcelain --allow-root | tr -d '\r')"
else
  docker compose exec -T php wp post update "$PAGE_ID" --post_status=publish --post_title='Home' --post_content='[acore_realm_portal]' --allow-root >/dev/null
fi

docker compose exec -T php wp option update show_on_front page --allow-root >/dev/null
docker compose exec -T php wp option update page_on_front "$PAGE_ID" --allow-root >/dev/null

SAMPLE_PAGE_ID="$(docker compose exec -T php wp post list --post_type=page --name=sample-page --field=ID --allow-root | tr -d '\r' | tail -n 1)"
if [[ -n "$SAMPLE_PAGE_ID" ]]; then
  docker compose exec -T php wp post delete "$SAMPLE_PAGE_ID" --force --allow-root >/dev/null
fi

echo "ACore CMS bootstrap complete."
echo "Portal page ID: $PAGE_ID"
echo "SOAP account: $SOAP_USER"
