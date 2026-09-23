<?php

// Make mysqli throw exceptions on error instead of returning false silently.
// This surfaces query/connection bugs immediately instead of causing
// "Trying to access array offset on false" style failures further down.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect("localhost", "root", "", "badminton_booking");
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
