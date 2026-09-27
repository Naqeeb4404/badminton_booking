<?php

session_start();

include __DIR__ . '/../config/db.php';

// Waktu tempatan Malaysia untuk semakan slot yang sudah lepas.

date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['user'])) {

    header("Location: ../auth/login.php");

    exit();

}

$user_id = (int)$_SESSION['user']['id'];

/* USER MESSAGE NOTIFICATION */

$unread_reply_count = 0;

$stmtNoti = $conn->prepare("

    SELECT COUNT(*) AS total

    FROM messages

    WHERE user_id = ?

      AND admin_reply IS NOT NULL

      AND admin_reply != ''

      AND reply_is_read = 0

");

if ($stmtNoti) {

    $stmtNoti->bind_param("i", $user_id);

    $stmtNoti->execute();

    $notiResult = $stmtNoti->get_result();

    if ($notiResult) {

        $notiData = $notiResult->fetch_assoc();

        $unread_reply_count = (int)($notiData['total'] ?? 0);

    }

    $stmtNoti->close();

}

/* PILIHAN USER */

$selected_date = $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d');

$selected_duration = isset($_POST['duration']) ? (int)$_POST['duration'] : (isset($_GET['duration']) ? (int)$_GET['duration'] : 1);

$selected_time = $_POST['time'] ?? $_GET['time'] ?? '18:00:00';

$court_page = isset($_POST['court_page']) ? (int)$_POST['court_page'] : (isset($_GET['court_page']) ? (int)$_GET['court_page'] : 1);

/* VALIDATE DATE */

$date_object = DateTime::createFromFormat('Y-m-d', $selected_date);

if (

    !$date_object ||

    $date_object->format('Y-m-d') !== $selected_date ||

    $selected_date < date('Y-m-d')

) {

    $selected_date = date('Y-m-d');

}

if ($selected_duration < 1) $selected_duration = 1;

if ($selected_duration > 4) $selected_duration = 4;

if ($court_page < 1) $court_page = 1;

// Slot yang masa mula sudah lepas pada hari ini tidak boleh ditempah.

$selected_slot_is_past = strtotime($selected_date . ' ' . substr($selected_time, 0, 5)) <= time();

/* BOOK COURT */

if (isset($_POST['court'])) {

    $court_id = $_POST['court'] ?? '';

    $booking_date = $_POST['date'] ?? '';

    $booking_time = $_POST['time'] ?? '';

    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 1;

    if ($duration < 1) $duration = 1;

    if ($duration > 4) $duration = 4;

    if (

        empty($court_id) ||

        empty($booking_date) ||

        empty($booking_time)

    ) {

        header(

            "Location: dashboard.php?error=" .

            urlencode("Please select date, duration, time and court.")

        );

        exit();

    }

    // Jangan benarkan booking slot yang sudah lepas untuk hari semasa.

    if (strtotime($booking_date . ' ' . substr($booking_time, 0, 5)) <= time()) {

        header(

            "Location: dashboard.php?date=" . urlencode($booking_date) .

            "&duration=" . urlencode($duration) .

            "&error=" . urlencode("Selected time has already passed.")

        );

        exit();

    }

    $url = "confirm_booking.php"

        . "?date=" . urlencode($booking_date)

        . "&time=" . urlencode($booking_time)

        . "&duration=" . urlencode($duration)

        . "&court_id=" . urlencode($court_id);

    header("Location: " . $url);

    exit();

}

/* COURT PAGINATION */

$limit = 6;

/* DISABLED & DELETED TAK DIKIRA */

$count_sql = "

    SELECT COUNT(*) AS total

    FROM courts

    WHERE status NOT IN ('Disabled', 'Deleted')

";

$count_result = mysqli_query($conn, $count_sql);

$count_row = mysqli_fetch_assoc($count_result);

$total_courts = (int)$count_row['total'];

$total_pages = max(1, (int)ceil($total_courts / $limit));

if ($court_page > $total_pages) {

    $court_page = $total_pages;

}

$offset = ($court_page - 1) * $limit;

/* COURT + STATUS IKUT TARIKH */

/* DISABLED & DELETED TAK DIPAPARKAN */

$selected_start = $selected_date . ' ' . substr($selected_time, 0, 5) . ':00';

$selected_end = date('Y-m-d H:i:s', strtotime($selected_start . " +{$selected_duration} hours"));

$court_sql = "

    SELECT

        c.*,

        CASE WHEN EXISTS (

            SELECT 1

            FROM court_unavailability cu

            WHERE cu.court_id = c.id

              AND cu.unavailable_date = ?

              AND (

                    cu.start_time IS NULL

                    OR cu.end_time IS NULL

                    OR (

                        TIMESTAMP(cu.unavailable_date, cu.start_time) < ?

                        AND TIMESTAMP(cu.unavailable_date, cu.end_time) > ?

                    )

              )

        ) THEN 1 ELSE 0 END AS date_blocked,

        (

            SELECT cu.reason

            FROM court_unavailability cu

            WHERE cu.court_id = c.id

              AND cu.unavailable_date = ?

              AND (

                    cu.start_time IS NULL

                    OR cu.end_time IS NULL

                    OR (

                        TIMESTAMP(cu.unavailable_date, cu.start_time) < ?

                        AND TIMESTAMP(cu.unavailable_date, cu.end_time) > ?

                    )

              )

            ORDER BY cu.id DESC

            LIMIT 1

        ) AS unavailable_reason,

        CASE WHEN EXISTS (

            SELECT 1

            FROM bookings b

            WHERE b.court_id = c.id

              AND b.booking_date = ?

              AND b.status IN ('Pending', 'Approved')

              AND TIMESTAMP(b.booking_date, b.booking_time) < ?

              AND DATE_ADD(

                    TIMESTAMP(b.booking_date, b.booking_time),

                    INTERVAL COALESCE(b.duration, 1) HOUR

                  ) > ?

        ) THEN 1 ELSE 0 END AS slot_booked

    FROM courts c

    WHERE c.status NOT IN ('Disabled', 'Deleted')

    ORDER BY c.id ASC

    LIMIT ? OFFSET ?

";

$court_stmt = mysqli_prepare($conn, $court_sql);

mysqli_stmt_bind_param(

    $court_stmt,

    "sssssssssii",

    $selected_date,

    $selected_end,

    $selected_start,

    $selected_date,

    $selected_end,

    $selected_start,

    $selected_date,

    $selected_end,

    $selected_start,

    $limit,

    $offset

);

mysqli_stmt_execute($court_stmt);

$result = mysqli_stmt_get_result($court_stmt);

?>

<!DOCTYPE html>

<html lang="ms">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Book Court - Badminton Kampung Panji</title>

<!-- Bootstrap 5 CSS -->

<link

    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

    rel="stylesheet"

>

<!-- Font Awesome -->

<link

    rel="stylesheet"

    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"

>

<!-- Google Fonts -->

<link

    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"

    rel="stylesheet"

>

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

html {

    scroll-behavior: smooth;

}

body {

    font-family: 'Plus Jakarta Sans', sans-serif;

    background-color: var(--credix-bg);

    color: var(--text-main);

    min-height: 100vh;

    margin: 0;

    padding: 0;

}

/* TOP BAR */

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

/* NAVBAR */

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

    width: 53px;

    height: 53px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    background: transparent;

    border: none;

    border-radius: 10px;

    overflow: hidden;

    margin: 0;

    padding: 0;

}

