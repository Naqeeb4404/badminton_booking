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
    <?php 
    // Shared Admin Sidebar tanpa 'Filter Bookings'
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_admin = $admin ?? $_SESSION['user'] ?? ['name' => 'Admin', 'phone' => 'Admin Panel', 'profile_pic' => ''];

    $admin_sidebar_items = [
        ['file' => 'dashboard.php',       'icon' => 'fa-chart-pie',            'label' => 'Dashboard'],
        ['file' => 'calendar.php',        'icon' => 'fa-calendar-days',        'label' => 'Calendar'],
        ['file' => 'profile.php',         'icon' => 'fa-user-gear',            'label' => 'Profile'],
        ['file' => 'manage_booking.php', 'icon' => 'fa-book',                  'label' => 'Bookings'],
        ['file' => 'manage_court.php',   'icon' => 'fa-table-tennis-paddle-ball', 'label' => 'Courts'],
        ['file' => 'manage_users.php',   'icon' => 'fa-users',                'label' => 'Users'],
    ];
    ?>
    <div class="sidebar">
        <!-- Bahagian Logo / Jenama -->
        <a href="dashboard.php" class="sidebar-brand" style="display: flex; align-items: center; gap: 12px; padding: 22px 20px; text-decoration: none; color: #ffffff;">
            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                <i class="fa-solid fa-shuttlecock" style="color: #fff; font-size: 0.9rem;"></i>
            </div>
            <span style="font-weight: 700; font-size: 1.05rem; letter-spacing: 0.5px; background: linear-gradient(90deg, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Kampung Panji</span>
        </a>

        <!-- Kad Profil Pengguna -->
        <div class="sidebar-profile-card" style="margin: 0 15px 18px 15px; padding: 14px; background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9)); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(37, 99, 235, 0.15); border-radius: 50%; filter: blur(20px);"></div>
            <div style="display: flex; align-items: center; gap: 12px; overflow: hidden; z-index: 1;">
                <div style="position: relative; flex-shrink: 0;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #0f172a; border: 2px solid rgba(255, 255, 255, 0.15); background-image: url('<?php echo !empty($current_admin['profile_pic']) ? '../uploads/'.htmlspecialchars($current_admin['profile_pic'], ENT_QUOTES, 'UTF-8') : ''; ?>'); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.85rem;">
                        <?php echo empty($current_admin['profile_pic']) ? htmlspecialchars(strtoupper(substr($current_admin['name'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8') : ''; ?>
                    </div>
                    <span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background-color: #22c55e; border: 2px solid #0f172a; border-radius: 50%;"></span>
                </div>
                <div style="overflow: hidden;">
                    <h4 style="margin: 0 0 2px 0; font-size: 0.88rem; font-weight: 600; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($current_admin['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></h4>
                    <span style="font-size: 0.72rem; color: #94a3b8; display: flex; align-items: center; gap: 4px;"><i class="fa-solid fa-shield-halved" style="font-size: 0.6rem; color: #38bdf8;"></i> Super Admin</span>
                </div>
            </div>
            <a href="profile.php" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; color: #94a3b8; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; z-index: 1;" title="Tetapan Profil">
                <i class="fa-solid fa-gear"></i>
            </a>
        </div>

        <div class="sidebar-menu" style="padding: 0 10px;">
            <div class="menu-label" style="padding: 0 10px 8px 10px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700;">Menu Utama</div>

            <?php foreach ($admin_sidebar_items as $item): ?>
                <a href="<?php echo htmlspecialchars($item['file']); ?>"
                   class="sidebar-nav-link<?php echo $current_page === $item['file'] ? ' active' : ''; ?>">
                    <div class="sidebar-nav-link-content">
                        <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        <span><?php echo htmlspecialchars($item['label']); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>

            <div class="menu-label mt-3" style="padding: 15px 10px 8px 10px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700;">Sokongan (Support)</div>

            <a href="message.php" class="sidebar-nav-link<?php echo $current_page === 'message.php' ? ' active' : ''; ?>">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-comments"></i>
                    <span>Message</span>
                </div>
            </a>

            <a href="manage_payment.php" class="sidebar-nav-link<?php echo $current_page === 'manage_payment.php' ? ' active' : ''; ?>">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Invoice / Payments</span>
                </div>
            </a>

            <a href="monthly_revenue.php" class="sidebar-nav-link<?php echo $current_page === 'monthly_revenue.php' ? ' active' : ''; ?>">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Revenue Report</span>
                </div>
            </a>

            <a href="daily_report.php" class="sidebar-nav-link<?php echo $current_page === 'daily_report.php' ? ' active' : ''; ?>">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>Daily Report</span>
                </div>
            </a>
        </div>
    </div>

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