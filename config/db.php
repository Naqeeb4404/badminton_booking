<?php

// Make mysqli throw exceptions on error instead of returning false silently.
// This surfaces query/connection bugs immediately instead of causing
// "Trying to access array offset on false" style failures further down.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Reads from environment variables, with local defaults as a fallback
// so this still works out-of-the-box for local development.
$db_host = getenv("DB_HOST") ?: "localhost";
$db_port = getenv("DB_PORT") ?: 3306;
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASS") ?: "";
$db_name = getenv("DB_NAME") ?: "badminton_booking";

// Path to the CA certificate for SSL/TLS connections (required by managed
// providers like Aiven). Download it from your Aiven service's Overview
// page ("CA certificate") and save it as config/ca.pem. Left unset for
// local development, where plain (non-SSL) MySQL is normal.
$db_ssl_ca = getenv("DB_SSL_CA") ?: null;

try {
    $conn = mysqli_init();

    if ($db_ssl_ca) {
        $conn->ssl_set(null, null, $db_ssl_ca, null, null);
        $conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port, null, MYSQLI_CLIENT_SSL);
    } else {
        $conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
    }

    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