.brand-logo-icon img {

    width: 51px;

    height: 51px;

    display: block;

    object-fit: contain;

    object-position: center;

    background: transparent;

    border-radius: 9px;

    border: none;

    margin: 0;

    padding: 0;

    filter: none;

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

.nav-links a:hover,

.nav-links a.active {

    color: var(--text-main);

}

.message-nav {

    position: relative;

    display: inline-flex;

    align-items: center;

    gap: 6px;

}

.message-badge {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 20px;

    height: 20px;

    padding: 0 6px;

    border-radius: 999px;

    background: #ef4444;

    color: #fff;

    font-size: 0.68rem;

    font-weight: 800;

    line-height: 1;

}

.btn-logout {

    border: 1px solid rgba(239, 68, 68, 0.3);

    color: #f87171;

    border-radius: 50px;

    padding: 8px 20px;

    font-weight: 700;

    font-size: 0.85rem;

    background: rgba(239, 68, 68, 0.05);

    text-decoration: none;

    transition: all 0.2s;

}

.btn-logout:hover {

    background: #ef4444;

    border-color: #ef4444;

    color: #fff;

    box-shadow: 0 0 20px rgba(239, 68, 68, 0.4);

}

/* HERO */

.hero {

    max-width: 1140px;

    margin: 40px auto 20px;

    padding: 0 20px;

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

    line-height: 1.6;

}

/* CONTAINER */

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

    box-shadow:

        0 20px 40px rgba(0, 0, 0, 0.5),

        0 0 25px rgba(99, 102, 241, 0.05);

}

