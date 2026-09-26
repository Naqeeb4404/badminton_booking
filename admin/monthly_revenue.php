<?php
session_start();
include "../config/db.php";

// Admin sahaja
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// 1. Filter Bulan (untuk Monthly & Daily)
$selected_month = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date('Y-m');
}
$year = (int) substr($selected_month, 0, 4);
$month = (int) substr($selected_month, 5, 2);

// 2. Filter Tahun (untuk Yearly Revenue)
$selected_year = $_GET['year'] ?? date('Y');
if (!preg_match('/^\d{4}$/', $selected_year)) {
    $selected_year = date('Y');
}
$selected_year = (int) $selected_year;

// --- A. JUMLAH BULANAN (MONTHLY REVENUE) ---
$revenue_sql = "
    SELECT COALESCE(SUM(amount), 0) AS total_revenue, COUNT(*) AS total_payment
    FROM payments
    WHERE status = 'Approved'
    AND YEAR(payment_date) = ?
    AND MONTH(payment_date) = ?
";
$stmt = mysqli_prepare($conn, $revenue_sql);
mysqli_stmt_bind_param($stmt, "ii", $year, $month);
mysqli_stmt_execute($stmt);
$monthly_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$total_monthly_revenue = (float) ($monthly_data['total_revenue'] ?? 0);
$total_monthly_count = (int) ($monthly_data['total_payment'] ?? 0);
mysqli_stmt_close($stmt);


// --- B. JUMLAH HARIAN (DAILY REVENUE - Hari ini / Tarikh Dipilih) ---
$selected_date = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}

$daily_sql = "
    SELECT COALESCE(SUM(amount), 0) AS total_revenue, COUNT(*) AS total_payment
    FROM payments
    WHERE status = 'Approved'
    AND DATE(payment_date) = ?
";
$stmt = mysqli_prepare($conn, $daily_sql);
mysqli_stmt_bind_param($stmt, "s", $selected_date);
mysqli_stmt_execute($stmt);
$daily_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$total_daily_revenue = (float) ($daily_data['total_revenue'] ?? 0);
$total_daily_count = (int) ($daily_data['total_payment'] ?? 0);
mysqli_stmt_close($stmt);


// --- C. JUMLAH KESELURUHAN TAHUN (YEARLY REVENUE) ---
$yearly_sql = "
    SELECT COALESCE(SUM(amount), 0) AS total_revenue, COUNT(*) AS total_payment
    FROM payments
    WHERE status = 'Approved'
    AND YEAR(payment_date) = ?
";
$stmt = mysqli_prepare($conn, $yearly_sql);
mysqli_stmt_bind_param($stmt, "i", $selected_year);
mysqli_stmt_execute($stmt);
$yearly_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$total_yearly_revenue = (float) ($yearly_data['total_revenue'] ?? 0);
$total_yearly_count = (int) ($yearly_data['total_payment'] ?? 0);
mysqli_stmt_close($stmt);

// Nama bulan untuk paparan
$month_name = date("F Y", strtotime($selected_month . "-01"));
$date_name = date("d M Y", strtotime($selected_date));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Report - Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
</head>

<body class="admin-report-page">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="report-content">

            <div class="report-header">
                <h2><i class="fa-solid fa-chart-line text-warning me-2"></i>Revenue Report</h2>
                <p>Semak pendapatan daripada pembayaran yang telah diluluskan secara harian, bulanan dan tahunan.</p>
            </div>

            <div class="card report-card report-filter">
                <form method="GET" class="row align-items-end g-3">

                    <div class="col-xl-4 col-md-6">
                        <label for="revenue-date" class="form-label fw-bold">Pilih Tarikh Harian</label>
                        <input id="revenue-date" type="date" name="date"
                               class="form-control"
                               value="<?= htmlspecialchars($selected_date) ?>">
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <label for="revenue-month" class="form-label fw-bold">Pilih Bulan</label>
                        <input id="revenue-month" type="month" name="month"
                               class="form-control"
                               value="<?= htmlspecialchars($selected_month) ?>">
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label for="revenue-year" class="form-label fw-bold">Pilih Tahun</label>
                        <select id="revenue-year" name="year" class="form-select">
                            <?php
                            $current_yr = date('Y');
                            for ($y = $current_yr; $y >= $current_yr - 5; $y--) {
                                $selected = ($y == $selected_year) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-xl-1 col-md-6">
                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="fa-solid fa-filter"></i>
                            <span class="d-xl-none ms-1">Filter</span>
                        </button>
                    </div>

                </form>
            </div>

            <div class="row g-4">

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100 border-start border-primary border-4">
                        <div class="text-muted small fw-semibold text-uppercase">Pendapatan Harian</div>
                        <div class="text-muted small mt-2">
                            <i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars($date_name) ?>
                        </div>
                        <div class="fs-2 fw-bold text-primary mt-2">
                            RM <?= number_format($total_daily_revenue, 2) ?>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fa-solid fa-receipt me-1"></i>
                            <?= $total_daily_count ?> transaksi berjaya
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100 border-start border-success border-4">
                        <div class="text-muted small fw-semibold text-uppercase">Pendapatan Bulanan</div>
                        <div class="text-muted small mt-2">
                            <i class="fa-regular fa-calendar-days me-1"></i><?= htmlspecialchars($month_name) ?>
                        </div>
                        <div class="fs-2 fw-bold text-success mt-2">
                            RM <?= number_format($total_monthly_revenue, 2) ?>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fa-solid fa-receipt me-1"></i>
                            <?= $total_monthly_count ?> transaksi berjaya
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100 border-start border-warning border-4">
                        <div class="text-muted small fw-semibold text-uppercase">Pendapatan Tahunan</div>
                        <div class="text-muted small mt-2">
                            <i class="fa-solid fa-calendar-check me-1"></i>
                            Tahun <?= (int)$selected_year ?>
                        </div>
                        <div class="fs-2 fw-bold text-warning mt-2">
                            RM <?= number_format($total_yearly_revenue, 2) ?>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fa-solid fa-receipt me-1"></i>
                            <?= $total_yearly_count ?> transaksi berjaya
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
