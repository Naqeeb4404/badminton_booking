<?php
session_start();
include "config/db.php";

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedTime = $_GET['time'] ?? '';
$selectedCourt = $_GET['court_id'] ?? '';

// Past dates are not allowed.
if ($selectedDate < date('Y-m-d')) {
    $selectedDate = date('Y-m-d');
}

$courts = [];
$result = mysqli_query($conn, "SELECT id, court_name, price, status FROM courts ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $courts[] = $row;
}

// Court is available only when admin has marked it Available and there is no active booking for the selected slot.
$booked = [];
if ($selectedDate) {
    $stmt = mysqli_prepare($conn, "SELECT court_id, booking_time FROM bookings WHERE booking_date = ? AND status IN ('Pending','Approved')");
    mysqli_stmt_bind_param($stmt, "s", $selectedDate);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $booked[$r['court_id'] . '|' . substr($r['booking_time'], 0, 5)] = true;
    }
    mysqli_stmt_close($stmt);
}

$times = [];
for ($h = 8; $h <= 22; $h++) {
    $times[] = sprintf('%02d:00', $h);
}

function isPastSlot($date, $time) {
    if ($date !== date('Y-m-d')) return false;
    return $time <= date('H:i');
}

$continueUrl = 'auth/login.php?return=booking&date=' . urlencode($selectedDate) . '&time=' . urlencode($selectedTime) . '&court_id=' . urlencode($selectedCourt);
if (isset($_SESSION['user'])) {
    $continueUrl = 'booking.php?date=' . urlencode($selectedDate) . '&time=' . urlencode($selectedTime) . '&court_id=' . urlencode($selectedCourt) . '&confirm=1';
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book a Court - Sports Center</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f7f5;color:#111;margin:0}.top{max-width:1180px;margin:auto;padding:18px 20px 8px;display:flex;justify-content:space-between;color:#6b7280;font-size:13px}.nav{max-width:1180px;margin:auto;padding:12px 20px 22px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb}.brand{font-weight:800;text-decoration:none;color:#111;font-size:20px}.brand small{display:block;color:#888;font-size:10px;letter-spacing:1.5px}.links a{color:#333;text-decoration:none;margin:0 10px;font-size:13px;font-weight:600}.bookbtn{background:#111;color:#fff;text-decoration:none;padding:11px 18px;border-radius:999px;font-size:13px;font-weight:700}.hero{max-width:1180px;margin:30px auto 0;padding:0 20px}.hero h1{font-size:42px;font-weight:800;letter-spacing:-1.5px;margin-bottom:8px}.hero p{color:#6b7280;max-width:620px}.wrap{max-width:1180px;margin:30px auto;padding:0 20px 60px}.panel{background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:26px;box-shadow:0 10px 30px rgba(0,0,0,.04)}.step{font-size:13px;font-weight:800;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px}.dates{display:flex;gap:10px;overflow-x:auto;padding-bottom:8px}.date{min-width:78px;padding:12px 8px;border:1px solid #e5e7eb;border-radius:14px;text-align:center;cursor:pointer;background:#fff}.date.active{border:2px solid #111;background:#fafafa}.date .day{font-size:10px;color:#777;font-weight:800}.date strong{display:block;font-size:21px}.date .month{font-size:11px;color:#666}.times{display:flex;gap:10px;flex-wrap:wrap}.time{border:1px solid #ddd;background:#fff;border-radius:12px;padding:11px 17px;font-weight:700;cursor:pointer}.time.active{background:#111;color:#fff;border-color:#111}.time.disabled{opacity:.35;cursor:not-allowed;text-decoration:line-through}.courts{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:15px}.court{border:1px solid #e5e7eb;border-radius:16px;padding:18px;background:#fff}.court.selected{border:2px solid #111}.court.unavailable{opacity:.45;background:#f8f8f8}.court h5{font-weight:800}.status{font-size:12px;font-weight:800}.available{color:#15803d}.busy{color:#b91c1c}.summary{background:#f8fafc;border-radius:16px;padding:18px;margin-top:20px}.continue{width:100%;border:0;border-radius:12px;background:#111;color:#fff;padding:14px;font-weight:800}.continue:disabled{opacity:.35}.notice{border-radius:12px;padding:12px 15px;background:#fff7ed;color:#9a3412;font-size:13px}.confirm-card{max-width:620px;margin:35px auto}.detail{display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:11px 0}.detail:last-child{border:0}.price{font-size:25px;font-weight:800}
</style>
</head>
<body>
<div class="top"><span>CALL +60 11 6351 9188 &nbsp;|&nbsp; Dewan Kampung Panji, Kuala Terengganu</span><span><?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['name']) : 'Login / Register'; ?></span></div>
<nav class="nav"><a class="brand" href="index.php"><i class="fa-solid fa-shuttlecock"></i> SPORTS CENTER<small>BADMINTON • KUALA TERENGGANU</small></a><div class="links d-none d-lg-block"><a href="index.php">Home</a><a href="rates.php">Rates</a><a href="facility.php">Facility</a><a href="about.php">About</a><a href="faq.php">FAQ</a><a href="rules.php">Rules</a><a href="location.php">Location</a></div><a class="bookbtn" href="booking.php">Book Now</a></nav>

