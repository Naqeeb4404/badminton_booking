<?php
session_start();
include __DIR__ . '/config/db.php';

if(!isset($_SESSION['user'])){
    $date = $_GET['date'] ?? '';
    $time = $_GET['time'] ?? '';
    $court_id = $_GET['court_id'] ?? '';
    header("Location: auth/login.php?return=booking&date=".urlencode($date)."&time=".urlencode($time)."&court_id=".urlencode($court_id));
    exit();
}

$user_id   = (int)$_SESSION['user']['id'];
$date      = $_GET['date'] ?? '';
$time      = $_GET['time'] ?? '';
$court_id  = (int)($_GET['court_id'] ?? 0);
$error     = $_GET['error'] ?? '';

// Basic sanity check on what dashboard.php sent us. We do NOT ask the user to
// pick again here — we only bounce back to the dashboard if something is
// genuinely missing or invalid, and we tell them why.
if(!$date || !$time || !$court_id || $date < date('Y-m-d')){
    header("Location: user/dashboard.php?error=".urlencode('Sila pilih tarikh, masa dan gelanggang.'));
    exit();
}

// Fetch the exact court the user already picked on the dashboard.
$stmt = $conn->prepare("SELECT id, court_name, price, status FROM courts WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $court_id);
$stmt->execute();
$court = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$court){
    header("Location: user/dashboard.php?error=".urlencode('Gelanggang tidak dijumpai. Sila pilih semula.'));
    exit();
}

// Court is still shown (read-only) so the user can SEE what they picked,
// but they are not forced to choose from a list again. If it went
// unavailable in the meantime, we say so and send them back to pick a
// different one — that's the only case where re-choosing makes sense.
$still_available = ($court['status'] === 'Available');

// Double-check the exact slot hasn't just been taken by someone else.
$slotTaken = false;
if($still_available){
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE court_id=? AND booking_date=? AND booking_time=? AND status IN ('Pending','Approved') LIMIT 1");
    $stmt->bind_param("iss", $court_id, $date, $time);
    $stmt->execute();
    $slotTaken = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$pretty_date = date('D, d M Y', strtotime($date));
$pretty_time = date('h:i A', strtotime($time));
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
        :root {
            --credix-bg: #090a0f;
            --credix-card: #13151f;
            --credix-border: rgba(255, 255, 255, 0.08);
            --credix-accent: #6366f1;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--credix-bg);
            color: var(--text-main);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .panel {
            background: var(--credix-card);
            border: 1px solid var(--credix-border);
            border-radius: 28px;
            padding: 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 25px rgba(99, 102, 241, 0.05);
        }
        .step {
            font-size: 0.72rem;
            font-weight: 800;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #818cf8;
        }
        h1 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 24px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid var(--credix-border);
        }
        .summary-row:last-of-type { border-bottom: none; }
        .summary-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
        }
        .summary-value {
            font-weight: 700;
            font-size: 0.98rem;
            text-align: right;
        }
        .btn-confirm {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            font-weight: 700;
            border-radius: 50px;
            padding: 14px;
            border: none;
            width: 100%;
            font-size: 0.95rem;
            margin-top: 24px;
        }
        .btn-confirm:hover { opacity: 0.92; color: #fff; }
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 14px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .btn-back:hover { color: var(--text-main); }
        .alert-custom {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="panel">
        <div class="step">Confirm your booking</div>
        <h1><?= htmlspecialchars($court['court_name']) ?></h1>

        <?php if($error): ?>
            <div class="alert-custom"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if(!$still_available): ?>
            <div class="alert-custom">
                <i class="fa-solid fa-circle-xmark me-2"></i>
                Maaf, gelanggang ini kini tidak tersedia.
            </div>
            <a href="user/dashboard.php" class="btn btn-confirm text-decoration-none">Pilih Gelanggang Lain</a>
        <?php elseif($slotTaken): ?>
            <div class="alert-custom">
                <i class="fa-solid fa-clock me-2"></i>
                Slot pada <?= htmlspecialchars($pretty_date) ?>, <?= htmlspecialchars($pretty_time) ?> baru sahaja ditempah oleh pengguna lain.
            </div>
            <a href="user/dashboard.php" class="btn btn-confirm text-decoration-none">Pilih Masa / Gelanggang Lain</a>
        <?php else: ?>
            <div class="summary-row">
                <span class="summary-label"><i class="fa-regular fa-calendar me-2"></i>Tarikh</span>
                <span class="summary-value"><?= htmlspecialchars($pretty_date) ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label"><i class="fa-regular fa-clock me-2"></i>Masa</span>
                <span class="summary-value"><?= htmlspecialchars($pretty_time) ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label"><i class="fa-solid fa-tag me-2"></i>Harga</span>
                <span class="summary-value">RM <?= htmlspecialchars($court['price']) ?></span>
            </div>

            <form method="POST" action="user/create_booking.php">
                <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">
                <input type="hidden" name="court_id" value="<?= htmlspecialchars($court_id) ?>">
                <button type="submit" class="btn-confirm">
                    <i class="fa-solid fa-check me-2"></i>Confirm Booking
                </button>
            </form>
        <?php endif; ?>

        <a href="user/dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>
