<?php
// Simple test to verify the callback path is accessible
echo "✅ Google Callback Test Page\n\n";
echo "If you see this, the path is accessible!\n\n";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

echo "GET Parameters:\n";
print_r($_GET);

echo "\n\nThis file is located at: /Applications/MAMP/htdocs/final/mahasiswa_fp/public/auth/test-callback.php\n";
echo "Access it at: http://localhost:8888/final/mahasiswa_fp/public/auth/test-callback.php\n";
?>