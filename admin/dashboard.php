<?php
session_start();
include __DIR__ . '/../config/db.php';

// Periksa kebenaran akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];
$searchTerm = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
?>
<?php
$today = date('Y-m-d');
$todayStmt = $conn->prepare("SELECT COUNT(*) AS total FROM bookings WHERE booking_date=? AND status IN ('Pending','Approved')");
$todayStmt->bind_param("s", $today);
$todayStmt->execute();
$todayBookings = (int)$todayStmt->get_result()->fetch_assoc()['total'];
$todayStmt->close();

$courtRow = $conn->query("SELECT COUNT(*) AS total FROM courts WHERE status='Available'")->fetch_assoc();
$availableCourts = (int)$courtRow['total'];

$salesStmt = $conn->prepare("SELECT COALESCE(SUM(p.amount),0) AS total FROM payments p JOIN bookings b ON p.booking_id=b.id WHERE b.booking_date=? AND b.status='Approved' AND p.status='Approved'");
$salesStmt->bind_param("s", $today);
$salesStmt->execute();
$todaySales = (float)$salesStmt->get_result()->fetch_assoc()['total'];
$salesStmt->close();

$userRow = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='user'")->fetch_assoc();
$totalUsers = (int)$userRow['total'];

$pendingRow = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status='Pending'")->fetch_assoc();
$pendingBookings = (int)$pendingRow['total'];
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Badminton Kampung Panji</title>

    <!-- Bootstrap 5 CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-bg: #1c2434;
            --sidebar-text: #dee4ee;
            --sidebar-hover: #333a48;
            --accent-lime: #ccff00;
            --text-dark: #111111;
            --body-bg: #f1f5f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* SIDEBAR STYLING */
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand {
            padding: 25px 20px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        .sidebar-menu {
            padding: 20px 15px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .menu-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8a99ad;
            margin-bottom: 10px;
            padding-left: 10px;
            font-weight: 700;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }

        .sidebar-nav-link-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav-link:hover, .sidebar-nav-link.active {
            background-color: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* MAIN CONTENT AREA */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* TOPBAR STYLING */
        .topbar {
            height: 80px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .search-form {
            position: relative;
            width: 350px;
        }

        .search-input {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 50px !important;
            padding: 10px 20px 10px 45px !important;
            font-size: 0.85rem !important;
            width: 100% !important;
            color: #1e293b !important;
            box-shadow: none !important;
            outline: none !important;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: #2563eb !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        }

        .search-form i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 5;
            pointer-events: none;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            padding: 6px 16px 6px 6px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--sidebar-bg);
            color: var(--accent-lime);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.9rem;
        }

        /* CONTENT BODY & CARDS */
        .content-body {
            padding: 40px;
            flex-grow: 1;
        }

        .welcome-card {
            background: linear-gradient(135deg, #1c2434 0%, #2f3a4c 100%);
            border-radius: 20px;
            padding: 25px 30px;
            color: #fff;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(28, 36, 52, 0.1);
        }

        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            height: 100%;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .chart-box {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            margin-bottom: 30px;
            position: relative;
        }

        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .action-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 15px;
        }

        .icon-blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .icon-green { background: rgba(34, 197, 94, 0.1); color: #16a34a; }
        .icon-yellow { background: rgba(234, 179, 8, 0.1); color: #ca8a04; }
        .icon-purple { background: rgba(147, 51, 234, 0.1); color: #9333ea; }
        .icon-pink { background: rgba(236, 72, 153, 0.1); color: #ec4899; }

        .btn-card {
            width: 100%;
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            color: #fff;
            border: none;
            transition: 0.2s;
            display: block;
        }

        .btn-blue { background: #2563eb; }
        .btn-blue:hover { background: #1d4ed8; color: #fff; }
        .btn-green { background: #16a34a; }
        .btn-green:hover { background: #15803d; color: #fff; }
        .btn-yellow { background: #ca8a04; }
        .btn-yellow:hover { background: #a16207; color: #fff; }
        .btn-purple { background: #9333ea; }
        .btn-purple:hover { background: #7e22ce; color: #fff; }
        .btn-pink { background: #ec4899; }
        .btn-pink:hover { background: #db2777; color: #fff; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand span, .sidebar .menu-label, .sidebar .sidebar-nav-link span, .sidebar .badge { display: none; }
            .main-content { margin-left: 70px; }
            .topbar { padding: 0 20px; }
            .search-form { display: none; }
            .content-body { padding: 20px; }
        }
    </style>
    <link rel="stylesheet" href="sidebar.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar">
            <form action="" method="GET" class="search-form">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Taip untuk cari..." autocomplete="off" value="<?php echo $searchTerm; ?>">
            </form>

            <div class="d-flex align-items-center gap-3">
                <div class="user-pill">
                    <div class="user-avatar"><?php echo htmlspecialchars(strtoupper(substr($user['name'], 0, 1))); ?></div>
                    <div class="fw-bold fs-7 pe-2"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Log Keluar
                </a>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <main class="content-body">
            
            <!-- Welcome Banner -->
            <div class="welcome-card">
                <h2 class="fw-bold mb-1">Selamat Datang Admin, <?php echo htmlspecialchars($user['name']); ?> 👋</h2>
                <p class="text-white-50 mb-0 fs-7">Statistik Jualan Harian & Status Tempahan Badminton Court</p>
            </div>

            <!-- KAD STATISTIK -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fa-solid fa-calendar-check"></i></div>
                        <span class="text-muted fs-7">Court Ditempah (Hari Ini)</span>
                        <h3 class="fw-bold mt-1 mb-0"><?= $todayBookings ?></h3>
                        <small class="text-muted fw-semibold">Booking hari ini</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fa-solid fa-table-tennis-paddle-ball"></i></div>
                        <span class="text-muted fs-7">Court Available (Kekosongan)</span>
                        <h3 class="fw-bold mt-1 mb-0"><?= $availableCourts ?> Gelanggang</h3>
                        <small class="text-muted">Status Available</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-yellow"><i class="fa-solid fa-money-bill-wave"></i></div>
                        <span class="text-muted fs-7">Jumlah Jualan Hari Ini</span>
                        <h3 class="fw-bold mt-1 mb-0">RM <?= number_format($todaySales, 2) ?></h3>
                        <small class="text-muted">Booking yang diluluskan hari ini</small>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-purple"><i class="fa-solid fa-users"></i></div>
                        <span class="text-muted fs-7">Jumlah Pengguna Aktif</span>
                        <h3 class="fw-bold mt-1 mb-0"><?= $totalUsers ?> Ahli</h3>
                        <small class="text-muted"><?= $pendingBookings ?> Booking Pending</small>
                    </div>
                </div>
            </div>

            <!-- BAHAGIAN GRAF -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-8">
                    <div class="chart-box h-100">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i> Graf Jualan Harian (Minggu Ini)</h5>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="chart-box h-100">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie text-success me-2"></i> Status Court Hari Ini</h5>
                        <div class="chart-container">
                            <canvas id="courtStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BAHAGIAN PINTASAN UTAMA (MANAGEMENT) -->
            <h5 class="fw-bold mb-3 mt-4"><i class="fa-solid fa-bolt text-warning me-2"></i> Pengurusan Utama</h5>
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="action-card">
                        <div>
                            <div class="action-icon icon-blue mx-auto"><i class="fa-solid fa-table-tennis-paddle-ball"></i></div>
                            <h5 class="fw-bold fs-6">Manage Court</h5>
                            <p class="text-muted fs-7">Uruskan senarai, status dan maklumat gelanggang badminton.</p>
                        </div>
                        <a href="manage_court.php" class="btn-card btn-blue">Manage Court ➔</a>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="action-card">
                        <div>
                            <div class="action-icon icon-green mx-auto"><i class="fa-solid fa-calendar-check"></i></div>
                            <h5 class="fw-bold fs-6">Manage Booking</h5>
                            <p class="text-muted fs-7">Semak dan pantau tempahan slot gelanggang pengguna.</p>
                        </div>
                        <a href="manage_booking.php" class="btn-card btn-green">Manage Booking ➔</a>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="action-card">
                        <div>
                            <div class="action-icon icon-yellow mx-auto"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                            <h5 class="fw-bold fs-6">Manage Payment</h5>
                            <p class="text-muted fs-7">Semak dan sahkan transaksi bayaran pengguna.</p>
                        </div>
                        <a href="manage_payment.php" class="btn-card btn-yellow">Manage Payment ➔</a>
                    </div>
                </div>
            </div>

            <!-- BAHAGIAN MODUL TAMBAHAN (USERS, MESSAGE, PAGES) -->
            <h5 class="fw-bold mb-3 mt-4"><i class="fa-solid fa-layer-group text-info me-2"></i> Modul & Alat Sokongan</h5>
            <div class="row g-4">
                <div class="col-6 col-lg-4">
                    <div class="action-card">
                        <div>
                            <div class="action-icon icon-purple mx-auto"><i class="fa-solid fa-users-gear"></i></div>
                            <h5 class="fw-bold fs-6">Manage Users</h5>
                            <p class="text-muted fs-7">Kawalselia akaun pengguna dan hak akses sistem.</p>
                        </div>
                        <a href="manage_users.php" class="btn-card btn-purple">Manage Users ➔</a>
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <div class="action-card">
                        <div>
                            <div class="action-icon icon-pink mx-auto"><i class="fa-solid fa-comments"></i></div>
                            <h5 class="fw-bold fs-6">Mesej & Pertanyaan</h5>
                            <p class="text-muted fs-7">Balas borang maklum balas pengguna.</p>
                        </div>
                        <a href="message.php" class="btn-card btn-pink">Buka Mesej ➔</a>
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <div class="action-card">
                        <div>
                            <div class="action-icon icon-green mx-auto"><i class="fa-solid fa-folder-open"></i></div>
                            <h5 class="fw-bold fs-6">Pengurusan Halaman</h5>
                            <p class="text-muted fs-7">Urus statik kandungan sistem.</p>
                        </div>
                        <a href="../index.php" class="btn-card btn-green">Ke Pages ➔</a>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Chart 1: Sales Line Chart
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: ['Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu', 'Ahad'],
                datasets: [{
                    label: 'Jualan (RM)',
                    data: [150, 200, 180, 250, 300, 420, 380],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Chart 2: Court Status Doughnut Chart
        const ctxCourt = document.getElementById('courtStatusChart').getContext('2d');
        new Chart(ctxCourt, {
            type: 'doughnut',
            data: {
                labels: ['Court Ditempah', 'Court Available'],
                datasets: [{
                    data: [8, 4],
                    backgroundColor: ['#16a34a', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>