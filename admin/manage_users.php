<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$user = $_SESSION['user'];

// Search
$search = trim($_GET['search'] ?? '');

// Summary statistics (read-only)
$totalUsers = 0;
$totalAdmins = 0;
$totalBookings = 0;

$r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role <> 'admin'");
if ($r) $totalUsers = (int)(mysqli_fetch_assoc($r)['total'] ?? 0);

$r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
if ($r) $totalAdmins = (int)(mysqli_fetch_assoc($r)['total'] ?? 0);

$r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
if ($r) $totalBookings = (int)(mysqli_fetch_assoc($r)['total'] ?? 0);

// User list + booking count
$users = [];
$sql = "SELECT u.id, u.name, u.email, u.phone, u.role,
               COUNT(b.id) AS booking_count
        FROM users u
        LEFT JOIN bookings b ON b.user_id = u.id
        WHERE u.role <> 'admin'";

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
}
$sql .= " GROUP BY u.id, u.name, u.email, u.phone, u.role ORDER BY u.id DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt->bind_param('sss', $like, $like, $like);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $users[] = $row;
    $stmt->close();
}

// Selected user details (View only)
$selectedUser = null;
$selectedBookings = [];
$viewId = isset($_GET['view']) ? (int)$_GET['view'] : 0;

if ($viewId > 0) {
    $stmt = $conn->prepare("SELECT id, name, email, phone, role FROM users WHERE id = ? AND role <> 'admin' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $viewId);
        $stmt->execute();
        $selectedUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($selectedUser) {
        $stmt = $conn->prepare("SELECT b.id, b.booking_date, b.booking_time, b.duration, b.status, c.court_name
                                FROM bookings b
                                LEFT JOIN courts c ON c.id = b.court_id
                                WHERE b.user_id = ?
                                ORDER BY b.booking_date DESC, b.booking_time DESC
                                LIMIT 10");
        if ($stmt) {
            $stmt->bind_param('i', $viewId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) $selectedBookings[] = $row;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users - Badminton Kampung Panji</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css?v=20260926">
<style>
:root{--sidebar-bg:#1c2434;--sidebar-text:#dee4ee;--sidebar-hover:#333a48;--accent-lime:#ccff00;--body-bg:#f1f5f9;}
*{box-sizing:border-box}body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--body-bg);margin:0;min-height:100vh;color:#0f172a;display:flex}.main-content{margin-left:280px;flex-grow:1;min-height:100vh}.topbar{height:80px;background:#fff;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;padding:0 40px;position:sticky;top:0;z-index:99}.search-form{position:relative;width:350px}.search-input{background:#f8fafc!important;border:1px solid #e2e8f0!important;border-radius:50px!important;padding:10px 20px 10px 45px!important;font-size:.85rem!important}.search-form i{position:absolute;left:18px;top:50%;transform:translateY(-50%);color:#94a3b8}.user-pill{display:flex;align-items:center;gap:12px;background:#f8fafc;padding:6px 16px 6px 6px;border-radius:50px;border:1px solid #e2e8f0}.user-avatar{width:38px;height:38px;border-radius:50%;background:var(--sidebar-bg);color:var(--accent-lime);display:flex;align-items:center;justify-content:center;font-weight:800}.content-body{padding:40px}.page-title{font-weight:800;margin:0}.page-subtitle{color:#64748b;font-size:.9rem}.stat-card,.panel{background:#fff;border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 4px 10px rgba(15,23,42,.03)}.stat-card{padding:20px;height:100%}.stat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}.icon-blue{background:#eff6ff;color:#2563eb}.icon-purple{background:#f5f3ff;color:#7c3aed}.icon-green{background:#f0fdf4;color:#16a34a}.panel{padding:24px}.user-table{width:100%;border-collapse:collapse}.user-table th{padding:13px 12px;font-size:.72rem;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap}.user-table td{padding:15px 12px;border-bottom:1px solid #f1f5f9;font-size:.84rem;vertical-align:middle}.user-table tr:last-child td{border-bottom:0}.avatar-mini{width:38px;height:38px;border-radius:12px;background:#eef2ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-weight:800}.role-chip{display:inline-flex;padding:5px 9px;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:.7rem;font-weight:800}.booking-chip{display:inline-flex;min-width:32px;height:28px;padding:0 8px;align-items:center;justify-content:center;border-radius:999px;background:#f1f5f9;font-weight:800}.btn-view{background:rgba(37,99,235,.10);color:#2563eb;border:1px solid rgba(37,99,235,.25);font-weight:700;border-radius:9px;padding:7px 12px;text-decoration:none;display:inline-block}.btn-view:hover{background:#2563eb;color:#fff}.detail-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:14px}.detail-label{font-size:.7rem;text-transform:uppercase;color:#64748b;font-weight:800}.status{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:.7rem;font-weight:800}.approved{background:#dcfce7;color:#166534}.pending{background:#fef3c7;color:#92400e}.rejected{background:#fee2e2;color:#991b1b}.empty-state{text-align:center;padding:40px;color:#94a3b8}@media(max-width:768px){.main-content{margin-left:70px}.topbar{padding:0 20px}.topbar .search-form{display:none}.content-body{padding:20px}}
</style>
</head>
<body class="admin-page">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<header class="topbar">
    <form method="GET" class="search-form">
        <i class="fa-solid fa-search"></i>
        <input type="text" name="search" class="form-control search-input" placeholder="Cari nama, email atau telefon..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
    </form>
    <div class="d-flex align-items-center gap-3">
        <div class="user-pill">
            <div class="user-avatar" style="<?= $admin_photo_style ?>"><?= $admin_photo === '' ? htmlspecialchars($admin_initial) : '' ?></div>
            <div class="fw-bold small pe-2"><?= htmlspecialchars($current_admin['name'] ?? $user['name'] ?? 'Admin') ?></div>
        </div>
        <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
    </div>
</header>

<main class="content-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div><h2 class="page-title">Manage Users</h2><div class="page-subtitle">Lihat pengguna berdaftar dan sejarah tempahan mereka.</div></div>
        <?php if ($search !== ''): ?><a href="manage_users.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-xmark me-1"></i> Reset Search</a><?php endif; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4"><div class="stat-card d-flex align-items-center justify-content-between"><div><div class="text-muted small fw-bold text-uppercase">Total Users</div><h3 class="fw-bold mb-0 mt-1"><?= $totalUsers ?></h3></div><div class="stat-icon icon-blue"><i class="fa-solid fa-users"></i></div></div></div>
        <div class="col-12 col-md-4"><div class="stat-card d-flex align-items-center justify-content-between"><div><div class="text-muted small fw-bold text-uppercase">Total Admin</div><h3 class="fw-bold mb-0 mt-1"><?= $totalAdmins ?></h3></div><div class="stat-icon icon-purple"><i class="fa-solid fa-user-shield"></i></div></div></div>
        <div class="col-12 col-md-4"><div class="stat-card d-flex align-items-center justify-content-between"><div><div class="text-muted small fw-bold text-uppercase">Total Bookings</div><h3 class="fw-bold mb-0 mt-1"><?= $totalBookings ?></h3></div><div class="stat-icon icon-green"><i class="fa-solid fa-calendar-check"></i></div></div></div>
    </div>

    <?php if ($selectedUser): ?>
    <div class="panel mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0"><i class="fa-solid fa-user text-primary me-2"></i>User Details</h5><a href="manage_users.php<?= $search !== '' ? '?search='.urlencode($search) : '' ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Close</a></div>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="detail-box"><div class="detail-label">Name</div><div class="fw-bold mt-1"><?= htmlspecialchars($selectedUser['name']) ?></div></div></div>
            <div class="col-md-3"><div class="detail-box"><div class="detail-label">Email</div><div class="fw-semibold mt-1 text-break"><?= htmlspecialchars($selectedUser['email']) ?></div></div></div>
            <div class="col-md-3"><div class="detail-box"><div class="detail-label">Phone</div><div class="fw-semibold mt-1"><?= htmlspecialchars($selectedUser['phone'] ?: '-') ?></div></div></div>
            <div class="col-md-3"><div class="detail-box"><div class="detail-label">Role</div><div class="mt-1"><span class="role-chip"><?= htmlspecialchars(ucfirst($selectedUser['role'])) ?></span></div></div></div>
        </div>
        <h6 class="fw-bold mb-3">Latest Bookings</h6>
        <div class="table-responsive"><table class="user-table"><thead><tr><th>ID</th><th>Court</th><th>Date</th><th>Time</th><th>Duration</th><th>Status</th></tr></thead><tbody>
        <?php if ($selectedBookings): foreach ($selectedBookings as $b): $cls = strtolower($b['status']); ?>
            <tr><td>#<?= (int)$b['id'] ?></td><td><?= htmlspecialchars($b['court_name'] ?? '-') ?></td><td><?= date('d/m/Y', strtotime($b['booking_date'])) ?></td><td><?= htmlspecialchars(substr($b['booking_time'],0,5)) ?></td><td><?= (int)$b['duration'] ?> hour<?= (int)$b['duration'] > 1 ? 's' : '' ?></td><td><span class="status <?= in_array($cls,['approved','pending','rejected']) ? $cls : 'pending' ?>"><?= htmlspecialchars($b['status']) ?></span></td></tr>
        <?php endforeach; else: ?><tr><td colspan="6" class="empty-state">User ini belum mempunyai booking.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <?php endif; ?>

    <div class="panel">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0"><i class="fa-solid fa-users-gear text-primary me-2"></i>User List</h5><span class="text-muted small"><?= count($users) ?> result<?= count($users) === 1 ? '' : 's' ?></span></div>
        <div class="table-responsive"><table class="user-table"><thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Role</th><th>Bookings</th><th>Action</th></tr></thead><tbody>
        <?php if ($users): foreach ($users as $u): ?>
            <tr>
                <td><div class="d-flex align-items-center gap-2"><div class="avatar-mini"><?= htmlspecialchars(strtoupper(substr($u['name'] ?: 'U',0,1))) ?></div><div><strong><?= htmlspecialchars($u['name']) ?></strong><div class="small text-muted">ID #<?= (int)$u['id'] ?></div></div></div></td>
                <td><?= htmlspecialchars($u['email']) ?></td><td><?= htmlspecialchars($u['phone'] ?: '-') ?></td><td><span class="role-chip"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td><td><span class="booking-chip"><?= (int)$u['booking_count'] ?></span></td>
                <td><a class="btn-view" href="?view=<?= (int)$u['id'] ?><?= $search !== '' ? '&search='.urlencode($search) : '' ?>"><i class="fa-regular fa-eye me-1"></i> View</a></td>
            </tr>
        <?php endforeach; else: ?><tr><td colspan="6" class="empty-state"><i class="fa-solid fa-user-slash fs-3 d-block mb-2"></i>Tiada pengguna dijumpai.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
</main>
</div>
</body>
</html>
