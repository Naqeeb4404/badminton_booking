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

$stmt = mysqli_prepare(
    $conn,
    'SELECT id, court_name, price, status FROM courts WHERE id = ? LIMIT 1'
);

mysqli_stmt_bind_param($stmt, 'i', $courtId);
mysqli_stmt_execute($stmt);

$court = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

if (!$court || $court['status'] !== 'Available') {
    header('Location: dashboard.php?error=' . urlencode('Selected court is not available.'));
    exit();
}

$start = strtotime(
    $date . ' ' . substr($time, 0, 5)
);

$end = $start + ($duration * 3600);

if (!$start || $start <= time()) {
    header('Location: dashboard.php?error=' . urlencode('Selected time has already passed.'));
    exit();
}

/* CHECK DOUBLE BOOKING */
$conflict = false;

for ($i = 0; $i < $duration; $i++) {

    $slot = date(
        'H:i:s',
        $start + ($i * 3600)
    );

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

    mysqli_stmt_bind_param(
        $check,
        'iss',
        $courtId,
        $date,
        $slot
    );

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

    header(
        'Location: dashboard.php?error=' .
        urlencode(
            'This court/time is already booked. Please choose another slot.'
        )
    );

    exit();
}

/* PRICE */
$pricePerHour = (float)$court['price'];
$total = $pricePerHour * $duration;

/* DISPLAY */
$displayDate = date(
    'D, d M Y',
    strtotime($date)
);

$startTime = date(
    'H:i',
    $start
);

$endTime = date(
    'H:i',
    $end
);
?>

<!DOCTYPE html>
<html lang="ms">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Confirm Booking - Badminton Kampung Panji
</title>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<style>

:root{
    --bg:#090a0f;
    --card:#151722;
    --border:rgba(255,255,255,.09);
    --accent:#6366f1;
    --purple:#a855f7;
    --muted:#94a3b8;
    --green:#4ade80;
}

*{
    box-sizing:border-box;
}

body{
    margin:0;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    background:#090a0f;

    color:#fff;

    min-height:100vh;

    overflow-x:hidden;
}

/* =========================
   BACKGROUND VIDEO
========================= */

.bg-video{
    position:fixed;

    inset:0;

    width:100vw;
    height:100vh;

    object-fit:cover;

    z-index:-3;

    filter:
        brightness(.20)
        saturate(.8);
}

/* =========================
   BACKGROUND OVERLAY
========================= */

.bg-overlay{
    position:fixed;

    inset:0;

    z-index:-2;

    background:
        radial-gradient(
            circle at 15% 20%,
            rgba(99,102,241,.25),
            transparent 35%
        ),
        radial-gradient(
            circle at 85% 70%,
            rgba(168,85,247,.18),
            transparent 35%
        ),
        linear-gradient(
            180deg,
            rgba(9,10,15,.45),
            rgba(9,10,15,.95)
        );

    pointer-events:none;
}

/* =========================
   GLOW
========================= */

.glow{
    position:fixed;

    width:350px;
    height:350px;

    border-radius:50%;

    background:#6366f1;

    filter:blur(150px);

    opacity:.12;

    z-index:-1;

    pointer-events:none;
}

.glow-one{
    top:80px;
    left:-120px;
}

.glow-two{
    right:-100px;
    bottom:-100px;

    background:#a855f7;
}

/* =========================
   NAVBAR
========================= */

.nav{
    min-height:78px;

    padding:12px 40px;

    display:flex;

    align-items:center;
    justify-content:space-between;

    background:
        rgba(9,10,15,.80);

    border-bottom:
        1px solid var(--border);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    position:relative;

    z-index:10;
}

/* =========================
   BRAND
========================= */

.brand{
    display:flex;

    align-items:center;

    gap:12px;

    text-decoration:none;
}

.brand-logo-icon{
    width:53px;
    height:53px;

    display:flex;

    align-items:center;
    justify-content:center;

    flex-shrink:0;

    border-radius:10px;

    overflow:hidden;
}

.brand-logo-icon img{
    width:51px;
    height:51px;

    display:block;

    object-fit:contain;

    border-radius:9px;
}

.brand-text{
    display:flex;

    flex-direction:column;
}

.brand-text span{
    color:#f8fafc;

    font-size:.95rem;

    font-weight:800;

    letter-spacing:.5px;

    line-height:1.1;
}

