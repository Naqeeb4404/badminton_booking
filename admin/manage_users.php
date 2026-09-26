<?php

session_start();

include __DIR__ . '/../config/db.php';

// Pastikan hanya admin yang boleh akses
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Dapatkan jumlah mesej untuk notifikasi badge pada sidebar
$msg_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages");
$msg_row = mysqli_fetch_assoc($msg_query);
$total_messages = $msg_row['total'];

// Statistik tambahan untuk dashboard (Contoh: Jumlah pengguna berdaftar)
$user_query = mysqli_query($conn, "SELECT COUNT(*) as total_users FROM users");
$user_row = mysqli_fetch_assoc($user_query);
$total_users = $user_row['total_users'];

?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Badminton Kampung Panji</title>

    <!-- Bootstrap 5 CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

        /* CUSTOM SCROLLBAR YANG KEMAS */
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

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02) !important;
            background: #ffffff;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand span, .sidebar .menu-label, .sidebar .sidebar-nav-link span, .sidebar .badge { display: none; }
            .main-content { margin-left: 70px; }
            .topbar { padding: 0 20px; }
            .search-form { display: none; }
        }
    </style>
    <link rel="stylesheet" href="sidebar.css?v=20260926">

    <!-- Stable shared admin shell -->
    <style>body.admin-page .sidebar, body.admin-page .main-content { transition: none !important; }</style>
</head>

<body class="admin-page">

    <!-- SIDEBAR MENU LENGKAP -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="search-form">
                <i class="fa-solid fa-search"></i>
                <input type="text" class="form-control search-input" placeholder="Type to search..." autocomplete="off">
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="user-pill">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                    <div class="fw-bold fs-7 pe-2"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </header>

        <!-- ISI KANDUNGAN UTAMA DASHBOARD -->
        <div class="content-body">
            
            <div class="row g-4 mb-4">
                <!-- Kad Ringkasan 1 -->
                <div class="col-md-4">
                    <div class="card p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fs-7 fw-bold">Jumlah Pengguna</p>
                                <h3 class="fw-bold mb-0"><?php echo $total_users; ?></h3>
                            </div>
                            <div class="bg-light p-3 rounded-4 text-primary fs-4">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Kad Ringkasan 2 -->
                <div class="col-md-4">
                    <div class="card p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fs-7 fw-bold">Mesej Masuk</p>
                                <h3 class="fw-bold mb-0"><?php echo $total_messages; ?></h3>
                            </div>
                            <div class="bg-light p-3 rounded-4 text-success fs-4">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="fw-bold mb-1">📊 Dashboard Utama</h2>
                    <p class="text-muted fs-7 mb-4">Selamat datang ke panel pentadbir Badminton Kampung Panji.</p>
                    <hr class="text-muted opacity-25 mb-4">
                    <p class="text-dark">Sila pilih menu di bahagian sidebar sebelah kiri untuk menguruskan pengguna, tempahan gelanggang, atau semakan mesej.</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>