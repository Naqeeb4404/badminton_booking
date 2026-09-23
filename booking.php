<?php
session_start();
include __DIR__ . '/config/db.php';

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
    <title>Book a Court - Badminton Kampung Panji</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --credix-bg: #090a0f;
            --credix-card: #13151f;
            --credix-border: rgba(255, 255, 255, 0.08);
            --credix-accent: #6366f1;
            --credix-accent-hover: #4f46e5;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--credix-bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        /* Top Announcement Bar */
        .top-announcement-bar {
            font-size: 0.75rem;
            color: var(--text-muted);
            padding: 10px 40px;
            border-bottom: 1px solid var(--credix-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(19, 21, 31, 0.5);
            backdrop-filter: blur(10px);
        }

        /* Navbar */
        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 40px;
            border-bottom: 1px solid var(--credix-border);
            background: rgba(9, 10, 15, 0.8);
            backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .brand-text span {
            display: block;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            color: var(--text-main);
            line-height: 1.1;
        }

        .brand-text small {
            font-size: 0.65rem;
            color: #a855f7;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--text-main);
        }

        .btn-book-now {
            border: 1px solid var(--credix-border);
            color: var(--text-main);
            border-radius: 50px;
            padding: 8px 24px;
            font-weight: 700;
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.03);
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-book-now:hover {
            background: var(--credix-accent);
            border-color: var(--credix-accent);
            color: #fff;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        /* Hero Section */
        .hero {
            max-width: 1140px;
            margin: 40px auto 20px;
            padding: 0 20px;
            text-align: center; /* Ditukar ke tengah */
        }

        .hero h1 {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .hero p {
            color: var(--text-muted);
            font-size: 0.98rem;
            max-width: 620px;
            margin: 0 auto; /* Menengahkan perenggan */
            line-height: 1.6;
        }

        /* Container & Panels */
        .wrap {
            max-width: 1140px;
            margin: 0 auto 60px;
            padding: 0 20px;
        }

        .panel {
            background: var(--credix-card);
            border: 1px solid var(--credix-border);
            border-radius: 28px;
            padding: 40px;
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

        /* Dates Carousel/List */
        .dates {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .date {
            min-width: 82px;
            padding: 14px 10px;
            border: 1px solid var(--credix-border);
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
        }

        .date:hover {
            border-color: rgba(99, 102, 241, 0.4);
            color: var(--text-main);
        }

        .date.active {
            border-color: var(--credix-accent);
            background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.2));
            color: var(--text-main);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
        }

        .date .day {
            font-size: 0.65rem;
            color: #818cf8;
            font-weight: 800;
            text-transform: uppercase;
        }

        .date strong {
            display: block;
            font-size: 1.4rem;
            color: var(--text-main);
            margin: 4px 0;
        }

        .date .month {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Times Slots */
        .times {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .time {
            border: 1px solid var(--credix-border);
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-muted);
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .time:hover {
            border-color: rgba(99, 102, 241, 0.4);
            color: var(--text-main);
        }

        .time.active {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .time.disabled {
            opacity: 0.25;
            cursor: not-allowed;
            text-decoration: line-through;
            background: transparent;
        }

        /* Courts Grid */
        .courts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .court {
            border: 1px solid var(--credix-border);
            border-radius: 20px;
            padding: 24px;
            background: rgba(255, 255, 255, 0.02);
            transition: all 0.2s;
        }

        .court.selected {
            border-color: var(--credix-accent);
            background: rgba(99, 102, 241, 0.05);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.15);
        }

        .court.unavailable {
            opacity: 0.4;
            background: rgba(0, 0, 0, 0.2);
        }

        .court h5 {
            font-weight: 800;
            color: var(--text-main);
            font-size: 1.15rem;
        }

        .status {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .available {
            color: #34d399;
        }

        .busy {
            color: #f87171;
        }

        /* Summary Box & Compact Button */
        .summary {
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 22px;
            padding: 24px 28px;
            margin-top: 30px;
            color: var(--text-main);
        }

        .btn-confirm-selection {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 12px 28px;
            font-size: 0.9rem;
            font-weight: 800;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-confirm-selection:hover {
            opacity: 0.92;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }

        .notice {
            border-radius: 14px;
            padding: 14px 18px;
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            font-size: 0.88rem;
        }

        .confirm-card {
            max-width: 640px;
            margin: 20px auto;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--credix-border);
            padding: 14px 0;
            color: var(--text-muted);
            font-size: 0.92rem;
        }

        .detail strong {
            color: var(--text-main);
        }

        .detail:last-child {
            border: 0;
        }

        .price {
            font-size: 2rem !important;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        hr {
            border-color: var(--credix-border) !important;
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="top-announcement-bar d-none d-md-flex">
        <div><i class="fa-solid fa-bolt me-1 text-indigo"></i> CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div>
            <?php if(isset($_SESSION['user'])): ?>
                <span class="text-light fw-bold"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
            <?php else: ?>
                <a href="auth/login.php" class="text-decoration-none text-light fw-bold">Login / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Credix Glass Navbar -->
    <nav class="custom-navbar">
        <a href="index.php" class="brand-container">
            <div class="brand-logo-icon">
                <i class="fa-solid fa-feather"></i>
            </div>
            <div class="brand-text">
                <span>BADMINTON</span>
                <small>Kampung Panji</small>
            </div>
        </a>

        <div class="nav-links d-none d-lg-flex">
            <a href="index.php">Home</a>
            <a href="rates.php">Rates</a>
            <a href="facility.php">Facility</a>
            <a href="about.php">About</a>
            <a href="faq.php">FAQ</a>
            <a href="rules.php">Rules</a>
            <a href="location.php">Location</a>
        </div>
        <a href="booking.php" class="btn-book-now active">Book Now</a>
    </nav>

    <div class="hero">
        <h1>Book a court.</h1>
        <p>Pilih tarikh, masa dan gelanggang badminton pilihan anda. Semak butiran slot sebelum meneruskan pengesahan tempahan.</p>
    </div>

    <div class="wrap">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger rounded-4 mb-4"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['confirm']) && isset($_SESSION['user']) && $selectedTime && $selectedCourt):
            $confirmCourt = null;
            foreach ($courts as $c) if ((string)$c['id'] === (string)$selectedCourt) $confirmCourt = $c;
            $key = $selectedCourt.'|'.substr($selectedTime,0,5);
            $valid = $confirmCourt && $confirmCourt['status']==='Available' && !isset($booked[$key]) && !isPastSlot($selectedDate,$selectedTime);
        ?>
            <div class="panel confirm-card">
                <h3 class="fw-bold mb-4 text-white">Confirm Booking</h3>
                <?php if(!$valid): ?>
                    <div class="notice mb-4">Slot ini sudah tidak tersedia. Sila pilih slot lain.</div>
                    <a class="btn btn-light rounded-pill w-100 fw-bold py-3" href="booking.php">Choose another slot</a>
                <?php else: ?>
                    <div class="detail"><span>Name</span><strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong></div>
                    <div class="detail"><span>Date</span><strong><?= htmlspecialchars(date('D, d M Y', strtotime($selectedDate))) ?></strong></div>
                    <div class="detail"><span>Start time</span><strong><?= htmlspecialchars(substr($selectedTime,0,5)) ?></strong></div>
                    <div class="detail"><span>End time</span><strong><?= htmlspecialchars(date('H:i', strtotime($selectedTime) + 3600)) ?></strong></div>
                    <div class="detail"><span>Court</span><strong><?= htmlspecialchars($confirmCourt['court_name']) ?></strong></div>
                    <div class="detail"><span>Duration</span><strong>1 hour</strong></div>
                    <div class="detail align-items-center"><span>Total</span><strong class="price">RM <?= number_format((float)$confirmCourt['price'],2) ?></strong></div>
                    
                    <form method="POST" action="user/create_booking.php" class="mt-4">
                        <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                        <input type="hidden" name="time" value="<?= htmlspecialchars($selectedTime) ?>">
                        <input type="hidden" name="court_id" value="<?= htmlspecialchars($selectedCourt) ?>">
                        <button class="btn-confirm-selection w-100 py-3" type="submit">Confirm Booking →</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="panel">
                <!-- Step 1 -->
                <div class="step">1. Choose a date</div>
                <div class="dates mb-4">
                    <?php for($i=0;$i<14;$i++): $d=date('Y-m-d',strtotime("+$i days")); ?>
                        <a class="date <?= $d===$selectedDate?'active':'' ?>" href="booking.php?date=<?= $d ?>">
                            <span class="day"><?= $i===0?'TODAY':strtoupper(date('D',strtotime($d))) ?></span>
                            <strong><?= date('d',strtotime($d)) ?></strong>
                            <span class="month"><?= date('M',strtotime($d)) ?></span>
                        </a>
                    <?php endfor; ?>
                </div>

                <hr class="my-4">

                <!-- Step 2 -->
                <div class="step">2. Choose a start time</div>
                <div class="times mb-4">
                    <?php foreach($times as $t): $past=isPastSlot($selectedDate,$t); ?>
                        <a class="time <?= $t===$selectedTime?'active':'' ?> <?= $past?'disabled':'' ?>" href="<?= $past?'#':'booking.php?date='.urlencode($selectedDate).'&time='.urlencode($t) ?>">
                            <?= $t ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if(!$selectedTime): ?>
                    <div class="notice mb-4"><i class="fa-solid fa-circle-info me-2"></i>Sila pilih masa mula untuk melihat senarai gelanggang yang kosong.</div>
                <?php endif; ?>

                <?php if($selectedTime): ?>
                    <hr class="my-4">
                    <!-- Step 3 -->
                    <div class="step">3. Choose a court</div>
                    <div class="courts mb-4">
                        <?php foreach($courts as $c): 
                            $key=$c['id'].'|'.substr($selectedTime,0,5); 
                            $can=$c['status']==='Available' && !isset($booked[$key]) && !isPastSlot($selectedDate,$selectedTime); 
                        ?>
                            <div class="court <?= !$can?'unavailable':'' ?> <?= (string)$selectedCourt===(string)$c['id']?'selected':'' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5><?= htmlspecialchars($c['court_name']) ?></h5>
                                    <span class="fw-bold text-white">RM <?= number_format((float)$c['price'],2) ?></span>
                                </div>
                                <?php if($can): ?>
                                    <div class="status available mb-3"><i class="fa-solid fa-circle-check me-1"></i> Available</div>
                                    <a href="booking.php?date=<?= urlencode($selectedDate) ?>&time=<?= urlencode($selectedTime) ?>&court_id=<?= $c['id'] ?>" class="btn btn-outline-light rounded-pill w-100 fw-bold py-2" style="font-size: 0.85rem;">Select court</a>
                                <?php else: ?>
                                    <div class="status busy mb-3"><i class="fa-solid fa-circle-xmark me-1"></i> <?= $c['status']==='Available'?'Booked':'Not Available' ?></div>
                                    <button class="btn btn-dark rounded-pill w-100 py-2" style="font-size: 0.85rem; opacity: 0.5;" disabled>Unavailable</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if($selectedCourt): ?>
                    <div class="summary d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                        <div>
                            <span class="d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 1.5px; color: #a855f7;">Selected Slot</span>
                            <strong class="text-white" style="font-size: 1.05rem;">
                                <?= htmlspecialchars(date('d M Y',strtotime($selectedDate))) ?> • <?= htmlspecialchars($selectedTime) ?> • 
                                <?php foreach($courts as $c) if((string)$c['id']===(string)$selectedCourt) echo htmlspecialchars($c['court_name']); ?>
                            </strong>
                        </div>
                        <a class="btn-confirm-selection" href="<?= htmlspecialchars($continueUrl) ?>">Confirm Selection →</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>