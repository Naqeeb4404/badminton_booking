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



    $check = mysqli_prepare(

        $conn,

        "SELECT id

         FROM bookings

         WHERE court_id = ?

         AND booking_date = ?

         AND booking_time = ?

         AND status IN ('Pending','Approved')

         LIMIT 1"

    );



    mysqli_stmt_bind_param($check, 'iss', $courtId, $date, $slot);

    mysqli_stmt_execute($check);



    $res = mysqli_stmt_get_result($check);



    if (mysqli_fetch_assoc($res)) {

        $conflict = true;

    }



    mysqli_stmt_close($check);



    if ($conflict) {

        break;

    }

}



if ($conflict) {

    header('Location: dashboard.php?error=' . urlencode('This court/time is already booked. Please choose another slot.'));

    exit();

}



// Ambil kadar harga terus daripada court yang dipilih.

$pricePerHour = (float)$court['price'];

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
:root{--bg:#090a0f;--card:#13151f;--border:rgba(255,255,255,.09);--accent:#6366f1;--purple:#a855f7;--muted:#94a3b8;--green:#4ade80}
*{box-sizing:border-box}
body{margin:0;font-family:'Plus Jakarta Sans',sans-serif;background:#090a0f;color:#fff;min-height:100vh;overflow-x:hidden}
.bg-video{position:fixed;inset:0;width:100vw;height:100vh;object-fit:cover;z-index:-3;filter:brightness(.22) saturate(.8)}
.bg-overlay{position:fixed;inset:0;z-index:-2;background:radial-gradient(circle at 15% 20%,rgba(99,102,241,.25),transparent 35%),radial-gradient(circle at 85% 70%,rgba(168,85,247,.20),transparent 35%),linear-gradient(180deg,rgba(9,10,15,.45),rgba(9,10,15,.94));pointer-events:none}
.glow{position:fixed;width:350px;height:350px;border-radius:50%;background:#6366f1;filter:blur(150px);opacity:.12;z-index:-1;pointer-events:none}.glow-one{top:80px;left:-120px}.glow-two{bottom:-100px;right:-100px;background:#a855f7}
.nav{min-height:78px;padding:12px 40px;display:flex;align-items:center;justify-content:space-between;background:rgba(9,10,15,.80);border-bottom:1px solid var(--border);backdrop-filter:blur(18px);position:relative;z-index:10}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}.brand-logo-icon{width:53px;height:53px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:10px;overflow:hidden}.brand-logo-icon img{width:51px;height:51px;display:block;object-fit:contain;border-radius:9px}
.brand-text{display:flex;flex-direction:column}.brand-text span{color:#f8fafc;font-size:.95rem;font-weight:800;letter-spacing:.5px;line-height:1.1}.brand-text small{color:#a855f7;font-size:.65rem;font-weight:700;letter-spacing:1.5px;margin-top:5px;text-transform:uppercase}
.my-booking-link{color:#cbd5e1;text-decoration:none;font-size:.82rem;font-weight:700;padding:9px 16px;border:1px solid var(--border);border-radius:50px;background:rgba(255,255,255,.03);transition:.2s}.my-booking-link:hover{color:#fff;background:rgba(255,255,255,.08)}
.page{width:100%;display:flex;justify-content:center;padding:32px 20px 70px}.containerx{width:100%;max-width:650px}

.cardx{position:relative;background:linear-gradient(145deg,rgba(24,26,39,.94),rgba(15,16,25,.94));backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.10);border-radius:26px;padding:30px;box-shadow:0 30px 80px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.05);overflow:hidden}.cardx:before{content:"";position:absolute;top:-80px;right:-80px;width:180px;height:180px;border-radius:50%;background:#6366f1;filter:blur(80px);opacity:.12;pointer-events:none}
.badge-step{width:max-content;margin:0 auto 12px;display:flex;align-items:center;gap:6px;padding:6px 11px;border-radius:50px;background:rgba(99,102,241,.09);border:1px solid rgba(99,102,241,.22);color:#a5b4fc;font-size:.65rem;font-weight:800;letter-spacing:.6px;text-transform:uppercase}
.title{text-align:center;font-size:1.55rem;font-weight:800;letter-spacing:-.5px;margin:0 0 7px}.subtitle{text-align:center;color:var(--muted);font-size:.78rem;line-height:1.5;margin-bottom:22px}
.court-preview{position:relative;height:175px;margin-bottom:16px;border-radius:18px;overflow:hidden;border:1px solid rgba(255,255,255,.10);background:#11131d}.court-preview>img{width:100%;height:100%;display:block;object-fit:cover}.court-preview-overlay{position:absolute;inset:0;background:linear-gradient(180deg,rgba(8,9,14,.05) 15%,rgba(8,9,14,.88) 100%)}.court-preview-content{position:absolute;left:0;right:0;bottom:0;padding:16px 17px;display:flex;align-items:flex-end;justify-content:space-between;gap:15px}.court-label{display:block;color:#c7d2fe;font-size:.58rem;font-weight:800;letter-spacing:1.2px;margin-bottom:4px}.court-preview-content strong{display:block;color:#fff;font-size:1rem;font-weight:800}.ready-badge{flex-shrink:0;padding:6px 9px;border-radius:50px;background:rgba(74,222,128,.13);border:1px solid rgba(74,222,128,.25);color:#86efac;font-size:.6rem;font-weight:800;backdrop-filter:blur(8px)}
.details{background:rgba(255,255,255,.025);border:1px solid var(--border);padding:7px 16px;border-radius:15px}.detail{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:12px 0;border-bottom:1px solid var(--border);color:var(--muted);font-size:.78rem}.detail:last-child{border-bottom:0}.detail-label{display:flex;align-items:center;gap:9px}.detail-label i{width:18px;text-align:center;color:#818cf8}.detail strong{color:#fff;text-align:right;font-weight:700}.detail.total-row{padding:15px 0}.detail .total{font-size:1.25rem;color:var(--green)!important;font-weight:800}
.actions{display:grid;grid-template-columns:1fr 1.35fr;gap:10px;margin-top:20px}.btn-confirm,.btn-back{min-height:48px;border-radius:50px;font-family:inherit;font-size:.78rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:7px;text-decoration:none;transition:.2s}.btn-confirm{background:linear-gradient(135deg,#6366f1,#a855f7);border:0;color:#fff;box-shadow:0 8px 25px rgba(99,102,241,.25)}.btn-confirm:hover{transform:translateY(-2px);color:#fff;box-shadow:0 12px 30px rgba(99,102,241,.35)}.btn-back{border:1px solid var(--border);color:#cbd5e1;background:rgba(255,255,255,.025)}.btn-back:hover{color:#fff;background:rgba(255,255,255,.07)}
.security-note{display:flex;justify-content:center;align-items:center;gap:6px;margin-top:14px;color:#64748b;font-size:.62rem}.security-note i{color:#4ade80}
@media(max-width:768px){.nav{min-height:70px;padding:10px 18px}.brand-logo-icon{width:47px;height:47px}.brand-logo-icon img{width:45px;height:45px}.brand-text span{font-size:.85rem}.brand-text small{font-size:.57rem}.page{padding:24px 14px 50px}.cardx{padding:24px 18px;border-radius:22px}}
@media(max-width:480px){.brand-text{display:none}.nav{padding-left:14px;padding-right:14px}.cardx{padding:22px 16px}.title{font-size:1.35rem}.actions{grid-template-columns:1fr}.btn-confirm{order:1}.btn-back{order:2}}
</style>
</head>
<body>

<video autoplay loop muted playsinline class="bg-video">
    <source src="sports.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>
<div class="glow glow-one"></div>
<div class="glow glow-two"></div>

<nav class="nav">
    <a class="brand" href="dashboard.php">
        <div class="brand-logo-icon">
            <img src="../logo-badminton.png" alt="Badminton Kampung Panji">
        </div>
        <div class="brand-text">
            <span>BADMINTON</span>
            <small>KAMPUNG PANJI</small>
        </div>
    </a>
    <a class="my-booking-link" href="my_booking.php">
        <i class="fa-solid fa-calendar-check"></i> My Booking
    </a>
</nav>

<div class="page">
<div class="containerx">

    <div class="steps">
        <div class="step-item active">
            <div class="step-number">1</div>
            Confirm Booking
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <div class="step-number">2</div>
            Payment
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <div class="step-number">3</div>
            Receipt
        </div>
    </div>

    <div class="cardx">
        <div class="badge-step">
            <i class="fa-solid fa-circle-check"></i>
            Final Booking Check
        </div>

        <h2 class="title">Confirm Booking</h2>
        <div class="subtitle">
            Semak maklumat tempahan anda sebelum meneruskan ke pembayaran.
        </div>

        <div class="court-preview">
            <img
                src="../images/court<?= (int)$courtId ?>.jpg"
                alt="<?= htmlspecialchars($court['court_name']) ?>"
                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=900&auto=format&fit=crop';"
            >
            <div class="court-preview-overlay"></div>
            <div class="court-preview-content">
                <div>
                    <span class="court-label">YOUR SELECTED COURT</span>
                    <strong><?= htmlspecialchars($court['court_name']) ?></strong>
                </div>
                <span class="ready-badge"><i class="fa-solid fa-circle-check"></i> Ready to Book</span>
            </div>
        </div>

        <div class="details">
            <div class="detail">
                <div class="detail-label"><i class="fa-solid fa-user"></i><span>Name</span></div>
                <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>
            </div>
            <div class="detail">
                <div class="detail-label"><i class="fa-solid fa-calendar-days"></i><span>Date</span></div>
                <strong><?= htmlspecialchars(date('D, d M Y', strtotime($date))) ?></strong>
            </div>
            <div class="detail">
                <div class="detail-label"><i class="fa-regular fa-clock"></i><span>Time</span></div>
                <strong><?= htmlspecialchars(date('H:i', $start)) ?> - <?= htmlspecialchars(date('H:i', $end)) ?></strong>
            </div>
            <div class="detail">
                <div class="detail-label"><i class="fa-solid fa-hourglass-half"></i><span>Duration</span></div>
                <strong><?= $duration ?> Hour<?= $duration > 1 ? 's' : '' ?></strong>
            </div>
            <div class="detail">
                <div class="detail-label"><i class="fa-solid fa-tag"></i><span>Rate</span></div>
                <strong>RM <?= number_format($pricePerHour,2) ?> / hour</strong>
            </div>
            <div class="detail total-row">
                <div class="detail-label"><i class="fa-solid fa-wallet"></i><span>Total</span></div>
                <strong class="total">RM <?= number_format($total,2) ?></strong>
            </div>
        </div>

        <form method="POST" action="create_booking.php">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
            <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">
            <input type="hidden" name="duration" value="<?= $duration ?>">
            <input type="hidden" name="court_id" value="<?= $courtId ?>">

            <div class="actions">
                <a href="dashboard.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Change Selection
                </a>
                <button type="submit" class="btn-confirm">
                    Confirm & Continue <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </form>

        <div class="security-note">
            <i class="fa-solid fa-lock"></i>
            Your booking details are securely processed
        </div>
    </div>
</div>
</div>
</body>
</html>
