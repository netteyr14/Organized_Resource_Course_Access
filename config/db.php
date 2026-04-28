<?php
// ============================================================
//  config/db.php  —  Database connection (PDO, ENV-ready)
// ============================================================

// Load from Render environment variables
$host    = getenv('DB_HOST');
$dbname  = getenv('DB_NAME');
$user    = getenv('DB_USER');
$pass    = getenv('DB_PASS');
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$ssl_ca  = getenv('DB_SSL_CA'); // optional but needed for Aiven

// Build DSN
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,

    // SSL for Aiven
    PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
];

// Add SSL for Aiven if provided
if ($ssl_ca) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = $ssl_ca;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die(json_encode(['error' => 'Database connection failed']));
}