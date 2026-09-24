<?php
session_start();
include __DIR__ . '/../config/db.php';

// Periksa kebenaran akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Proses Padam Pengguna (jika ada permintaan)
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    // Elakkan admin padam diri sendiri
    if ($deleteId !== (int)$user['id']) {
        $delStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delStmt->bind_param("i", $deleteId);
        $delStmt->execute();
        $delStmt->close();
    }
    header("Location: manage_users.php");
    exit();
}

// Carian dan Paparan Senarai Pengguna
if ($searchTerm !== '') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC");
    $like = "%" . $searchTerm . "%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengurusan Pengguna - Badminton Kampung Panji</title>

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

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

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

        /* CONTENT BODY & TABLES */
        .content-body {
            padding: 40px;
            flex-grow: 1;
        }

        .card-custom {
            background: #ffffff;
            border-radius: 20px;
            padding: 25px 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            margin-bottom: 30px;
        }

        .table-custom {
            vertical-align: middle;
        }

        .table-custom th {
            font-weight: 600;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px;
        }

        .table-custom td {
            padding: 15px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-role {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .badge-admin { background: rgba(147, 51, 234, 0.1); color: #9333ea; }
        .badge-user { background: rgba(37, 99, 235, 0.1); color: #2563eb; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand span, .sidebar .menu-label, .sidebar .sidebar-nav-link span { display: none; }
            .main-content { margin-left: 70px; }
            .topbar { padding: 0 20px; }
            .search-form { display: none; }
            .content-body { padding: 20px; }
        }
    </style>
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
                <input type="text" name="search" class="form-control search-input" placeholder="Cari nama, emel, telefon..." autocomplete="off" value="<?php echo htmlspecialchars($searchTerm); ?>">
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
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Pengurusan Pengguna (*Manage Users*)</h2>
                    <p class="text-muted mb-0 fs-7">Senarai keseluruhan ahli dan tetapan akses sistem.</p>
                </div>
            </div>

            <!-- TABLE KANDUNGAN PENGGUNA -->
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Nama Penuh</th>
                                <th>Emel</th>
                                <th>No. Telefon</th>
                                <th>Peranan (Role)</th>
                                <th>Tarikh Daftar</th>
                                <th class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $row['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="user-avatar" style="width:32px;height:32px;font-size:0.8rem;">
                                                    <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                                </div>
                                                <span class="fw-semibold"><?= htmlspecialchars($row['name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($row['role'] === 'admin'): ?>
                                                <span class="badge-role badge-admin">Admin</span>
                                            <?php else: ?>
                                                <span class="badge-role badge-user">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                        <td class="text-end">
                                            <?php if ($row['id'] !== $user['id']): ?>
                                                <a href="manage_users.php?delete_id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Adakah anda pasti mahu memadam pengguna ini?')">
                                                    <i class="fa-solid fa-trash me-1"></i> Padam
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted fs-7 fst-italic">Akaun Anda</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tiada rekod pengguna dijumpai.</td>
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