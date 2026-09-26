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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #1c2434;
            --sidebar-text: #dee4ee;
            --sidebar-hover: #333a48;
            --accent-lime: #ccff00;
            --text-dark: #111111;
            --body-bg: #f4f6f9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Sidebar dikekalkan asal */
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

        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Topbar diselaraskan ikut gaya gambar (bersih, ada ikon hamburger & info admin di kanan) */
        .topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle-btn {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #334155;
            cursor: pointer;
        }

        .admin-dropdown-text {
            font-weight: 600;
            font-size: 0.9rem;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .notification-badge-icon {
            position: relative;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            text-decoration: none;
        }

        .notification-badge-icon .badge-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f59e0b;
            color: white;
            font-size: 0.65rem;
            padding: 2px 5px;
            border-radius: 50px;
            font-weight: bold;
        }

        .content-body {
            padding: 30px;
            flex-grow: 1;
        }

        /* Breadcrumb ala gaya rujukan gambar */
        .breadcrumb-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .breadcrumb-custom {
            font-size: 0.85rem;
            color: #64748b;
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-custom a {
            color: #334155;
            text-decoration: none;
        }

        /* Card & Kotak Borang */
        .card-custom {
            border: none;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background: #ffffff;
            margin-bottom: 25px;
        }

        .card-header-custom {
            background-color: #1e293b;
            color: white;
            font-weight: 600;
            padding: 12px 20px;
            border-top-left-radius: 8px !important;
            border-top-right-radius: 8px !important;
            font-size: 0.95rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .gray-box {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        /* Gaya Tabel ala rujukan */
        .table-custom {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table-custom thead th {
            background-color: #1e293b !important;
            color: #ffffff !important;
            font-weight: 600;
            text-align: center;
            border: none;
            padding: 12px 10px;
        }

        .table-custom tbody td {
            vertical-align: middle;
            text-align: center;
            padding: 12px 10px;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Tombol aksi khusus menyerupai rujukan */
        .btn-action-view {
            background-color: #0d6efd;
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            border: none;
        }

        .btn-action-print {
            background-color: #198754;
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            border: none;
        }

        .btn-action-history {
            background-color: #fd7e14;
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            border: none;
        }

        .btn-action-delete {
            background-color: #dc3545;
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            border: none;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .main-content { margin-left: 70px; }
        }
    </style>
    <link rel="stylesheet" href="sidebar.css?v=20260926">
</head>

<body>

    <!-- SIDEBAR MENU (DIKEKALKAN ASAL TANPA DIUBAH) -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="main-content">
        
        <!-- TOPBAR DISELARASKAN IKUT RUJUKAN GAMBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle-btn">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Ikon Notifikasi dengan badge angka -->
                <a href="messages.php" class="notification-badge-icon">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge-count"><?php echo (int)$total_messages; ?></span>
                </a>

                <!-- Info Admin di Kanan Atas -->
                <div class="dropdown">
                    <a href="#" class="admin-dropdown-text dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-circle fs-5"></i> 
                        <span><?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?> (Admin)</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ISI KANDUNGAN UTAMA -->
        <div class="content-body">
            
            <!-- Breadcrumb Header -->
            <div class="breadcrumb-area">
                <h1 class="page-title">Manage Badminton Court</h1>
                <div class="breadcrumb-custom">
                    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Home</a> &gt; <span>Manage Court</span>
                </div>
            </div>

            <?php if ($editCourt): ?>
            <!-- KOTAK EDIT GELANGGANG -->
            <div class="gray-box">
                <h5 class="fw-bold mb-3 text-secondary fs-6"><i class="fa-solid fa-pen me-1"></i> Edit Court #<?php echo (int)$editCourt['id']; ?></h5>
                <form method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo (int)$editCourt['id']; ?>">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7 text-muted">Nama Gelanggang</label>
                        <input type="text" name="court_name" class="form-control" value="<?php echo htmlspecialchars($editCourt['court_name']); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold fs-7 text-muted">Price (RM/hr)</label>
                        <input type="number" name="price" step="0.01" min="0" class="form-control" value="<?php echo htmlspecialchars($editCourt['price']); ?>" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button name="edit" value="1" class="btn btn-dark w-100 fw-semibold">
                            <i class="fa-solid fa-check me-1"></i> Save
                        </button>
                        <a href="manage_court.php" class="btn btn-secondary w-50">Cancel</a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <!-- KOTAK TAMBAH GELANGGANG BAHARU -->
            <div class="gray-box">
                <h5 class="fw-bold mb-3 text-secondary fs-6"><i class="fa-solid fa-circle-plus me-1"></i> Tambah Gelanggang Baharu</h5>
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7 text-muted">Nama Gelanggang</label>
                        <input type="text" name="court_name" class="form-control" placeholder="Example: Court 5" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold fs-7 text-muted">Price (RM/hr)</label>
                        <input type="number" name="price" step="0.01" min="0" class="form-control" placeholder="20.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold fs-7 text-muted">Status Awal</label>
                        <select name="status" class="form-select">
                            <option value="Available">Available</option>
                            <option value="Unavailable">Not Available</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button name="add" class="btn btn-dark w-100 fw-semibold">
                            <i class="fa-solid fa-plus me-1"></i> Add Court
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- SENARAI GELANGGANG (TABLE) -->
            <div class="card card-custom">
                <div class="card-header-custom">
                    <span><i class="fa-solid fa-list-ul me-2"></i> Court List</span>
                </div>
                <div class="card-body p-0">
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
                                <?php 
                                $counter = 1;
                                while ($row = mysqli_fetch_assoc($result)) { 
                                ?>
                                <tr>
                                    <td class="fw-semibold text-muted"><?php echo $counter++; ?></td>
                                    <td class="fw-bold text-dark">🏸 <?php echo htmlspecialchars($row['court_name']); ?></td>
                                    <td>RM <?php echo number_format((float)$row['price'], 2); ?></td>
                                    <td>
                                        <?php
                                        if ($row['status'] == "Available") {
                                            echo "<span class='badge bg-success px-2 py-1'>Available</span>";
                                        } else {
                                            echo "<span class='badge bg-danger px-2 py-1'>Not Available</span>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            <a href="?edit_id=<?php echo (int)$row['id']; ?>" class="btn btn-action-view" title="Edit">
                                                Edit
                                            </a>
                                            <?php if ($row['status'] == 'Available'): ?>
                                                <a href="?status=Not Available&id=<?php echo (int)$row['id']; ?>" class="btn btn-action-history" title="Disable">
                                                    Disable
                                                </a>
                                            <?php else: ?>
                                                <a href="?status=Available&id=<?php echo (int)$row['id']; ?>" class="btn btn-action-print" title="Available">
                                                    Available
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo (int)$row['id']; ?>" class="btn btn-action-delete" onclick="return confirm('Delete court?')" title="Delete">
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>