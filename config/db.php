```php
<?php

$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT');
$database = getenv('DB_NAME');
$user     = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

// Check database environment variables
if (!$host || !$port || !$database || !$user) {
    die("Database environment variables are not configured.");
}

$conn = mysqli_init();

/*
|--------------------------------------------------------------------------
| SSL Configuration
|--------------------------------------------------------------------------
| Only enable SSL when DB_SSL is set to "true".
| This prevents Laragon/local development from trying to use ca.pem.
*/

$db_ssl = strtolower(getenv('DB_SSL') ?: 'false');

if ($db_ssl === 'true') {

    $ca_file = getenv('DB_SSL_CA');

    if ($ca_file && file_exists($ca_file)) {

        mysqli_ssl_set(
            $conn,
            NULL,
            NULL,
            $ca_file,
            NULL,
            NULL
        );

        $flags = MYSQLI_CLIENT_SSL;

    } else {

        die("SSL is enabled but CA certificate was not found.");
    }

} else {

    $flags = 0;
}

/*
|--------------------------------------------------------------------------
| Connect Database
|--------------------------------------------------------------------------
*/

if (!mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    (int)$port,
    NULL,
    $flags
)) {
    die("Database connection failed: " . mysqli_connect_error());
}

/*
|--------------------------------------------------------------------------
| Character Set
|--------------------------------------------------------------------------
*/

mysqli_set_charset($conn, "utf8mb4");

?>
```
