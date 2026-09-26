<?php
session_start();
include __DIR__ . '/../config/db.php';

// Semak sama ada pengguna adalah admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Kira jumlah mesej untuk notifikasi
$msg_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM messages");
$msg_row = mysqli_fetch_assoc($msg_query);
$total_messages = $msg_row['total'];
mysqli_free_result($msg_query);

// 1. TAMBAH GELANGGANG (ADD COURT)
if (isset($_POST['add'])) {
    $court_name = trim($_POST['court_name'] ?? '');
    $status = ($_POST['status'] ?? '') === 'Available' ? 'Available' : 'Not Available';
    $price = (float)($_POST['price'] ?? 0);

    if ($court_name !== '') {
        $stmt = $conn->prepare("INSERT INTO courts (court_name, status, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $court_name, $status, $price);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_court.php");
    exit();
}

// 2. KEMASKINI GELANGGANG (EDIT)
if (isset($_POST['edit'])) {
    $id = (int)($_POST['id'] ?? 0);
    $court_name = trim($_POST['court_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);

    if ($id && $court_name !== '') {
        $stmt = $conn->prepare("UPDATE courts SET court_name=?, price=? WHERE id=?");
        $stmt->bind_param("sdi", $court_name, $price, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_court.php");
    exit();
}

// 3. PADAM GELANGGANG (DELETE)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM courts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_court.php");
    exit();
}

// 4. TUKAR STATUS
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];

    $allowed_statuses = ['Available', 'Unavailable', 'Disabled', 'Deleted'];

    if (!in_array($status, $allowed_statuses, true)) {
        $status = 'Available';
    }

    $stmt = $conn->prepare("UPDATE courts SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_court.php");
    exit();
}
// 5. AMBIL DATA GELANGGANG UNTUK DI-EDIT (JIKA ?edit_id WUJUD)
$editCourt = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $edit_stmt = $conn->prepare("SELECT * FROM courts WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $editCourt = $edit_result->fetch_assoc();
    $edit_stmt->close();
}

// Ambil senarai gelanggang dari database
$result = mysqli_query($conn, "SELECT * FROM courts ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Court - Badminton Kampung Panji</title>

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

        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

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

        .gray-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
        }

        .btn-minimal {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-minimal:hover {
            background-color: #e2e8f0;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-minimal-dark {
            background-color: #334155;
            border: 1px solid #334155;
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-minimal-dark:hover {
            background-color: #1e293b;
            color: #ffffff;
        }

        .table td, .table th {
            vertical-align: middle;
            padding: 14px 16px;
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

    <!-- SIDEBAR MENU -->
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
                    <div class="user-avatar" style="<?php echo $admin_photo_style; ?>"><?php echo $admin_photo === '' ? htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') : ''; ?></div>
                    <div class="fw-bold fs-7 pe-2"><?php echo htmlspecialchars($current_admin['name'] ?? $user['name'] ?? 'Admin'); ?></div>
                </div>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </header>

        <!-- ISI KANDUNGAN UTAMA -->
        <div class="content-body">
            
            <div class="card shadow">
                <div class="card-body p-4">
                    
                    <h2 class="fw-bold mb-1">🏸 Manage Badminton Court</h2>
                    <p class="text-muted fs-7 mb-4">Tambah, padam, atau kemas kini status ketersediaan gelanggang sukan.</p>
                    <hr class="text-muted opacity-25 mb-4">

                    <?php if ($editCourt): ?>
                    <!-- KOTAK EDIT GELANGGANG -->
                    <div class="gray-box mb-5">
                        <h5 class="fw-bold mb-3 text-secondary fs-6"><i class="fa-solid fa-pen me-1"></i> Edit Court #<?php echo (int)$editCourt['id']; ?></h5>
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="id" value="<?php echo (int)$editCourt['id']; ?>">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-7 text-muted">Nama Gelanggang</label>
                                <input type="text" name="court_name" class="form-control bg-white" value="<?php echo htmlspecialchars($editCourt['court_name']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold fs-7 text-muted">Price (RM/hr)</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control bg-white" value="<?php echo htmlspecialchars($editCourt['price']); ?>" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button name="edit" value="1" class="btn btn-minimal-dark w-100">
                                    <i class="fa-solid fa-check me-1"></i> Save
                                </button>
                                <a href="manage_court.php" class="btn btn-minimal">Cancel</a>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <!-- KOTAK KELABU LEMBUT UNTUK ADD COURT FORM -->
                    <div class="gray-box mb-5">
                        <h5 class="fw-bold mb-3 text-secondary fs-6"><i class="fa-solid fa-circle-plus me-1"></i> Tambah Gelanggang Baharu</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-7 text-muted">Nama Gelanggang</label>
                                <input type="text" name="court_name" class="form-control bg-white" placeholder="Example: Court 5" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold fs-7 text-muted">Price (RM/hr)</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control bg-white" placeholder="20.00" required>
                            </div>

                        <div class="col-md-3">
    <label class="form-label fw-semibold fs-7 text-muted">Status Awal</label>
    <select name="status" class="form-select bg-white">
        <option value="Available">Available</option>
        <option value="Unavailable">Unavailable</option>
    </select>
</div>

<div class="col-md-3 d-flex align-items-end">
    <button name="add" class="btn btn-minimal-dark w-100">
        <i class="fa-solid fa-plus me-1"></i> Add Court
    </button>
</div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <h4 class="fw-bold mb-3"><i class="fa-solid fa-list-ul me-2 text-dark"></i> Court List</h4>

                    <!-- TABLE KESELURUHAN -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Court Name</th>
                                    <th>Price (RM/hr)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td class="fw-semibold text-muted"><?php echo (int)$row['id']; ?></td>
                                    <td class="fw-bold">🏸 <?php echo htmlspecialchars($row['court_name']); ?></td>
                                    <td>RM <?php echo number_format((float)$row['price'], 2); ?></td>
                                    <td>
                                        <?php
                                        if ($row['status'] == "Available") {
                                            echo "<span class='badge bg-success px-3 py-2'>Available</span>";
                                        } else {
                                            echo "<span class='badge bg-danger px-3 py-2'>Not Available</span>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center flex-wrap gap-1">
                                            <a href="?edit_id=<?php echo (int)$row['id']; ?>" class="btn btn-minimal">
                                                Edit
                                            </a>
                                            <a href="?status=Available&id=<?php echo (int)$row['id']; ?>" class="btn btn-minimal">
                                                Available
                                            </a>
                                            <a href="?status=Not Available&id=<?php echo (int)$row['id']; ?>" class="btn btn-minimal">
                                                Disable
                                            </a>
                                            <a href="?delete=<?php echo (int)$row['id']; ?>" class="btn btn-minimal text-danger" onclick="return confirm('Delete court?')">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>