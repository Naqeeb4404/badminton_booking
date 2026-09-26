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

<!DOCTYPE html><html lang="ms"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Laporan Kewangan | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link rel="stylesheet" href="sidebar.css">
<style>.revenue-page .container-box{max-width:1200px;margin:0 auto;padding:0}.revenue-page .page-title{font-weight:700}.revenue-page .card{border:none;border-radius:16px;box-shadow:0 5px 20px rgba(0,0,0,.06)}.revenue-page .filter-card{padding:20px;margin-bottom:25px}.revenue-page .revenue-card{padding:25px;height:100%}.revenue-page .revenue-label{color:#6c757d;font-size:14px;font-weight:600;text-transform:uppercase}.revenue-page .revenue-amount{font-size:32px;font-weight:700;margin-top:8px;color:#1e293b}</style>
</head><body class="admin-page revenue-page">
<?php include __DIR__ . '/sidebar.php'; ?><div class="main-content"><header class="topbar"><div class="search-form"><i class="fa-solid fa-search"></i><input type="text" class="form-control search-input" placeholder="Taip untuk cari..." autocomplete="off"></div><div class="d-flex align-items-center gap-3"><div class="user-pill"><div class="user-avatar"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user']['name'] ?? 'A',0,1))); ?></div><div class="fw-bold fs-7 pe-2"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin'); ?></div></div><a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3"><i class="fa-solid fa-right-from-bracket me-1"></i> Log Keluar</a></div></header><main class="content-body"><div class="container-box">

    <!-- PAGE TITLE -->
    <div class="mb-4">
        <h2 class="page-title"><i class="fa-solid fa-chart-line text-warning"></i> Laporan Pendapatan (Revenue)</h2>
        <p class="text-muted">Semak jumlah pendapatan kutipan pembayaran (Approved) secara harian, bulanan, dan tahunan.</p>
    </div>

    <!-- FILTER SECTION -->
    <div class="card filter-card">
        <form method="GET" class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Pilih Tarikh Harian</label>
                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Pilih Bulan</label>
                <input type="month" name="month" class="form-control" value="<?php echo htmlspecialchars($selected_month); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Pilih Tahun</label>
                <select name="year" class="form-select">
                    <?php 
                    $current_yr = date('Y');
                    for ($y = $current_yr; $y >= $current_yr - 5; $y--) {
                        $selected = ($y == $selected_year) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-warning w-100 text-white fw-bold"><i class="fa-solid fa-filter"></i></button>
            </div>
        </form>
    </div>

    <!-- REVENUE STATS CARDS -->
    <div class="row g-4">
        <!-- 1. JUMLAH HARIAN -->
        <div class="col-md-4">
            <div class="card revenue-card border-start border-primary border-4">
                <div class="revenue-label">Pendapatan Harian</div>
                <div class="text-muted small mb-2"><i class="fa-regular fa-calendar"></i> <?php echo $date_name; ?></div>
                <div class="revenue-amount text-primary">RM <?php echo number_format($total_daily_revenue, 2); ?></div>
                <div class="mt-2 text-muted small"><i class="fa-solid fa-receipt"></i> <?php echo $total_daily_count; ?> transaksi berjaya</div>
            </div>
        </div>

        <!-- 2. JUMLAH BULANAN -->
        <div class="col-md-4">
            <div class="card revenue-card border-start border-success border-4">
                <div class="revenue-label">Pendapatan Bulanan</div>
                <div class="text-muted small mb-2"><i class="fa-regular fa-calendar-days"></i> <?php echo $month_name; ?></div>
                <div class="revenue-amount text-success">RM <?php echo number_format($total_monthly_revenue, 2); ?></div>
                <div class="mt-2 text-muted small"><i class="fa-solid fa-receipt"></i> <?php echo $total_monthly_count; ?> transaksi berjaya</div>
            </div>
        </div>

        <!-- 3. JUMLAH TAHUNAN -->
        <div class="col-md-4">
            <div class="card revenue-card border-start border-warning border-4">
                <div class="revenue-label">Pendapatan Keseluruhan Tahun</div>
                <div class="text-muted small mb-2"><i class="fa-solid fa-calendar-check"></i> Tahun <?php echo $selected_year; ?></div>
                <div class="revenue-amount text-warning">RM <?php echo number_format($total_yearly_revenue, 2); ?></div>
                <div class="mt-2 text-muted small"><i class="fa-solid fa-receipt"></i> <?php echo $total_yearly_count; ?> transaksi berjaya</div>
            </div>
        </div>
    </div>

</div></main></div></body></html>
