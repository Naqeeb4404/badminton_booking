<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$reportDate = $_GET['date'] ?? date('Y-m-d');

// Statistik Harian (Menggunakan LEFT JOIN payments untuk total_revenue yang tepat)
$summaryQuery = $conn->prepare("SELECT 
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'Approved' THEN 1 ELSE 0 END) as approved_bookings,
    SUM(CASE WHEN b.status = 'Pending' THEN 1 ELSE 0 END) as pending_bookings,
    SUM(CASE WHEN b.status = 'Rejected' THEN 1 ELSE 0 END) as rejected_bookings,
    SUM(CASE WHEN b.status = 'Approved' THEN COALESCE(p.amount, 0) ELSE 0 END) as total_revenue,
    COUNT(DISTINCT b.user_id) as total_customers
    FROM bookings b 
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.booking_date = ?");
$summaryQuery->bind_param("s", $reportDate);
$summaryQuery->execute();
$stats = $summaryQuery->get_result()->fetch_assoc();

// Penggunaan Gelanggang (Court Usage) pada tarikh tersebut
$courtUsageQuery = $conn->prepare("SELECT c.court_name, COUNT(b.id) as count_booked 
    FROM courts c 
    LEFT JOIN bookings b ON c.id = b.court_id AND b.booking_date = ? AND b.status = 'Approved'
    GROUP BY c.id, c.court_name");
$courtUsageQuery->bind_param("s", $reportDate);
$courtUsageQuery->execute();
$courtUsageResult = $courtUsageQuery->get_result();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f5f6fa;
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 20px;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Laporan Harian Tempahan</h2>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" value="<?= htmlspecialchars($reportDate) ?>" class="form-control form-control-sm">
                <button type="submit" class="btn btn-primary btn-sm">Papar Laporan</button>
            </form>
        </div>

        <!-- Kad Ringkasan Laporan -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <span class="text-muted small">Jumlah Tempahan</span>
                    <h4 class="fw-bold mb-0"><?= $stats['total_bookings'] ?? 0 ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 border-start border-success border-4">
                    <span class="text-muted small">Tempahan Diluluskan</span>
                    <h4 class="fw-bold text-success mb-0"><?= $stats['approved_bookings'] ?? 0 ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 border-start border-danger border-4">
                    <span class="text-muted small">Pendapatan Harian</span>
                    <h4 class="fw-bold text-danger mb-0">RM <?= number_format($stats['total_revenue'] ?? 0, 2) ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <span class="text-muted small">Dalam Proses (Pending)</span>
                    <h4 class="fw-bold text-warning mb-0"><?= $stats['pending_bookings'] ?? 0 ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <span class="text-muted small">Ditolak</span>
                    <h4 class="fw-bold text-secondary mb-0"><?= $stats['rejected_bookings'] ?? 0 ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <span class="text-muted small">Bilangan Pelanggan Unik</span>
                    <h4 class="fw-bold text-info mb-0"><?= $stats['total_customers'] ?? 0 ?></h4>
                </div>
            </div>
        </div>

        <!-- Jadual Penggunaan Gelanggang -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Status Penggunaan Gelanggang pada <?= htmlspecialchars($reportDate) ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Gelanggang</th>
                                <th>Jumlah Tempahan Diluluskan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($c = $courtUsageResult->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($c['court_name']) ?></td>
                                <td><?= $c['count_booked'] ?> sesi</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>