.brand-text small{
    color:#a855f7;

    font-size:.65rem;

    font-weight:700;

    letter-spacing:1.5px;

    margin-top:5px;

    text-transform:uppercase;
}

/* =========================
   MY BOOKING
========================= */

.my-booking-link{
    color:#cbd5e1;

    text-decoration:none;

    font-size:.82rem;

    font-weight:700;

    padding:9px 16px;

    border:
        1px solid var(--border);

    border-radius:50px;

    background:
        rgba(255,255,255,.03);

    transition:.2s;
}

.my-booking-link:hover{
    color:#fff;

    background:
        rgba(255,255,255,.08);
}

/* =========================
   PAGE
========================= */

.page{
    width:100%;

    display:flex;

    justify-content:center;

    padding:
        35px 20px 70px;
}

.container{
    width:100%;

    max-width:680px;
}

/* =========================
   HEADER
========================= */

.page-header{
    text-align:center;

    margin-bottom:20px;
}

.header-badge{
    display:inline-flex;

    align-items:center;

    gap:6px;

    padding:6px 12px;

    margin-bottom:11px;

    border-radius:50px;

    background:
        rgba(99,102,241,.10);

    border:
        1px solid
        rgba(99,102,241,.25);

    color:#a5b4fc;

    font-size:.63rem;

    font-weight:800;

    letter-spacing:1px;

    text-transform:uppercase;
}

.page-header h1{
    margin:0;

    font-size:1.7rem;

    font-weight:800;

    letter-spacing:-.6px;
}

.page-header p{
    margin:
        8px 0 0;

    color:var(--muted);

    font-size:.78rem;
}

/* =========================
   BOOKING PASS
========================= */

.booking-pass{
    position:relative;

    background:
        linear-gradient(
            145deg,
            rgba(24,26,39,.96),
            rgba(14,15,23,.96)
        );

    border:
        1px solid
        rgba(255,255,255,.10);

    border-radius:26px;

    overflow:hidden;

    box-shadow:
        0 30px 80px
        rgba(0,0,0,.55);

    backdrop-filter:
        blur(20px);
}

/* =========================
   COURT HERO
========================= */

.court-hero{
    position:relative;

    width:100%;

    height:280px;

    overflow:hidden;

    background:#11131d;
}

.court-hero img{
    width:100%;
    height:100%;

    display:block;

    object-fit:cover;

    transition:
        transform .5s ease;
}

.booking-pass:hover
.court-hero img{
    transform:
        scale(1.025);
}

.court-overlay{
    position:absolute;

    inset:0;

    background:
        linear-gradient(
            180deg,
            rgba(5,6,10,.08) 10%,
            rgba(5,6,10,.25) 45%,
            rgba(10,11,18,.97) 100%
        );
}

/* =========================
   COURT BADGE
========================= */

.court-badge{
    position:absolute;

    top:18px;
    left:18px;

    display:inline-flex;

    align-items:center;

    gap:7px;

    padding:8px 13px;

    border-radius:50px;

    background:
        rgba(10,11,18,.70);

    border:
        1px solid
        rgba(255,255,255,.14);

    backdrop-filter:
        blur(12px);

    color:#fff;

    font-size:.68rem;

    font-weight:800;

    letter-spacing:.5px;
}

.court-badge i{
    color:#a5b4fc;
}

/* =========================
   AVAILABLE BADGE
========================= */

.available-badge{
    position:absolute;

    top:18px;
    right:18px;

    display:flex;

    align-items:center;

    gap:5px;

    padding:7px 11px;

    border-radius:50px;

    background:
        rgba(34,197,94,.16);

    border:
        1px solid
        rgba(74,222,128,.30);

    color:#86efac;

    font-size:.62rem;

    font-weight:800;

    backdrop-filter:
        blur(12px);
}

/* =========================
   COURT HERO CONTENT
========================= */

.hero-content{
    position:absolute;

    left:0;
    right:0;
    bottom:0;

    padding:
        25px 25px 22px;
}

.hero-small{
    display:block;

    color:#c7d2fe;

    font-size:.61rem;

    font-weight:800;

    letter-spacing:1.5px;

    margin-bottom:5px;

    text-transform:uppercase;
}

.hero-title{
    margin:0;

    color:#fff;

    font-size:1.55rem;

    font-weight:800;

    letter-spacing:-.4px;
}

