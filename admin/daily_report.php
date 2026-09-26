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
    <title>Daily Report - Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
</head>

<body class="admin-report-page">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="report-content">

            <div class="report-header d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div>
                    <h2><i class="fa-solid fa-calendar-day me-2"></i>Daily Report</h2>
                    <p>Ringkasan tempahan, pelanggan dan pendapatan untuk tarikh yang dipilih.</p>
                </div>

                <form method="GET" class="d-flex gap-2 align-items-end">
                    <div>
                        <label for="report-date" class="form-label fw-semibold mb-1">Tarikh</label>
                        <input id="report-date" type="date" name="date"
                               value="<?= htmlspecialchars($reportDate) ?>"
                               class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i> Papar
                    </button>
                </form>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100">
                        <span class="text-muted small">Jumlah Tempahan</span>
                        <h3 class="fw-bold mb-0 mt-2"><?= (int)($stats['total_bookings'] ?? 0) ?></h3>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100 border-start border-success border-4">
                        <span class="text-muted small">Tempahan Diluluskan</span>
                        <h3 class="fw-bold text-success mb-0 mt-2"><?= (int)($stats['approved_bookings'] ?? 0) ?></h3>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100 border-start border-primary border-4">
                        <span class="text-muted small">Pendapatan Harian</span>
                        <h3 class="fw-bold text-primary mb-0 mt-2">
                            RM <?= number_format((float)($stats['total_revenue'] ?? 0), 2) ?>
                        </h3>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100">
                        <span class="text-muted small">Dalam Proses (Pending)</span>
                        <h3 class="fw-bold text-warning mb-0 mt-2"><?= (int)($stats['pending_bookings'] ?? 0) ?></h3>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100">
                        <span class="text-muted small">Ditolak</span>
                        <h3 class="fw-bold text-secondary mb-0 mt-2"><?= (int)($stats['rejected_bookings'] ?? 0) ?></h3>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card report-card p-4 h-100">
                        <span class="text-muted small">Bilangan Pelanggan Unik</span>
                        <h3 class="fw-bold text-info mb-0 mt-2"><?= (int)($stats['total_customers'] ?? 0) ?></h3>
                    </div>
                </div>
            </div>

            <div class="card report-card overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-table-list me-2"></i>
                        Penggunaan Gelanggang
                    </h5>
                    <small class="text-muted">
                        Tarikh: <?= htmlspecialchars($reportDate) ?>
                    </small>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">Nama Gelanggang</th>
                                    <th>Jumlah Tempahan Diluluskan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($c = $courtUsageResult->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-4 fw-bold"><?= htmlspecialchars($c['court_name']) ?></td>
                                        <td><?= (int)$c['count_booked'] ?> sesi</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
