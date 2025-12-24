<?php
// Mock Database to prevent connection error
class Database
{
    public function getConnection()
    {
        return null;
    }
}

require_once __DIR__ . '/modules/users/GoogleAuth.php';

$googleAuth = new GoogleAuth();
$reflection = new ReflectionClass($googleAuth);

$clientId = $reflection->getProperty('clientId');
$clientId->setAccessible(true);
$redirectUri = $reflection->getProperty('redirectUri');
$redirectUri->setAccessible(true);

echo "Loaded Client ID: " . $clientId->getValue($googleAuth) . "\n";
echo "Loaded Redirect URI: " . $redirectUri->getValue($googleAuth) . "\n";
echo "Full Auth URL:\n" . $googleAuth->getAuthUrl() . "\n";
