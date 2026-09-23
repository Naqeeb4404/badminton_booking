<?php

$host = 'localhost';
$port = 3306;
$database = 'badminton_booking';
$user = 'root';
$password = '';

// Sambungan terus ke MySQL Laragon tanpa SSL
$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}