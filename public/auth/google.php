<?php
session_start();
require_once __DIR__ . '/../../modules/users/GoogleAuth.php';

$googleAuth = new GoogleAuth();
$authUrl = $googleAuth->getAuthUrl();

header('Location: ' . $authUrl);
exit;