/* =========================
   QUICK SUMMARY
========================= */

.quick-summary{
    display:grid;

    grid-template-columns:
        1.3fr 1fr .8fr;

    border-top:
        1px solid
        rgba(255,255,255,.08);

    border-bottom:
        1px solid
        rgba(255,255,255,.08);

    background:
        rgba(255,255,255,.025);
}

.quick-item{
    position:relative;

    padding:
        17px 18px;

    display:flex;

    align-items:center;

    gap:10px;
}

.quick-item:not(:last-child){
    border-right:
        1px solid
        rgba(255,255,255,.08);
}

.quick-icon{
    width:34px;
    height:34px;

    flex-shrink:0;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:10px;

    background:
        rgba(99,102,241,.12);

    color:#a5b4fc;

    font-size:.78rem;
}

.quick-text small{
    display:block;

    color:#64748b;

    font-size:.57rem;

    font-weight:700;

    margin-bottom:3px;

    text-transform:uppercase;

    letter-spacing:.6px;
}

.quick-text strong{
    display:block;

    color:#fff;

    font-size:.72rem;

    font-weight:800;
}

/* =========================
   PASS CONTENT
========================= */

.pass-content{
    padding:
        24px 26px 27px;
}

/* =========================
   CUSTOMER
========================= */

.customer{
    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:20px;

    padding-bottom:18px;

    margin-bottom:8px;

    border-bottom:
        1px dashed
        rgba(255,255,255,.13);
}

.customer-left{
    display:flex;

    align-items:center;

    gap:11px;
}

.avatar{
    width:40px;
    height:40px;

    display:flex;

    align-items:center;
    justify-content:center;

    flex-shrink:0;

    border-radius:12px;

    background:
        linear-gradient(
            135deg,
            rgba(99,102,241,.20),
            rgba(168,85,247,.15)
        );

    border:
        1px solid
        rgba(99,102,241,.20);

    color:#a5b4fc;
}

.customer-info small{
    display:block;

    color:#64748b;

    font-size:.6rem;

    margin-bottom:2px;
}

.customer-info strong{
    color:#fff;

    font-size:.8rem;
}

/* =========================
   BOOKING ID STYLE
========================= */

.pass-status{
    display:flex;

    align-items:center;

    gap:5px;

    color:#86efac;

    font-size:.62rem;

    font-weight:800;
}

/* =========================
   DETAILS
========================= */

.details{
    padding-top:4px;
}

.detail{
    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:20px;

    padding:12px 0;

    border-bottom:
        1px solid
        rgba(255,255,255,.07);

    color:var(--muted);

    font-size:.76rem;
}

.detail:last-child{
    border-bottom:none;
}

.detail-label{
    display:flex;

    align-items:center;

    gap:9px;
}

.detail-label i{
    width:18px;

    text-align:center;

    color:#818cf8;
}

.detail strong{
    color:#fff;

    text-align:right;

    font-weight:700;
}

/* =========================
   TOTAL
========================= */

.total-section{
    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:20px;

    margin-top:8px;

    padding:
        18px 0 4px;

    border-top:
        1px dashed
        rgba(255,255,255,.14);
}

.total-label small{
    display:block;

    color:#64748b;

    font-size:.58rem;

    margin-bottom:3px;

    text-transform:uppercase;

    letter-spacing:.7px;
}

.total-label strong{
    font-size:.82rem;

    color:#fff;
}

.total-price{
    color:var(--green);

    font-size:1.55rem;

    font-weight:800;

    letter-spacing:-.5px;
}

/* =========================
   ACTIONS
========================= */

.actions{
    display:grid;

    grid-template-columns:
        1fr 1.45fr;

    gap:10px;

    margin-top:22px;
}

.btn-confirm,
.btn-back{
    min-height:50px;

    border-radius:50px;

    font-family:inherit;

    font-size:.77rem;

    font-weight:800;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:7px;

    text-decoration:none;

    transition:.2s;
}

.btn-confirm{
    border:none;

    background:
        linear-gradient(
            135deg,
            #6366f1,
            #a855f7
        );

    color:#fff;

    cursor:pointer;

    box-shadow:
        0 8px 25px
        rgba(99,102,241,.25);
}

.btn-confirm:hover{
    transform:
        translateY(-2px);

    box-shadow:
        0 12px 30px
        rgba(99,102,241,.35);
}

