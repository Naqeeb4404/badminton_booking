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

// 4. TUKAR STATUS (Available / Not Available)
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'] === 'Available' ? 'Available' : 'Not Available';

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #1e293b;
            --topbar-bg: #ffffff;
            --body-bg: #f8fafc;
            --card-border: #e2e8f0;
            --table-header-bg: #1e293b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: #334155;
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Sidebar Styling ala Rujukan */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #94a3b8;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 20px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #0f172a;
            border-bottom: 1px solid #334155;
            text-decoration: none;
        }

        .sidebar-menu {
            padding: 15px 10px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 4px;
            transition: background 0.2s;
        }

        .sidebar-nav-link:hover, .sidebar-nav-link.active {
            background-color: #334155;
            color: #fff;
        }

        .sidebar-nav-link-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Main Content & Topbar */
        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            height: 70px;
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .content-body {
            padding: 30px;
            flex-grow: 1;
        }

        /* Breadcrumb & Header Box */
        .page-header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.85rem;
        }

        /* Card & Panel Styling */
        .card-custom {
            background: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .card-custom-header {
            background: #1e293b;
            color: #fff;
            padding: 12px 20px;
            font-weight: 600;
            border-top-left-radius: 7px;
            border-top-right-radius: 7px;
            font-size: 0.95rem;
        }

        .card-custom-body {
            padding: 20px;
        }

        /* Table Styling */
        .table-custom {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table-custom th {
            background-color: #1e293b !important;
            color: #fff !important;
            font-weight: 600;
            text-align: center;
            padding: 12px 10px;
            border: none;
        }

        .table-custom td {
            vertical-align: middle;
            text-align: center;
            padding: 12px 10px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Action Buttons */
        .btn-action-view { background-color: #0dcaf0; color: #fff; border: none; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; }
        .btn-action-edit { background-color: #ffc107; color: #000; border: none; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; font-weight: 500; }
        .btn-action-success { background-color: #198754; color: #fff; border: none; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; }
        .btn-action-danger { background-color: #dc3545; color: #fff; border: none; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; }
        .btn-action-dark { background-color: #6c757d; color: #fff; border: none; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; }
        
        .btn-action-view:hover, .btn-action-edit:hover, .btn-action-success:hover, .btn-action-danger:hover, .btn-action-dark:hover {
            opacity: 0.85;
            color: #fff;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR MENU -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shuttlecock text-warning"></i>
            <span>Badminton Panji</span>
        </div>
        <div class="sidebar-menu">
            <div class="text-uppercase fs-7 text-muted fw-bold px-3 mb-2" style="font-size: 0.7rem;">Navigasi</div>
            <a href="dashboard.php" class="sidebar-nav-link">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </div>
            </a>
            <a href="manage_court.php" class="sidebar-nav-link active">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Manage Court</span>
                </div>
            </a>
            <a href="messages.php" class="sidebar-nav-link">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Messages</span>
                </div>
                <?php if($total_messages > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?php echo $total_messages; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-bars fs-5 text-secondary" style="cursor: pointer;"></i>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="position-relative text-secondary fs-5" style="cursor: pointer;">
                    <i class="fa-regular fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        <?php echo $total_messages; ?>
                    </span>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="fw-semibold fs-7">Admin, (<?php echo htmlspecialchars($user['name'] ?? 'SuperAdmin'); ?>)</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ISI KANDUNGAN UTAMA -->
        <div class="content-body">
            
            <!-- Tajuk & Breadcrumb ala Rujukan -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-header-title">Manage Courts</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Manage Court</li>
                    </ol>
                </nav>
            </div>

            <!-- KOTAK BORANG (TAMBAH / KEMASKINI) -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <i class="fa-solid fa-pen-to-square me-1"></i> 
                    <?php echo $editCourt ? 'Edit Court ID: #' . (int)$editCourt['id'] : 'Add New Court'; ?>
                </div>
                <div class="card-custom-body">
                    <?php if ($editCourt): ?>
                        <!-- Borang Kemaskini -->
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="id" value="<?php echo (int)$editCourt['id']; ?>">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold fs-7">Nama Gelanggang</label>
                                <input type="text" name="court_name" class="form-control" value="<?php echo htmlspecialchars($editCourt['court_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-7">Price (RM/hr)</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?php echo htmlspecialchars($editCourt['price']); ?>" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button name="edit" value="1" class="btn btn-dark w-100 fw-semibold">Update</button>
                                <a href="manage_court.php" class="btn btn-secondary w-100 fw-semibold">Cancel</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Borang Tambah -->
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-7">Nama Gelanggang</label>
                                <input type="text" name="court_name" class="form-control" placeholder="Contoh: Court 1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold fs-7">Price (RM/hr)</label>
                                <input type="number" name="price" step="0.01" min="0" class="form-control" placeholder="20.00" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold fs-7">Status Awal</label>
                                <select name="status" class="form-select">
                                    <option value="Available">Available</option>
                                    <option value="Not Available">Not Available</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button name="add" class="btn btn-dark w-100 fw-semibold">
                                    <i class="fa-solid fa-plus me-1"></i> Add
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KOTAK SENARAI KESELURUHAN (TABLE) -->
            <div class="card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-table-list me-1"></i> Court List</span>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Court Name</th>
                                    <th>Price (RM/hr)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td class="fw-semibold text-muted"><?php echo (int)$row['id']; ?></td>
                                    <td class="fw-bold text-start ps-4">🏸 <?php echo htmlspecialchars($row['court_name']); ?></td>
                                    <td>RM <?php echo number_format((float)$row['price'], 2); ?></td>
                                    <td>
                                        <?php if ($row['status'] == "Available") { ?>
                                            <span class="badge bg-success px-2 py-1">Available</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger px-2 py-1">Not Available</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="?edit_id=<?php echo (int)$row['id']; ?>" class="btn btn-action-edit">Edit</a>
                                            <a href="?status=Available&id=<?php echo (int)$row['id']; ?>" class="btn btn-action-success">Available</a>
                                            <a href="?status=Not Available&id=<?php echo (int)$row['id']; ?>" class="btn btn-action-dark">Disable</a>
                                            <a href="?delete=<?php echo (int)$row['id']; ?>" class="btn btn-action-danger" onclick="return confirm('Padam gelanggang ini?')">Delete</a>
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>