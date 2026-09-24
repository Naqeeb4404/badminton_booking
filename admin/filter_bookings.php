<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil parameter penapisan
$filterDate = $_GET['date'] ?? '';
$filterMonth = $_GET['month'] ?? '';
$filterCourt = $_GET['court_id'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterUser = $_GET['user_id'] ?? '';

// Bina Query Dinamik
$query = "SELECT b.*, u.name as customer_name, c.court_name FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN courts c ON b.court_id = c.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($filterDate)) {
    $query .= " AND b.booking_date = ?";
    $params[] = $filterDate;
    $types .= "s";
}
if (!empty($filterMonth)) {
    $query .= " AND DATE_FORMAT(b.booking_date, '%Y-%m') = ?";
    $params[] = $filterMonth;
    $types .= "s";
}
if (!empty($filterCourt)) {
    $query .= " AND b.court_id = ?";
    $params[] = $filterCourt;
    $types .= "i";
    $types = "s"; // sesuaikan jika court_id string
}
if (!empty($filterStatus)) {
    $query .= " AND b.status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}
if (!empty($filterUser)) {
    $query .= " AND b.user_id = ?";
    $params[] = $filterUser;
    $types .= "i";
}

$query .= " ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Senarai dropdown untuk tapisan
$courtsList = $conn->query("SELECT * FROM courts");
$usersList = $conn->query("SELECT id, name FROM users WHERE role='customer' OR role='user'");
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Penapisan Tempahan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content p-4">
        <h2 class="mb-4">Penapisan Rekod Tempahan</h2>

        <!-- Borang Tapisan -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tarikh Tertentu</label>
                        <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Bulan</label>
                        <input type="month" name="month" value="<?= htmlspecialchars($filterMonth) ?>" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Gelanggang</label>
                        <select name="court_id" class="form-select form-select-sm">
                            <option value="">Semua Gelanggang</option>
                            <?php while($c = $courtsList->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>" <?= $filterCourt == $c['id'] ? 'selected' : '' ?>><?= $c['court_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="approved" <?= $filterStatus == 'approved' ? 'selected' : '' ?>>Diluluskan</option>
                            <option value="pending" <?= $filterStatus == 'pending' ? 'selected' : '' ?>>Dalam Proses</option>
                            <option value="rejected" <?= $filterStatus == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Pelanggan</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">Semua Pelanggan</option>
                            <?php while($u = $usersList->fetch_assoc()): ?>
                                <option value="<?= $u['id'] ?>" <?= $filterUser == $u['id'] ? 'selected' : '' ?>><?= $u['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end mt-3">
                        <a href="filter_bookings.php" class="btn btn-secondary btn-sm px-4">Reset Filter</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Cari / Tapis</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Paparan Jadual Keputusan -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Gelanggang</th>
                                <th>Tarikh</th>
                                <th>Masa</th>
                                <th>Jumlah (RM)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->id ?? $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($row['court_name']) ?></td>
                                    <td><?= $row['booking_date'] ?></td>
                                    <td><?= $row['start_time'] ?? '' ?> - <?= $row['end_time'] ?? '' ?></td>
                                    <td>RM <?= number_format($row['total_amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] == 'approved' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Tiada rekod dijumpai berdasarkan tapisan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>