.btn-back{
    border:
        1px solid
        var(--border);

    color:#cbd5e1;

    background:
        rgba(255,255,255,.025);
}

.btn-back:hover{
    color:#fff;

    background:
        rgba(255,255,255,.07);
}

/* =========================
   SECURITY NOTE
========================= */

.security-note{
    display:flex;

    justify-content:center;

    align-items:center;

    gap:6px;

    margin-top:15px;

    color:#64748b;

    font-size:.61rem;
}

.security-note i{
    color:#4ade80;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .nav{
        min-height:70px;

        padding:
            10px 18px;
    }

    .brand-logo-icon{
        width:47px;
        height:47px;
    }

    .brand-logo-icon img{
        width:45px;
        height:45px;
    }

    .brand-text span{
        font-size:.85rem;
    }

    .brand-text small{
        font-size:.57rem;
    }

    .page{
        padding:
            25px 14px 50px;
    }

    .court-hero{
        height:240px;
    }

    .quick-summary{
        grid-template-columns:1fr;
    }

    .quick-item:not(:last-child){
        border-right:none;

        border-bottom:
            1px solid
            rgba(255,255,255,.07);
    }

    .pass-content{
        padding:
            20px 18px 23px;
    }
}

@media(max-width:480px){

    .brand-text{
        display:none;
    }

    .nav{
        padding-left:14px;
        padding-right:14px;
    }

    .my-booking-link{
        padding:8px 11px;

        font-size:.7rem;
    }

    .page-header h1{
        font-size:1.45rem;
    }

    .court-hero{
        height:220px;
    }

    .court-badge{
        top:13px;
        left:13px;
    }

    .available-badge{
        top:13px;
        right:13px;
    }

    .hero-content{
        padding:
            20px 17px;
    }

    .hero-title{
        font-size:1.3rem;
    }

    .customer{
        align-items:flex-start;
    }

    .actions{
        grid-template-columns:1fr;
    }

    .btn-confirm{
        order:1;
    }

    .btn-back{
        order:2;
    }
}

</style>

</head>

<body>

<!-- BACKGROUND VIDEO -->
<video
    autoplay
    loop
    muted
    playsinline
    class="bg-video"
>
    <source
        src="sports.mp4"
        type="video/mp4"
    >
</video>

<div class="bg-overlay"></div>

<div class="glow glow-one"></div>
<div class="glow glow-two"></div>

<!-- =========================
     NAVBAR
========================= -->

<nav class="nav">

    <a
        class="brand"
        href="dashboard.php"
    >

        <div class="brand-logo-icon">

            <img
                src="../logo-badminton.png"
                alt="Badminton Kampung Panji"
            >

        </div>

        <div class="brand-text">

            <span>
                BADMINTON
            </span>

            <small>
                KAMPUNG PANJI
            </small>

        </div>

    </a>

    <a
        class="my-booking-link"
        href="my_booking.php"
    >

        <i class="fa-solid fa-calendar-check"></i>

        My Booking

    </a>

</nav>

<!-- =========================
     PAGE
========================= -->

<div class="page">

