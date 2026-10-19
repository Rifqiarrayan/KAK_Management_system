<?php
/**
 * PATH DEBUG - Cek konfigurasi path sistem
 */

echo "=== PATH DEBUG ===\n\n";

echo "1. REQUEST URI:\n";
echo "   " . $_SERVER['REQUEST_URI'] . "\n\n";

echo "2. DOCUMENT ROOT:\n";
echo "   " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

echo "3. SCRIPT FILENAME:\n";
echo "   " . __FILE__ . "\n\n";

echo "4. CURRENT DIR:\n";
echo "   " . __DIR__ . "\n\n";

echo "5. REALPATH __DIR__:\n";
echo "   " . realpath(__DIR__) . "\n\n";

echo "6. PHP Config Info:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Post Max Size: " . ini_get('post_max_size') . "\n";
echo "   Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n\n";

echo "7. Extension Check:\n";
echo "   mod_rewrite: " . (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? '✓ ENABLED' : '? UNKNOWN') . "\n";
echo "   MySQL/PDO: " . (extension_loaded('pdo_mysql') ? '✓ ENABLED' : '✗ DISABLED') . "\n";
echo "   JSON: " . (extension_loaded('json') ? '✓ ENABLED' : '✗ DISABLED') . "\n\n";

echo "8. File Structure Check:\n";
$paths = [
    'api/index.php' => __DIR__ . '/api/index.php',
    'includes/config.php' => __DIR__ . '/includes/config.php',
    'includes/Database.php' => __DIR__ . '/includes/Database.php',
    'index.php' => __DIR__ . '/index.php',
    'assets/js/script.js' => __DIR__ . '/assets/js/script.js'
];

foreach ($paths as $name => $path) {
    $exists = file_exists($path) ? '✓' : '✗';
    echo "   $exists $name\n";
}

echo "\n9. Simulated API URL:\n";
$basePath = dirname($_SERVER['REQUEST_URI']);
if ($basePath === '/') $basePath = '';
echo "   Base: " . $basePath . "\n";
echo "   API Would Be: " . $basePath . "/api/authenticate\n";
?>
