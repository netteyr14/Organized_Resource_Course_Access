<?php
// ============================================================
//  config/db.php — Render + Aiven compatible PDO connection
// ============================================================

// Load from Render environment variables
$host    = getenv('DB_HOST');
$port    = getenv('DB_PORT') ?: 3306;
$dbname  = getenv('DB'); // IMPORTANT: Render uses DB (not DB_NAME)
$user    = getenv('DB_USER');
$pass    = getenv('DB_PASS');
$charset = 'utf8mb4';

// Aiven SSL certificate
$ssl_ca = '/etc/secrets/ca.pem';

// Build DSN (include port)
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

// Base options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Add SSL only if provided
if (file_exists($ssl_ca)) {
    $options[PDO::MYSQL_ATTR_SSL_CA]                  = $ssl_ca;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]  = false; // Add this line
} else {
    // Helpful debug (you'll see this in error)
    error_log("SSL CA file not found at: $ssl_ca");
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die(json_encode([
        'error' => 'Database connection failed',
        'debug' => $e->getMessage()
    ]));
}