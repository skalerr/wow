<?php

declare(strict_types=1);

function env_value(string $name, ?string $default = null): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        return (string) $default;
    }

    return $value;
}

function wait_for_install_page(string $baseUrl): void
{
    $deadline = time() + 90;

    while (time() < $deadline) {
        $ch = curl_init(rtrim($baseUrl, '/') . '/install');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($httpCode === 200 && is_string($body) && str_contains($body, 'FusionCMS')) {
            return;
        }

        sleep(2);
    }

    throw new RuntimeException('FusionCMS install page did not become ready in time.');
}

function http_request(string $method, string $url, array $data = []): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('HTTP request failed: ' . $error);
    }

    $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new RuntimeException(sprintf('HTTP %d returned from %s', $httpCode, $url));
    }

    return trim($response);
}

function calculate_verifier(string $username, string $password, string $salt): string
{
    $g = gmp_init(7);
    $n = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);
    $h1 = sha1(strtoupper($username . ':' . $password), true);
    $h2 = sha1($salt . $h1, true);
    $h2 = gmp_import($h2, 1, GMP_LSW_FIRST);
    $verifier = gmp_powm($g, $h2, $n);
    $verifier = gmp_export($verifier, 1, GMP_LSW_FIRST);

    return str_pad($verifier, 32, chr(0), STR_PAD_RIGHT);
}

function connect_auth_db(): mysqli
{
    $db = new mysqli(
        env_value('ACORE_DB_HOST'),
        env_value('ACORE_DB_USER'),
        env_value('ACORE_DB_PASSWORD'),
        env_value('ACORE_AUTH_DB'),
        (int) env_value('ACORE_DB_PORT', '3306')
    );

    if ($db->connect_errno) {
        throw new RuntimeException('Auth DB connection failed: ' . $db->connect_error);
    }

    return $db;
}

function ensure_auth_account(mysqli $db, string $username, string $password): int
{
    $username = strtoupper($username);

    $stmt = $db->prepare('SELECT id FROM account WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $salt = random_bytes(32);
        $verifier = calculate_verifier($username, $password, $salt);

        $stmt = $db->prepare('INSERT INTO account (username, salt, verifier, email, reg_mail, expansion, locale, os) VALUES (?, ?, ?, ?, ?, 2, 0, ?)');
        $email = '';
        $os = 'Win';
        $saltBin = $salt;
        $verifierBin = $verifier;
        $stmt->bind_param('ssssss', $username, $saltBin, $verifierBin, $email, $email, $os);
        $stmt->execute();
        $accountId = $stmt->insert_id;
        $stmt->close();
    } else {
        $accountId = (int) $row['id'];
    }

    return $accountId;
}

function ensure_soap_account(): void
{
    $db = connect_auth_db();

    $accountId = ensure_auth_account(
        $db,
        env_value('ACORE_SOAP_USER'),
        env_value('ACORE_SOAP_PASSWORD')
    );

    $stmt = $db->prepare('INSERT INTO account_access (id, gmlevel, RealmID, comment) VALUES (?, 3, -1, ?) ON DUPLICATE KEY UPDATE gmlevel = VALUES(gmlevel), RealmID = VALUES(RealmID), comment = VALUES(comment)');
    $comment = 'FusionCMS SOAP account';
    $stmt->bind_param('is', $accountId, $comment);
    $stmt->execute();
    $stmt->close();

    $db->close();
}

function ensure_owner_account(): void
{
    $db = connect_auth_db();

    ensure_auth_account(
        $db,
        env_value('FUSIONCMS_OWNER_ACCOUNT', 'ADMIN'),
        env_value('FUSIONCMS_OWNER_PASSWORD', env_value('FUSIONCMS_SECURITY_CODE'))
    );

    $db->close();
}

