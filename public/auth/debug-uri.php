<?php
// Direct test of what GoogleAuth is sending
require_once __DIR__ . '/../../modules/users/GoogleAuth.php';

$googleAuth = new GoogleAuth();
$authUrl = $googleAuth->getAuthUrl();

// Parse the redirect_uri from the URL
parse_str(parse_url($authUrl, PHP_URL_QUERY), $params);

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n<title>Google AuthDebug</title>\n</head>\n<body style='font-family: monospace; padding: 20px;'>\n";
echo "<h2>Google Auth URL Debug</h2>\n";
echo "<h3>Redirect URI Being Sent to Google:</h3>\n";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; word-break: break-all;'>\n";
echo htmlspecialchars($params['redirect_uri'] ?? 'NOT SET');
echo "</div>\n";
echo "<h3>Character Codes:</h3>\n";
echo "<pre>";
$uri = $params['redirect_uri'] ?? '';
for ($i = 0; $i < strlen($uri); $i++) {
    echo $uri[$i] . " = " . ord($uri[$i]) . "\n";
}
echo "</pre>\n";
echo "<h3>Full Auth URL:</h3>\n";
echo "<textarea style='width: 100%; height: 200px;'>" . htmlspecialchars($authUrl) . "</textarea>\n";
echo "<hr>\n";
echo "<a href='$authUrl'>Click here to test Google login</a>\n";
echo "</body>\n</html>";
?>