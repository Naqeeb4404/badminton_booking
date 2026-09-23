<?php

// Make mysqli throw exceptions on error instead of returning false silently.
// This surfaces query/connection bugs immediately instead of causing
// "Trying to access array offset on false" style failures further down.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Reads from environment variables, with local defaults as a fallback
// so this still works out-of-the-box for local development.
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_pass = getenv("DB_PASS") ?: "";
$db_name = getenv("DB_NAME") ?: "badminton_booking";

try {
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