function install_fusioncms(): void
{
    $baseUrl = env_value('FUSION_INSTALL_BASE', 'http://fusion-web');
    $lockFile = '/var/www/html/writable/install/.lock';

    ensure_soap_account();
    ensure_owner_account();

    if (is_file($lockFile)) {
        echo "FusionCMS already installed.\n";
        return;
    }

    wait_for_install_page($baseUrl);

    $dbPayload = [
        'cms_hostname' => env_value('FUSIONCMS_DB_HOST'),
        'cms_port' => env_value('FUSIONCMS_DB_PORT', '3306'),
        'cms_username' => env_value('FUSIONCMS_DB_USER'),
        'cms_password' => env_value('FUSIONCMS_DB_PASSWORD'),
        'cms_database' => env_value('FUSIONCMS_DB_NAME'),
        'auth_hostname' => env_value('ACORE_DB_HOST'),
        'auth_port' => env_value('ACORE_DB_PORT', '3306'),
        'auth_username' => env_value('ACORE_DB_USER'),
        'auth_password' => env_value('ACORE_DB_PASSWORD'),
        'auth_database' => env_value('ACORE_AUTH_DB'),
    ];

    $response = http_request('POST', $baseUrl . '/install/next?step=setAndCheckDbConnection', $dbPayload);
    if ($response !== '1') {
        throw new RuntimeException('Database bootstrap failed: ' . $response);
    }

    $authDetect = json_decode(http_request('POST', $baseUrl . '/install/next?step=autoDetectAuthConfig', [
        'auth_hostname' => env_value('ACORE_DB_HOST'),
        'auth_port' => env_value('ACORE_DB_PORT', '3306'),
        'auth_username' => env_value('ACORE_DB_USER'),
        'auth_password' => env_value('ACORE_DB_PASSWORD'),
        'auth_database' => env_value('ACORE_AUTH_DB'),
    ]), true, 512, JSON_THROW_ON_ERROR);

    $configPayload = [
        'title' => env_value('FUSIONCMS_TITLE'),
        'server_name' => env_value('FUSIONCMS_SERVER_NAME'),
        'realmlist' => env_value('FUSIONCMS_REALMLIST'),
        'max_expansion' => '2',
        'keywords' => env_value('FUSIONCMS_KEYWORDS'),
        'description' => env_value('FUSIONCMS_DESCRIPTION'),
        'analytics' => '',
        'captcha' => 'disabled',
        'site_key' => '',
        'secret_key' => '',
        'cdn' => '0',
        'cdn_link' => '',
        'security_code' => env_value('FUSIONCMS_SECURITY_CODE'),
        'emulator' => 'azerothcore',
        'superadmin' => env_value('FUSIONCMS_OWNER_ACCOUNT', 'ADMIN'),
        'realmd_rbac' => $authDetect['realmd_rbac'] ?? 'false',
        'realmd_battle_net' => $authDetect['realmd_battle_net'] ?? 'false',
        'realmd_totp_secret' => $authDetect['realmd_totp_secret'] ?? 'false',
        'realmd_totp_secret_name' => $authDetect['realmd_totp_secret_name'] ?? 'totp_secret',
        'realmd_account_encryption' => $authDetect['realmd_account_encryption'] ?? 'SRP6',
        'realmd_battle_net_encryption' => $authDetect['realmd_battle_net_encryption'] ?? 'SRP6_V2',
    ];

    $response = http_request('POST', $baseUrl . '/install/next?step=config', $configPayload);
    if ($response !== '1') {
        throw new RuntimeException('Config write failed: ' . $response);
    }

    $response = http_request('POST', $baseUrl . '/install/next?step=database');
    if ($response !== '1') {
        throw new RuntimeException('CMS SQL import failed: ' . $response);
    }

    $realms = [[
        'hostname' => env_value('ACORE_DB_HOST'),
        'username' => env_value('ACORE_DB_USER'),
        'password' => env_value('ACORE_DB_PASSWORD'),
        'characters' => env_value('ACORE_CHAR_DB'),
        'world' => env_value('ACORE_WORLD_DB'),
        'cap' => '100',
        'realm_expansion' => '2',
        'realmName' => env_value('FUSIONCMS_REALM_NAME'),
        'console_username' => env_value('ACORE_SOAP_USER'),
        'console_password' => env_value('ACORE_SOAP_PASSWORD'),
        'console_port' => env_value('ACORE_SOAP_PORT', '7878'),
        'emulator' => 'azerothcore',
        'port' => env_value('ACORE_WORLD_PORT', '8085'),
        'db_port' => env_value('ACORE_DB_PORT', '3306'),
    ]];

    $response = http_request('POST', $baseUrl . '/install/next?step=realms', [
        'realms' => json_encode($realms, JSON_THROW_ON_ERROR),
        'emulator' => 'azerothcore',
    ]);
    if ($response !== '1') {
        throw new RuntimeException('Realm import failed: ' . $response);
    }

    $response = http_request('GET', $baseUrl . '/install/next?step=final');
    if ($response !== 'success') {
        throw new RuntimeException('Final install step failed: ' . $response);
    }

    echo "FusionCMS installation complete.\n";
}

install_fusioncms();
