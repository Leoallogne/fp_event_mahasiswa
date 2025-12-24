<?php
require_once __DIR__ . '/modules/users/GoogleAuth.php';

$googleAuth = new GoogleAuth();
$reflection = new ReflectionClass($googleAuth);

$clientId = $reflection->getProperty('clientId');
$clientId->setAccessible(true);
$clientSecret = $reflection->getProperty('clientSecret');
$clientSecret->setAccessible(true);
$redirectUri = $reflection->getProperty('redirectUri');
$redirectUri->setAccessible(true);

echo "Client ID: " . ($clientId->getValue($googleAuth) ?: "EMPTY") . "\n";
echo "Client Secret: " . ($clientSecret->getValue($googleAuth) ?: "EMPTY") . "\n";
echo "Redirect URI: " . ($redirectUri->getValue($googleAuth) ?: "EMPTY") . "\n";
echo "Env File Path: " . realpath(__DIR__ . '/.env') . "\n";
echo "Env File Exists: " . (file_exists(__DIR__ . '/.env') ? "YES" : "NO") . "\n";
