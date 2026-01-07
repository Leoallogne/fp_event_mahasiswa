<?php
// Debug script to check Google Auth Configuration
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Google Auth Debugger</h1>";

// 1. Check File Locations
echo "<h2>1. File Structure</h2>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "google-callback.php exists: " . (file_exists('google-callback.php') ? 'YES' : 'NO') . "<br>";

// 2. Load .env
echo "<h2>2. Environment Config</h2>";
$envFile = __DIR__ . '/../../.env';
echo ".env file path: " . realpath($envFile) . "<br>";

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, 'GOOGLE_REDIRECT_URI') !== false) {
            echo "<strong>Found in .env:</strong> " . htmlspecialchars($line) . "<br>";

            // Parse it to check for hidden characters
            list($key, $value) = explode('=', $line, 2);
            $value = trim($value);
            echo "Parsed Value: [" . htmlspecialchars($value) . "]<br>";
        }
    }
} else {
    echo "<span style='color:red'>.env file NOT FOUND</span><br>";
}

// 3. Server Info
echo "<h2>3. Server Info</h2>";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";

$detectedLink = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['SCRIPT_NAME']) . "/google-callback.php";
echo "<br><strong>Expected Correct Callback URL:</strong><br>";
echo $detectedLink;

echo "<br><br>";
echo "<h3>Recommendations:</h3>";
echo "<ul>";
echo "<li>Ensure the URL in <strong>Step 2 (Parsed Value)</strong> exactly matches the <strong>Expected Correct Callback URL</strong> above.</li>";
echo "<li>Ensure this EXACT URL is added to your <strong>Google Cloud Console</strong> > APIs & Services > Credentials > OAuth 2.0 Client IDs > Authorized redirect URIs.</li>";
echo "</ul>";