.step {

    font-size: 0.72rem;

    font-weight: 800;

    margin-bottom: 16px;

    text-transform: uppercase;

    letter-spacing: 1.5px;

    color: #818cf8;

}

/* DATE - KEKAL MACAM ASAL */

.dates {

    display: flex;

    gap: 12px;

    overflow-x: auto;

    padding-bottom: 8px;

    scrollbar-width: thin;

    scrollbar-color: rgba(255,255,255,0.2) transparent;

}

.date-card {

    min-width: 82px;

    padding: 14px 10px;

    border: 1px solid var(--credix-border);

    border-radius: 16px;

    text-align: center;

    cursor: pointer;

    background: rgba(255, 255, 255, 0.02);

    color: var(--text-muted);

    transition: all 0.2s;

}

.date-card input[type="radio"] {

    display: none;

}

.date-card:hover {

    border-color: rgba(99, 102, 241, 0.4);

    color: var(--text-main);

    transform: translateY(-2px);

}

.date-card:active {

    transform: scale(0.96);

}

.date-card.active {

    border-color: var(--credix-accent);

    background: linear-gradient(

        135deg,

        rgba(99,102,241,0.2),

        rgba(168,85,247,0.2)

    );

    color: var(--text-main);

    box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);

}

/* DURATION & TIME */

.time-slots {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

}

.time-slot-btn {

    border: 1px solid var(--credix-border);

    background: rgba(255, 255, 255, 0.02);

    color: var(--text-muted);

    border-radius: 12px;

    padding: 12px 20px;

    font-weight: 700;

    font-size: 0.88rem;

    cursor: pointer;

    transition: all 0.2s;

}

.time-slot-btn input[type="radio"] {

    display: none;

}

.time-slot-btn:hover {

    border-color: rgba(99, 102, 241, 0.4);

    color: var(--text-main);

    transform: translateY(-2px);

}

.time-slot-btn:active {

    transform: scale(0.95);

}

.time-slot-btn.active {

    background: linear-gradient(135deg, #6366f1, #a855f7);

    color: #fff;

    border-color: transparent;

    box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);

}

/* COURT TABLE */

.table-custom {

    background-color: transparent;

    color: var(--text-main);

    border-radius: 16px;

    overflow: hidden;

    border: 1px solid var(--credix-border);

}

.table-custom th {

    background-color: rgba(9, 10, 15, 0.6);

    color: #818cf8;

    font-weight: 800;

    font-size: 0.8rem;

    text-transform: uppercase;

    letter-spacing: 1px;

    padding: 16px;

    border-bottom: 1px solid var(--credix-border);

}

.table-custom td {

    padding: 16px;

    vertical-align: middle;

    color: var(--text-main);

    border-bottom: 1px solid var(--credix-border);

    background: rgba(255, 255, 255, 0.01);

}

.table-custom tr.unavailable td {

    opacity: 0.4;

    background: rgba(0, 0, 0, 0.2);

}

.btn-book {

    background: linear-gradient(135deg, #6366f1, #a855f7);

    color: #fff;

    font-weight: 700;

    border-radius: 50px;

    padding: 10px 20px;

    border: none;

    font-size: 0.85rem;

    transition: all 0.2s;

}

.btn-book:hover {

    opacity: 0.92;

    color: #fff;

    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);

}

/* COURT PAGINATION */

.court-pagination {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-top: 24px;

}

.court-page-btn {

    border: 1px solid var(--credix-border);

    background: rgba(255,255,255,0.03);

    color: var(--text-main);

    border-radius: 50px;

    padding: 10px 22px;

    font-weight: 700;

    font-size: 0.85rem;

    transition: all 0.2s;

}

