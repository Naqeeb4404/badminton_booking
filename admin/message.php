<?php
session_start();

// Periksa kebenaran akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Sambungan ke pangkalan data mengikut struktur folder config/db.php
$db_path = __DIR__ . '/../config/db.php';
if (file_exists($db_path)) {
    include $db_path;
} else {
    die("Ralat: Fail pangkalan data (db.php) tidak dijumpai.");
}

if (!isset($conn) || !$conn) {
    die("Ralat: Sambungan ke pangkalan data gagal.");
}

$user = $_SESSION['user'];
$searchTerm = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Query untuk mengambil senarai mesej (menyokong carian jika ada)
if (!empty($searchTerm)) {
    $query = "SELECT * FROM messages WHERE name LIKE '%$searchTerm%' OR message LIKE '%$searchTerm%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM messages ORDER BY id DESC";
}
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesej & Pertanyaan - Admin Dashboard</title>

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
            outline: none !important;
        }

        .search-form i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
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

        /* CONTENT BODY */
        .content-body {
            padding: 40px;
            flex-grow: 1;
        }

        .card-custom {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

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

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar">
            <form action="" method="GET" class="search-form">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Cari mesej..." autocomplete="off" value="<?php echo $searchTerm; ?>">
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
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="fw-bold mb-0"><i class="fa-solid fa-comments text-primary me-2"></i> Senarai Mesej & Pertanyaan</h3>
            </div>

            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama Pengirim</th>
                                <th>Mesej</th>
                                <th>Tarikh</th>
                                <th class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                                        <td><?php echo isset($row['created_at']) ? $row['created_at'] : '-'; ?></td>
                                        <td class="text-end">
                                            <a href="mailto:<?php echo isset($row['email']) ? $row['email'] : '#'; ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="fa-solid fa-reply me-1"></i> Balas
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tiada mesej ditemui.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>