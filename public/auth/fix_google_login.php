<?php
// fix_google_login.php
// Automatically detects the correct URL and updates .env

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<!DOCTYPE html><html><head><title>Fix Google Login</title>';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<style>body{padding:2rem; max-width:800px; margin:0 auto; font-family: sans-serif;}</style>';
echo '</head><body>';

echo '<h2><i class="bi bi-tools"></i> Google Login Auto-Fixer</h2>';

// 1. Detect Current URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = $_SERVER['SCRIPT_NAME']; // /final/mahasiswa_fp/public/auth/fix_google_login.php
$dirPath = dirname($scriptPath); // /final/mahasiswa_fp/public/auth

// Construct the correct callback URL
$correctCallbackUrl = $protocol . "://" . $host . $dirPath . "/google-callback.php";

echo "<div class='alert alert-info'>";
echo "<strong>DETECTED CORRECT URL:</strong><br>";
echo "<code class='fs-5'>" . $correctCallbackUrl . "</code>";
echo "</div>";

// 2. Read and Update .env
$envPath = __DIR__ . '/../../.env';
$updated = false;
$message = "";

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);

    // Regex to find GOOGLE_REDIRECT_URI=...
    // Matches GOOGLE_REDIRECT_URI=anything_until_newline
    $pattern = '/^GOOGLE_REDIRECT_URI=.*$/m';
    $replacement = "GOOGLE_REDIRECT_URI=" . $correctCallbackUrl;

    // Check if it's already correct
    if (strpos($envContent, $replacement) !== false) {
        $message = "Your .env file is ALREADY CORRECT.";
        $alertClass = "alert-success";
    } else {
        // Perform replacement
        if (preg_match($pattern, $envContent)) {
            $newContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            // Append if not found
            $newContent = $envContent . "\n" . $replacement;
        }

        // Save
        if (file_put_contents($envPath, $newContent)) {
            $updated = true;
            $message = "SUCCESS! Updated .env file with the correct URL.";
            $alertClass = "alert-success";
        } else {
            $message = "ERROR: Could not write to .env file. Check permissions.";
            $alertClass = "alert-danger";
        }
    }
} else {
    $message = "ERROR: .env file not found at $envPath";
    $alertClass = "alert-danger";
}

echo "<div class='alert $alertClass'>" . $message . "</div>";

// 3. Instructions
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Copy</strong> the URL above (in the blue box).</li>";
echo "<li>Go to <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console > Credentials</a>.</li>";
echo "<li>Edit your OAuth 2.0 Request.</li>";
echo "<li>Paste the URL into <strong>Authorized redirect URIs</strong>.</li>";
echo "<li>Save the changes in Google Console.</li>";
echo "<li><a href='../login.php' class='btn btn-primary btn-sm'>Try Login Again</a></li>";
echo "</ol>";

echo '</body></html>';