<div class="hero"><h1>Book a court.</h1><p>Pilih tarikh, masa dan gelanggang badminton yang masih kosong. Semak butiran dahulu sebelum membuat tempahan.</p></div>
<div class="wrap">
<?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
<?php if (isset($_GET['confirm']) && isset($_SESSION['user']) && $selectedTime && $selectedCourt):
    $confirmCourt = null;
    foreach ($courts as $c) if ((string)$c['id'] === (string)$selectedCourt) $confirmCourt = $c;
    $key = $selectedCourt.'|'.substr($selectedTime,0,5);
    $valid = $confirmCourt && $confirmCourt['status']==='Available' && !isset($booked[$key]) && !isPastSlot($selectedDate,$selectedTime);
?>
<div class="panel confirm-card"><h3 class="fw-bold mb-3">Confirm Booking</h3><?php if(!$valid): ?><div class="notice mb-3">Slot ini sudah tidak tersedia. Sila pilih slot lain.</div><a class="btn btn-dark w-100" href="booking.php">Choose another slot</a><?php else: ?><div class="detail"><span>Name</span><strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong></div><div class="detail"><span>Date</span><strong><?= htmlspecialchars(date('D, d M Y', strtotime($selectedDate))) ?></strong></div><div class="detail"><span>Start time</span><strong><?= htmlspecialchars(substr($selectedTime,0,5)) ?></strong></div><div class="detail"><span>End time</span><strong><?= htmlspecialchars(date('H:i', strtotime($selectedTime) + 3600)) ?></strong></div><div class="detail"><span>Court</span><strong><?= htmlspecialchars($confirmCourt['court_name']) ?></strong></div><div class="detail"><span>Duration</span><strong>1 hour</strong></div><div class="detail"><span>Total</span><strong class="price">RM <?= number_format((float)$confirmCourt['price'],2) ?></strong></div><form method="POST" action="user/create_booking.php" class="mt-4"><input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>"><input type="hidden" name="time" value="<?= htmlspecialchars($selectedTime) ?>"><input type="hidden" name="court_id" value="<?= htmlspecialchars($selectedCourt) ?>"><button class="continue" type="submit">Confirm Booking →</button></form><?php endif; ?></div>
<?php else: ?>
<div class="panel">
<div class="step">1. Choose a date</div>
<div class="dates">
<?php for($i=0;$i<14;$i++): $d=date('Y-m-d',strtotime("+$i days")); ?><a class="date <?= $d===$selectedDate?'active':'' ?>" href="booking.php?date=<?= $d ?>"><span class="day"><?= $i===0?'TODAY':strtoupper(date('D',strtotime($d))) ?></span><strong><?= date('d',strtotime($d)) ?></strong><span class="month"><?= date('M',strtotime($d)) ?></span></a><?php endfor; ?>
</div>
<hr class="my-4">
<div class="step">2. Choose a start time</div>
<div class="times mb-4">
<?php foreach($times as $t): $past=isPastSlot($selectedDate,$t); ?><a class="time <?= $t===$selectedTime?'active':'' ?> <?= $past?'disabled':'' ?>" href="<?= $past?'#':'booking.php?date='.urlencode($selectedDate).'&time='.urlencode($t) ?>"><?= $t ?></a><?php endforeach; ?>
</div>
<?php if(!$selectedTime): ?><div class="notice mb-4">Pilih masa mula untuk lihat gelanggang yang tersedia.</div><?php endif; ?>
<?php if($selectedTime): ?>
<div class="step">3. Choose a court</div>
<div class="courts">
<?php foreach($courts as $c): $key=$c['id'].'|'.substr($selectedTime,0,5); $can=$c['status']==='Available' && !isset($booked[$key]) && !isPastSlot($selectedDate,$selectedTime); ?><div class="court <?= !$can?'unavailable':'' ?> <?= (string)$selectedCourt===(string)$c['id']?'selected':'' ?>"><div class="d-flex justify-content-between"><h5><?= htmlspecialchars($c['court_name']) ?></h5><span>RM <?= number_format((float)$c['price'],2) ?></span></div><?php if($can): ?><div class="status available mb-3"><i class="fa-solid fa-circle-check"></i> Available</div><a href="booking.php?date=<?= urlencode($selectedDate) ?>&time=<?= urlencode($selectedTime) ?>&court_id=<?= $c['id'] ?>" class="btn btn-outline-dark w-100">Select court</a><?php else: ?><div class="status busy mb-3"><i class="fa-solid fa-circle-xmark"></i> <?= $c['status']==='Available'?'Booked':'Not Available' ?></div><button class="btn btn-light w-100" disabled>Unavailable</button><?php endif; ?></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if($selectedCourt): ?><div class="summary"><div class="d-flex justify-content-between"><span>Selected</span><strong><?= htmlspecialchars(date('d M Y',strtotime($selectedDate))) ?> • <?= htmlspecialchars($selectedTime) ?> • <?php foreach($courts as $c) if((string)$c['id']===(string)$selectedCourt) echo htmlspecialchars($c['court_name']); ?></strong></div><a class="btn btn-dark mt-3 w-100" href="<?= htmlspecialchars($continueUrl) ?>">Confirm Booking →</a></div><?php endif; ?>
</div>
<?php endif; ?>
</div>
</body></html>
