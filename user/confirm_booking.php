<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}

$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 0;
$courtId = isset($_GET['court_id']) ? (int)$_GET['court_id'] : 0;

if (!$date || !$time || $duration < 1 || $duration > 4 || $courtId < 1) {
    header('Location: dashboard.php?error=' . urlencode('Booking information is incomplete. Please choose your slot again.'));
    exit();
}

$stmt = mysqli_prepare($conn, 'SELECT id, court_name, price, status FROM courts WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $courtId);
mysqli_stmt_execute($stmt);
$court = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$court || $court['status'] !== 'Available') {
    header('Location: dashboard.php?error=' . urlencode('Selected court is not available.'));
    exit();
}

$start = strtotime($date . ' ' . substr($time, 0, 5));
$end = $start + ($duration * 3600);
if (!$start || $start <= time()) {
    header('Location: dashboard.php?error=' . urlencode('Selected time has already passed.'));
    exit();
}

// Check every hour in the selected duration to prevent overlapping bookings.
$conflict = false;
for ($i = 0; $i < $duration; $i++) {
    $slot = date('H:i:s', $start + ($i * 3600));
    $check = mysqli_prepare($conn, "SELECT id FROM bookings WHERE court_id = ? AND booking_date = ? AND booking_time = ? AND status IN ('Pending','Approved') LIMIT 1");
    mysqli_stmt_bind_param($check, 'iss', $courtId, $date, $slot);
    mysqli_stmt_execute($check);
    $res = mysqli_stmt_get_result($check);
    if (mysqli_fetch_assoc($res)) $conflict = true;
    mysqli_stmt_close($check);
    if ($conflict) break;
}

if ($conflict) {
    header('Location: dashboard.php?error=' . urlencode('This court/time is already booked. Please choose another slot.'));
    exit();
}

// Fixed rate requested for the project: RM10 per hour.
$pricePerHour = 10.00;
$total = $pricePerHour * $duration;
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirm Booking - Badminton Kampung Panji</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#090a0f;--card:#13151f;--border:rgba(255,255,255,.08);--accent:#6366f1;--muted:#94a3b8}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:#f8fafc;min-height:100vh}
.nav{padding:18px 40px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:rgba(9,10,15,.9)}
.brand{font-weight:800;color:#fff;text-decoration:none}.brand small{color:#a855f7;margin-left:8px}
.wrap{max-width:720px;margin:60px auto;padding:0 20px}.cardx{background:var(--card);border:1px solid var(--border);border-radius:28px;padding:36px;box-shadow:0 20px 40px rgba(0,0,0,.45)}
.detail{display:flex;justify-content:space-between;gap:20px;padding:15px 0;border-bottom:1px solid var(--border);color:var(--muted)}.detail strong{color:#fff;text-align:right}.total{font-size:1.8rem;color:#fff!important}.btn-confirm{background:linear-gradient(135deg,#6366f1,#a855f7);border:0;color:#fff;border-radius:50px;font-weight:800;padding:14px 24px}.btn-back{border:1px solid var(--border);color:#fff;border-radius:50px;text-decoration:none;padding:14px 24px;text-align:center}
</style>
</head>
<body>
<nav class="nav"><a class="brand" href="dashboard.php">BADMINTON <small>KAMPUNG PANJI</small></a><a class="text-light text-decoration-none" href="my_booking.php">My Booking</a></nav>
<div class="wrap">
<div class="cardx">
<div class="text-uppercase fw-bold mb-2" style="color:#818cf8;font-size:.75rem;letter-spacing:1.5px">Final Step</div>
<h2 class="fw-bold mb-2">Confirm Booking</h2>
<p class="mb-4" style="color:var(--muted)">Semak pilihan anda. Court tidak perlu dipilih semula.</p>
<div class="detail"><span>Name</span><strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong></div>
<div class="detail"><span>Court</span><strong><?= htmlspecialchars($court['court_name']) ?></strong></div>
<div class="detail"><span>Date</span><strong><?= htmlspecialchars(date('D, d M Y', strtotime($date))) ?></strong></div>
<div class="detail"><span>Time</span><strong><?= htmlspecialchars(date('H:i', $start)) ?> - <?= htmlspecialchars(date('H:i', $end)) ?></strong></div>
<div class="detail"><span>Duration</span><strong><?= $duration ?> Hour<?= $duration > 1 ? 's' : '' ?></strong></div>
<div class="detail"><span>Rate</span><strong>RM 10.00 / hour</strong></div>
<div class="detail"><span>Total</span><strong class="total">RM <?= number_format($total,2) ?></strong></div>
<form method="POST" action="create_booking.php" class="mt-4">
<input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
<input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">
<input type="hidden" name="duration" value="<?= $duration ?>">
<input type="hidden" name="court_id" value="<?= $courtId ?>">
<div class="d-grid gap-3"><button type="submit" class="btn-confirm">Confirm Booking →</button><a href="dashboard.php" class="btn-back">← Change Selection</a></div>
</form>
</div>
</div>
</body>
</html>
