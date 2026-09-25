```php
<?php
session_start();
include "../config/db.php";

// Admin sahaja
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Bulan dipilih
$selected_month = $_GET['month'] ?? date('Y-m');

// Pastikan format YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date('Y-m');
}

// Pecahkan tahun dan bulan
$year = (int) substr($selected_month, 0, 4);
$month = (int) substr($selected_month, 5, 2);

// Jumlah revenue
$revenue_sql = "
    SELECT COALESCE(SUM(amount), 0) AS total_revenue
    FROM payments
    WHERE status = 'Approved'
    AND YEAR(payment_date) = ?
    AND MONTH(payment_date) = ?
";

$stmt = mysqli_prepare($conn, $revenue_sql);
mysqli_stmt_bind_param($stmt, "ii", $year, $month);
mysqli_stmt_execute($stmt);

$revenue_result = mysqli_stmt_get_result($stmt);
$revenue_data = mysqli_fetch_assoc($revenue_result);

$total_revenue = (float) ($revenue_data['total_revenue'] ?? 0);

mysqli_stmt_close($stmt);

// Jumlah payment
$count_sql = "
    SELECT COUNT(*) AS total_payment
    FROM payments
    WHERE status = 'Approved'
    AND YEAR(payment_date) = ?
    AND MONTH(payment_date) = ?
";

$stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($stmt, "ii", $year, $month);
mysqli_stmt_execute($stmt);

$count_result = mysqli_stmt_get_result($stmt);
$count_data = mysqli_fetch_assoc($count_result);

$total_payment = (int) ($count_data['total_payment'] ?? 0);

mysqli_stmt_close($stmt);

// Nama bulan
$month_name = date("F Y", strtotime($selected_month . "-01"));
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Monthly Revenue | Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f6fa;
            font-family: Arial, sans-serif;
        }

        .container-box {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-title {
            font-weight: 700;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .filter-card {
            padding: 25px;
        }

        .revenue-card {
            padding: 35px;
            margin-top: 25px;
        }

        .revenue-label {
            color: #6c757d;
            font-size: 15px;
        }

        .revenue-amount {
            font-size: 42px;
            font-weight: 700;
            margin-top: 8px;
        }

        .info-card {
            padding: 25px;
            margin-top: 25px;
        }

    </style>

</head>

<body>

<div class="container-box">

    <!-- PAGE TITLE -->

    <div class="mb-4">

        <h2 class="page-title">
            Monthly Revenue
        </h2>

        <p class="text-muted">
            View total approved payment revenue by month.
        </p>

    </div>


    <!-- MONTH FILTER -->

    <div class="card filter-card">

        <form method="GET">

            <div class="row align-items-end g-3">

                <div class="col-md-6">

                    <label class="form-label">
                        Select Month
                    </label>
```