.court-page-btn:hover:not(:disabled) {

    background: linear-gradient(135deg, #6366f1, #a855f7);

    border-color: transparent;

    color: #fff;

}

.court-page-btn:disabled {

    opacity: 0.35;

    cursor: not-allowed;

}

.court-page-info {

    color: var(--text-muted);

    font-size: 0.85rem;

    font-weight: 700;

}

hr {

    border-color: var(--credix-border) !important;

    opacity: 1;

}

.error-box {

    background: rgba(239, 68, 68, 0.1);

    border: 1px solid rgba(239, 68, 68, 0.3);

    color: #f87171;

    padding: 15px 20px;

    border-radius: 14px;

    margin-bottom: 20px;

    font-size: 0.9rem;

    font-weight: 600;

}

@media (max-width: 768px) {

    .custom-navbar {

        padding: 15px 20px;

    }

    .panel {

        padding: 25px 18px;

    }

    .hero h1 {

        font-size: 2rem;

    }

    .top-announcement-bar {

        padding: 10px 20px;

    }

    .court-page-btn {

        padding: 8px 14px;

    }

}

/* NAVBAR - SAME STYLE AS FEEDBACK PAGE */
.custom-navbar{height:78px;padding:10px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,.09);background:rgba(9,10,15,.84);backdrop-filter:blur(18px);position:sticky;top:0;z-index:30}
.brand-container{display:flex;align-items:center;gap:12px;text-decoration:none}
.brand-logo-icon{width:53px;height:53px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:transparent;border:none;border-radius:10px;overflow:hidden;margin:0;padding:0}
.brand-logo-icon img{width:51px;height:51px;display:block;object-fit:contain;object-position:center;background:transparent;border-radius:9px;border:none;margin:0;padding:0;filter:none}
.brand-text{display:flex;flex-direction:column;justify-content:center;align-items:flex-start}
.brand-text span{display:block;color:#f8fafc;font-size:.95rem;font-weight:800;letter-spacing:.5px;line-height:1.1}
.brand-text small{display:block;color:#a855f7;font-size:.65rem;font-weight:700;letter-spacing:1.5px;line-height:1;margin-top:5px;text-transform:uppercase}
.nav-center{display:flex;gap:34px;align-items:center;margin-left:auto;margin-right:auto;white-space:nowrap}
.nav-center a{color:#94a3b8;text-decoration:none;font-size:.84rem;font-weight:700;transition:color .22s ease,transform .22s ease}
.nav-center a:hover{color:#fff;transform:translateY(-1px)}
.nav-actions{display:flex;align-items:center;gap:8px}
.nav-btn{height:40px;padding:0 14px;border:1px solid rgba(255,255,255,.09);border-radius:50px;background:rgba(255,255,255,.035);color:#dbe3ef;text-decoration:none;display:flex;align-items:center;gap:7px;font-size:.68rem;font-weight:800;transition:.22s}
.nav-btn:hover{transform:translateY(-2px);background:rgba(255,255,255,.08);border-color:rgba(99,102,241,.25);color:#fff;box-shadow:0 7px 20px rgba(99,102,241,.10)}
.nav-btn.active{background:rgba(99,102,241,.11);border-color:rgba(99,102,241,.25);color:#c4b5fd}
.nav-btn.logout{border-color:rgba(251,113,133,.2);color:#fda4af;background:rgba(251,113,133,.06)}
.nav-btn.logout:hover{background:rgba(251,113,133,.11);border-color:rgba(251,113,133,.3)}
@media(max-width:900px){.nav-center{gap:18px}.nav-center a{font-size:.72rem}}
@media(max-width:768px){.custom-navbar{height:70px;padding:8px 16px}.brand-logo-icon{width:47px;height:47px}.brand-logo-icon img{width:45px;height:45px}.brand-text{display:none}.nav-center{display:none}.nav-btn span{display:none}.nav-btn{width:40px;padding:0;justify-content:center}.nav-actions{gap:5px}}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="custom-navbar">
    <a href="dashboard.php" class="brand-container">
        <div class="brand-logo-icon"><img src="../logo-badminton.png" alt="Badminton Kampung Panji"></div>
        <div class="brand-text"><span>BADMINTON</span><small>KAMPUNG PANJI</small></div>
    </a>
    <div class="nav-center">
        <a href="feedback_report.php">Feedback</a>
        <a href="message.php" class="message-nav">Message<?php if($unread_reply_count>0){ ?><span class="message-badge"><?php echo $unread_reply_count>99?'99+':$unread_reply_count; ?></span><?php } ?></a>
        <a href="my_booking.php">My Booking</a>
        <a href="profile.php">Profile</a>
    </div>
    <div class="nav-actions">
        <a href="dashboard.php" class="nav-btn active"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
        <a href="../auth/logout.php" class="nav-btn logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
    </div>
</nav>

<!-- HERO -->

<div class="hero">

    <h1>Book your court.</h1>

    <p>

        Pilih tarikh, tempoh permainan, masa mula dan gelanggang.

        Selepas memilih gelanggang, anda akan terus ke halaman

        pengesahan tempahan.

    </p>

</div>

<!-- BOOKING -->

<div class="wrap">

<?php if (isset($_GET['error'])) { ?>

    <div class="error-box">

        <i class="fa-solid fa-circle-exclamation me-2"></i>

        <?php echo htmlspecialchars($_GET['error']); ?>

    </div>

<?php } ?>

<div class="panel">

<form method="POST" action="dashboard.php" id="bookingForm">

<!-- STEP 1 - DATE -->

<div class="step">

    1. Choose a date

</div>

<div class="dates mb-4">

<?php

for ($i = 0; $i < 14; $i++) {

    $date_val = date(

        'Y-m-d',

        strtotime("+$i days")

    );

    $day_name = ($i == 0)

        ? 'TODAY'

        : strtoupper(

            date(

                'D',

                strtotime("+$i days")

            )

        );

    $day_num = date(

        'd',

        strtotime("+$i days")

    );

    $month_short = date(

        'M',

        strtotime("+$i days")

    );

    $checked = ($date_val === $selected_date)

        ? 'checked'

        : '';

    $active_class = ($date_val === $selected_date)

        ? 'active'

        : '';

?>

<label

    class="date-card <?php echo $active_class; ?>"

    onclick="updateDateCard(this)"

>

    <input

        type="radio"

        name="date"

        value="<?php echo $date_val; ?>"

        <?php echo $checked; ?>

        required

    >

    <span

        style="

            font-size:0.65rem;

            color:#818cf8;

            font-weight:800;

            text-transform:uppercase;

        "

    >

        <?php echo $day_name; ?>

    </span>

    <strong

        style="

            display:block;

            font-size:1.4rem;

            color:var(--text-main);

            margin:4px 0;

        "

    >

        <?php echo $day_num; ?>

    </strong>

    <span

        style="

            font-size:0.7rem;

            color:var(--text-muted);

        "

    >

        <?php echo $month_short; ?>

    </span>

</label>

<?php } ?>

</div>

<hr class="my-4">

<!-- STEP 2 - DURATION -->

<div class="step">

    2. Choose duration (hours)

</div>

<div class="time-slots mb-4">

<?php

for ($d = 1; $d <= 4; $d++) {

    $duration_checked =

        ($d === $selected_duration)

        ? 'checked'

        : '';

    $duration_active =

        ($d === $selected_duration)

        ? 'active'

        : '';

?>

<label

    class="time-slot-btn duration-card <?php echo $duration_active; ?>"

    onclick="updateDurationCard(this)"

>

    <input

        type="radio"

        name="duration"

        value="<?php echo $d; ?>"

        <?php echo $duration_checked; ?>

        required

    >

    <?php echo $d; ?>

    Hour<?php echo $d > 1 ? 's' : ''; ?>

</label>

<?php } ?>

</div>

<hr class="my-4">

<!-- STEP 3 - TIME -->

<div class="step">

    3. Choose a start time

</div>

<div class="time-slots mb-4">

<?php

$times = [

    '08:00',

    '10:00',

    '14:00',

    '16:00',

    '18:00',

    '20:00',

    '22:00'

];

foreach ($times as $t) {

    $time_value = $t . ':00';

    $t_checked =

        ($time_value === $selected_time)

        ? 'checked'

        : '';

    $t_active =

        ($time_value === $selected_time)

        ? 'active'

        : '';

    $slot_timestamp = strtotime($selected_date . ' ' . $t);

    $time_is_past = ($slot_timestamp <= time());

    // Slot lama tidak boleh kekal selected.

    if ($time_is_past) {

        $t_checked = '';

        $t_active = '';

    }

?>

<label

    class="time-slot-btn start-time-card <?php echo $t_active; ?><?php echo $time_is_past ? ' unavailable' : ''; ?>"

    <?php if (!$time_is_past): ?>onclick="updateStartTimeCard(this)"<?php endif; ?>

    <?php echo $time_is_past ? 'style="opacity:.45;cursor:not-allowed;"' : ''; ?>

>

    <input

        type="radio"

        name="time"

        value="<?php echo $time_value; ?>"

        <?php echo $t_checked; ?>

        <?php echo $time_is_past ? 'disabled' : ''; ?>

        required

    >

    <?php echo $t; ?>

    <?php if ($time_is_past): ?>

        <small style="display:block;font-size:10px;">Passed</small>

    <?php endif; ?>

</label>

<?php } ?>

</div>

<hr class="my-4">

<!-- STEP 4 - COURT -->

<div id="courtSection" class="step">

    4. Choose a court

</div>

<input

    type="hidden"

    name="court_page"

    id="courtPage"

    value="<?php echo $court_page; ?>"

>

<div class="table-responsive">

<table class="table table-custom align-middle mb-0">

<thead>

<tr>

    <th style="width:80px;">

        ID

    </th>

    <th>

        Nama Gelanggang

    </th>

    <th

        style="width:140px;"

        class="text-center"

    >

        Harga

    </th>

    <th

        style="width:160px;"

        class="text-center"

    >

        Status

    </th>

    <th

        style="width:180px;"

        class="text-end"

    >

        Tindakan

    </th>

</tr>

</thead>

<tbody>

<?php

while ($row = mysqli_fetch_assoc($result)) {

    $is_available =

        !$selected_slot_is_past &&

        ($row['status'] === 'Available') &&

        ((int)$row['date_blocked'] === 0) &&

        ((int)$row['slot_booked'] === 0);

    $row_class =

        $is_available

        ? ''

        : 'unavailable';

?>

<tr class="<?php echo $row_class; ?>">

<!-- ID -->

<td

    class="fw-bold"

    style="color:#818cf8;"

>

    #<?php echo $row['id']; ?>

</td>

<!-- COURT -->

<td>

<div class="d-flex align-items-center gap-3">

<img

    src="../images/court<?php echo $row['id']; ?>.jpg"

    alt="Court Image"

    style="

        width:55px;

        height:40px;

        object-fit:cover;

        border-radius:8px;

        border:1px solid var(--credix-border);

    "

    onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=500&auto=format&fit=crop'"

>

<span class="fw-bold fs-6">

    <?php

    echo htmlspecialchars(

        $row['court_name']

    );

    ?>

</span>

</div>

</td>

<!-- PRICE -->

<td class="text-center">

    <span style="display:inline-block;white-space:nowrap;font-weight:800;color:#f8fafc;font-size:0.92rem;">

        RM <?php echo number_format((float)$row['price'], 2); ?> / hour

    </span>

</td>

<!-- STATUS -->

<td class="text-center">

<?php if ($is_available) { ?>

<span

    style="

        font-size:0.78rem;

        font-weight:800;

        color:#34d399;

    "

>

    <i class="fa-solid fa-circle-check me-1"></i>

    Available

</span>

<?php } else { ?>

<span

    style="

        font-size:0.78rem;

        font-weight:800;

        color:#f87171;

    "

>

    <i class="fa-solid fa-circle-xmark me-1"></i>

    Not Available

</span>

<?php } ?>

</td>

<!-- ACTION -->

<td class="text-end">

<?php if ($is_available) { ?>

<button

    type="submit"

    name="court"

    value="<?php echo $row['id']; ?>"

    class="btn btn-book w-100"

>

    <i class="fa-solid fa-calendar-check me-1"></i>

    Book Court

</button>

<?php } else { ?>

<button

    type="button"

    class="btn btn-dark w-100"

    disabled

    style="

        border-radius:50px;

        padding:10px;

        font-weight:700;

        font-size:0.85rem;

        opacity:0.5;

    "

>

    Unavailable

</button>

<?php } ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<!-- BACK / NEXT COURT -->

<div class="court-pagination">

<button

    type="button"

    class="court-page-btn"

    onclick="changeCourtPage(<?php echo $court_page - 1; ?>)"

    <?php echo $court_page <= 1 ? 'disabled' : ''; ?>

>

    <i class="fa-solid fa-arrow-left me-2"></i>

    Back

</button>

<span class="court-page-info">

    Page

    <?php echo $court_page; ?>

    of

    <?php echo $total_pages; ?>

</span>

<button

    type="button"

    class="court-page-btn"

    onclick="changeCourtPage(<?php echo $court_page + 1; ?>)"

    <?php echo $court_page >= $total_pages ? 'disabled' : ''; ?>

>

    Next

    <i class="fa-solid fa-arrow-right ms-2"></i>

</button>

</div>

</form>

</div>

</div>

<!-- Bootstrap -->

<script

    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"

></script>

<script>

/* DATE / DURATION / TIME - update without page reload or scrolling */

let dashboardUpdating = false;

async function refreshAvailability() {

    if (dashboardUpdating) return;

    const form = document.getElementById('bookingForm');

    const courtPage = document.getElementById('courtPage');

    if (courtPage) courtPage.value = 1;

    dashboardUpdating = true;

    const fixedScrollY = window.scrollY;

    try {

        const formData = new FormData(form);

        const response = await fetch('dashboard.php', {

            method: 'POST',

            body: formData,

            credentials: 'same-origin',

            headers: { 'X-Requested-With': 'XMLHttpRequest' }

        });

        if (!response.ok) throw new Error('Unable to update availability.');

        const html = await response.text();

        const doc = new DOMParser().parseFromString(html, 'text/html');

        const freshForm = doc.getElementById('bookingForm');

        if (!freshForm) throw new Error('Booking form not found.');

        // Replace only the form content. Browser page itself never reloads.

        form.innerHTML = freshForm.innerHTML;

        // Keep viewport at exactly the same position.

        window.scrollTo({ top: fixedScrollY, left: 0, behavior: 'instant' });

    } catch (error) {

        console.error(error);

        alert('Could not refresh court availability. Please try again.');

    } finally {

        dashboardUpdating = false;

    }

}

function updateDateCard(element) {

    document.querySelectorAll('.date-card').forEach(card => card.classList.remove('active'));

    element.classList.add('active');

    const radio = element.querySelector('input[type="radio"]');

    if (radio) {

        radio.checked = true;

        refreshAvailability();

    }

}

function updateDurationCard(element) {

    document.querySelectorAll('.duration-card').forEach(card => card.classList.remove('active'));

    element.classList.add('active');

    const radio = element.querySelector('input[type="radio"]');

    if (radio) {

        radio.checked = true;

        refreshAvailability();

    }

}

function updateStartTimeCard(element) {

    if (element.classList.contains('unavailable')) return;

    document.querySelectorAll('.start-time-card').forEach(card => card.classList.remove('active'));

    element.classList.add('active');

    const radio = element.querySelector('input[type="radio"]');

    if (radio) {

        radio.checked = true;

        refreshAvailability();

    }

}

/* COURT BACK / NEXT - AJAX too, so page stays still */

async function changeCourtPage(page) {

    if (page < 1 || dashboardUpdating) return;

    const courtPage = document.getElementById('courtPage');

    if (!courtPage) return;

    courtPage.value = page;

    dashboardUpdating = true;

    const fixedScrollY = window.scrollY;

    try {

        const form = document.getElementById('bookingForm');

        const formData = new FormData(form);

        const response = await fetch('dashboard.php', {

            method: 'POST',

            body: formData,

            credentials: 'same-origin',

            headers: { 'X-Requested-With': 'XMLHttpRequest' }

        });

        if (!response.ok) throw new Error('Unable to change court page.');

        const html = await response.text();

        const doc = new DOMParser().parseFromString(html, 'text/html');

        const freshForm = doc.getElementById('bookingForm');

        if (!freshForm) throw new Error('Booking form not found.');

        form.innerHTML = freshForm.innerHTML;

        window.scrollTo({ top: fixedScrollY, left: 0, behavior: 'instant' });

    } catch (error) {

        console.error(error);

        alert('Could not change court page. Please try again.');

    } finally {

        dashboardUpdating = false;

    }

}

</script>

</body>

</html>
