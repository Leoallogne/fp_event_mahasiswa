<?php
require_once __DIR__ . '/modules/users/GoogleAuth.php';
$googleAuth = new GoogleAuth();
echo "Generated Auth URL:\n" . $googleAuth->getAuthUrl() . "\n\n";

$reflection = new ReflectionClass($googleAuth);
$prop = $reflection->getProperty('redirectUri');
$prop->setAccessible(true);
echo "Configured Redirect URI in class: " . $prop->getValue($googleAuth) . "\n";