<div class="container">

    <!-- HEADER -->

    <div class="page-header">

        <div class="header-badge">

            <i class="fa-solid fa-ticket"></i>

            Booking Pass

        </div>

        <h1>
            Ready to Play?
        </h1>

        <p>
            Review your court details before confirming your booking.
        </p>

    </div>

    <!-- =========================
         BOOKING PASS
    ========================== -->

    <div class="booking-pass">

        <!-- COURT IMAGE -->

        <div class="court-hero">

            <img
                src="../images/court<?= (int)$courtId ?>.jpg"
                alt="<?= htmlspecialchars($court['court_name']) ?>"
                onerror="
                    this.onerror=null;
                    this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=1200&auto=format&fit=crop';
                "
            >

            <div class="court-overlay"></div>

            <!-- COURT BADGE -->

            <div class="court-badge">

                <i class="fa-solid fa-table-tennis-paddle-ball"></i>

                <?= htmlspecialchars($court['court_name']) ?>

            </div>

            <!-- AVAILABLE -->

            <div class="available-badge">

                <i class="fa-solid fa-circle-check"></i>

                Available

            </div>

            <!-- HERO TEXT -->

            <div class="hero-content">

                <span class="hero-small">
                    YOUR SELECTED COURT
                </span>

                <h2 class="hero-title">

                    <?= htmlspecialchars($court['court_name']) ?>

                </h2>

            </div>

        </div>

        <!-- =========================
             QUICK SUMMARY
        ========================== -->

        <div class="quick-summary">

            <!-- DATE -->

            <div class="quick-item">

                <div class="quick-icon">

                    <i class="fa-solid fa-calendar-days"></i>

                </div>

                <div class="quick-text">

                    <small>
                        Date
                    </small>

                    <strong>
                        <?= htmlspecialchars($displayDate) ?>
                    </strong>

                </div>

            </div>

            <!-- TIME -->

            <div class="quick-item">

                <div class="quick-icon">

                    <i class="fa-regular fa-clock"></i>

                </div>

                <div class="quick-text">

                    <small>
                        Time
                    </small>

                    <strong>

                        <?= htmlspecialchars($startTime) ?>

                        -

                        <?= htmlspecialchars($endTime) ?>

                    </strong>

                </div>

            </div>

            <!-- DURATION -->

            <div class="quick-item">

                <div class="quick-icon">

                    <i class="fa-solid fa-hourglass-half"></i>

                </div>

                <div class="quick-text">

                    <small>
                        Duration
                    </small>

                    <strong>

                        <?= $duration ?>

                        Hour<?= $duration > 1 ? 's' : '' ?>

                    </strong>

                </div>

            </div>

        </div>

        <!-- =========================
             PASS CONTENT
        ========================== -->

        <div class="pass-content">

            <!-- CUSTOMER -->

            <div class="customer">

                <div class="customer-left">

                    <div class="avatar">

                        <i class="fa-solid fa-user"></i>

                    </div>

                    <div class="customer-info">

                        <small>
                            BOOKED FOR
                        </small>

                        <strong>

                            <?= htmlspecialchars(
                                $_SESSION['user']['name']
                            ) ?>

                        </strong>

                    </div>

                </div>

                <div class="pass-status">

                    <i class="fa-solid fa-circle-check"></i>

                    Ready to Book

                </div>

            </div>

            <!-- DETAILS -->

            <div class="details">

                <div class="detail">

                    <div class="detail-label">

                        <i class="fa-solid fa-location-dot"></i>

                        <span>
                            Court
                        </span>

                    </div>

                    <strong>

                        <?= htmlspecialchars(
                            $court['court_name']
                        ) ?>

                    </strong>

                </div>

                <div class="detail">

                    <div class="detail-label">

                        <i class="fa-solid fa-tag"></i>

                        <span>
                            Rate
                        </span>

                    </div>

                    <strong>

                        RM <?= number_format(
                            $pricePerHour,
                            2
                        ) ?> / hour

                    </strong>

                </div>

                <div class="detail">

                    <div class="detail-label">

                        <i class="fa-solid fa-stopwatch"></i>

                        <span>
                            Playing Time
                        </span>

                    </div>

                    <strong>

                        <?= $duration ?>

                        Hour<?= $duration > 1 ? 's' : '' ?>

                    </strong>

                </div>

            </div>

            <!-- TOTAL -->

            <div class="total-section">

                <div class="total-label">

                    <small>
                        Total Payment
                    </small>

                    <strong>
                        Booking Amount
                    </strong>

                </div>

                <div class="total-price">

                    RM <?= number_format(
                        $total,
                        2
                    ) ?>

                </div>

            </div>

            <!-- =========================
                 FORM
            ========================== -->

            <form
                method="POST"
                action="create_booking.php"
            >

                <input
                    type="hidden"
                    name="date"
                    value="<?= htmlspecialchars($date) ?>"
                >

                <input
                    type="hidden"
                    name="time"
                    value="<?= htmlspecialchars($time) ?>"
                >

                <input
                    type="hidden"
                    name="duration"
                    value="<?= $duration ?>"
                >

                <input
                    type="hidden"
                    name="court_id"
                    value="<?= $courtId ?>"
                >

                <div class="actions">

                    <a
                        href="dashboard.php"
                        class="btn-back"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Change Selection

                    </a>

                    <button
                        type="submit"
                        class="btn-confirm"
                    >

                        Confirm & Continue

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </div>

            </form>

            <!-- SECURITY -->

            <div class="security-note">

                <i class="fa-solid fa-lock"></i>

                Your booking details are securely processed

            </div>

        </div>

    </div>

</div>

</div>

</body>

</html>