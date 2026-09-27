<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$booking_id = (int)($_POST['booking_id'] ?? $_GET['booking_id'] ?? ($_SESSION['latest_booking_id'] ?? 0));
$payment_method = trim($_POST['payment_method'] ?? ($_SESSION['selected_payment_method'] ?? 'MAE'));

$allowedMethods = ['MAE','Touch N Go','Bank Islam','Card Payment'];

if(!in_array($payment_method, $allowedMethods, true)){
    $payment_method = 'MAE';
}

$_SESSION['latest_booking_id'] = $booking_id;
$_SESSION['selected_payment_method'] = $payment_method;

// Ambil booking terakhir user
$stmt = $conn->prepare("
SELECT bookings.*, courts.court_name,
(courts.price * COALESCE(bookings.duration, 1)) AS price
FROM bookings
JOIN courts ON bookings.court_id = courts.id
WHERE bookings.user_id = ? AND bookings.id = ?
LIMIT 1
");

$stmt->bind_param("ii", $user_id, $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$booking || $booking['status'] !== 'Pending'){
    header("Location: my_booking.php");
    exit();
}

$payStmt = $conn->prepare("
SELECT payment_id
FROM payments
WHERE booking_id = ?
LIMIT 1
");

$payStmt->bind_param("i", $booking_id);
$payStmt->execute();
$alreadyPaid = $payStmt->get_result()->fetch_assoc();
$payStmt->close();

if($alreadyPaid){
    header("Location: my_booking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Upload Receipt - Badminton Kampung Panji</title>

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<style>

:root{
    --bg:#090a0f;
    --card:#13151f;
    --card2:#191b28;
    --border:rgba(255,255,255,.09);
    --accent:#6366f1;
    --purple:#a855f7;
    --muted:#94a3b8;
    --green:#4ade80;
    --red:#fb7185;
}

*{
    box-sizing:border-box;
}

html{
    scroll-behavior:smooth;
}

body{
    margin:0;
    font-family:'Plus Jakarta Sans',sans-serif;
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
    top:0;
    left:0;

    width:100vw;
    height:100vh;

    object-fit:cover;

    z-index:-3;

    filter:
        brightness(.22)
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
            rgba(168,85,247,.20),
            transparent 35%
        ),
        linear-gradient(
            180deg,
            rgba(9,10,15,.45),
            rgba(9,10,15,.92)
        );

    pointer-events:none;
}

/* =========================
   DECORATIVE GLOW
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
    bottom:-100px;
    right:-100px;

    background:#a855f7;
}

/* =========================
   NAVBAR
========================= */

.navbar{
    min-height:78px;

    padding:12px 40px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    background:rgba(9,10,15,.80);

    border-bottom:1px solid var(--border);

    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);

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

/* =========================
   LOGO
========================= */

.brand-logo-icon{
    width:53px;
    height:53px;

    display:flex;
    align-items:center;
    justify-content:center;

    flex-shrink:0;

    background:transparent;

    border:none;
    border-radius:10px;

    overflow:hidden;
}

.brand-logo-icon img{
    width:51px;
    height:51px;

    display:block;

    object-fit:contain;
    object-position:center;

    border-radius:9px;

    filter:none;
}

/* =========================
   BRAND TEXT
========================= */

.brand-text{
    display:flex;
    flex-direction:column;
    justify-content:center;
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
   NAV RIGHT
========================= */

.nav-right{
    display:flex;
    align-items:center;

    gap:12px;
}

.back-link{
    color:#cbd5e1;

    text-decoration:none;

    font-size:.82rem;
    font-weight:700;

    padding:9px 16px;

    border:1px solid var(--border);

    border-radius:50px;

    background:rgba(255,255,255,.03);

    transition:.2s;
}

.back-link:hover{
    color:#fff;

    background:rgba(255,255,255,.08);

    border-color:rgba(255,255,255,.16);
}

/* =========================
   PAGE
========================= */

.page{
    width:100%;

    display:flex;
    justify-content:center;

    padding:45px 20px 70px;
}

/* =========================
   CONTAINER
========================= */

.container{
    width:100%;
    max-width:520px;
}

/* =========================
   STEP INDICATOR
========================= */

.steps{
    display:flex;
    align-items:center;
    justify-content:center;

    margin-bottom:22px;
}

.step-item{
    display:flex;
    align-items:center;

    gap:7px;

    font-size:.68rem;
    font-weight:700;

    color:#64748b;

    white-space:nowrap;
}

.step-item.done{
    color:#a5b4fc;
}

.step-item.active{
    color:#fff;
}

.step-number{
    width:25px;
    height:25px;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    border:1px solid rgba(255,255,255,.12);

    background:rgba(255,255,255,.04);

    font-size:.65rem;
}

.step-item.done .step-number{
    background:rgba(99,102,241,.18);

    border-color:rgba(99,102,241,.45);

    color:#a5b4fc;
}

.step-item.active .step-number{
    background:linear-gradient(
        135deg,
        #6366f1,
        #a855f7
    );

    border-color:transparent;

    color:#fff;

    box-shadow:
        0 0 20px rgba(99,102,241,.35);
}

.step-line{
    width:28px;
    height:1px;

    margin:0 8px;

    background:rgba(255,255,255,.10);
}

/* =========================
   CARD
========================= */

.card{
    position:relative;

    background:
        linear-gradient(
            145deg,
            rgba(24,26,39,.94),
            rgba(15,16,25,.94)
        );

    backdrop-filter:blur(20px);
    -webkit-backdrop-filter:blur(20px);

    border:1px solid rgba(255,255,255,.10);

    border-radius:26px;

    padding:30px;

    box-shadow:
        0 30px 80px rgba(0,0,0,.55),
        inset 0 1px 0 rgba(255,255,255,.05);

    overflow:hidden;
}

.card::before{
    content:"";

    position:absolute;

    top:-80px;
    right:-80px;

    width:180px;
    height:180px;

    border-radius:50%;

    background:#6366f1;

    filter:blur(80px);

    opacity:.12;

    pointer-events:none;
}

/* =========================
   SECURE BADGE
========================= */

.secure-badge{
    width:max-content;

    margin:0 auto 12px;

    display:flex;
    align-items:center;

    gap:6px;

    padding:6px 11px;

    border-radius:50px;

    background:rgba(74,222,128,.08);

    border:1px solid rgba(74,222,128,.20);

    color:#86efac;

    font-size:.65rem;
    font-weight:800;

    letter-spacing:.6px;

    text-transform:uppercase;
}

/* =========================
   TITLE
========================= */

h2{
    text-align:center;

    margin:0;

    font-size:1.55rem;

    font-weight:800;

    letter-spacing:-.5px;
}

.subtitle{
    text-align:center;

    margin:8px auto 18px;

    color:var(--muted);

    font-size:.78rem;

    line-height:1.5;
}

/* =========================
   TIMER
========================= */

.timer-wrap{
    display:flex;
    justify-content:center;

    margin-bottom:20px;
}

.timer{
    display:inline-flex;
    align-items:center;

    gap:7px;

    background:rgba(244,63,94,.08);

    border:1px solid rgba(244,63,94,.20);

    color:#fda4af;

    border-radius:50px;

    padding:7px 13px;

    font-size:.72rem;

    font-weight:700;
}

.timer #time{
    color:#fff;

    font-weight:800;

    min-width:40px;
}

/* =========================
   ERROR
========================= */

.error-box{
    background:rgba(244,63,94,.10);

    border:1px solid rgba(244,63,94,.30);

    color:#fecaca;

    padding:10px 14px;

    border-radius:10px;

    font-size:13px;

    margin-bottom:14px;

    text-align:center;
}

/* =========================
   QR SECTION
========================= */

.qr-section{
    display:flex;
    flex-direction:column;
    align-items:center;

    margin:10px 0 24px;
}

.qr-box{
    position:relative;

    width:210px;
    height:210px;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:12px;

    background:#fff;

    border-radius:20px;

    box-shadow:
        0 15px 40px rgba(0,0,0,.35),
        0 0 35px rgba(99,102,241,.15);
}

.qr-box::before{
    content:"";

    position:absolute;

    inset:-5px;

    border-radius:24px;

    background:linear-gradient(
        135deg,
        #6366f1,
        #a855f7,
        #6366f1
    );

    z-index:-1;

    opacity:.65;
}

.qr-box img{
    width:100%;
    height:100%;

    object-fit:contain;

    border-radius:10px;
}

.scan-text{
    margin-top:13px;

    color:#94a3b8;

    font-size:.72rem;

    text-align:center;
}

.scan-text i{
    color:#818cf8;

    margin-right:4px;
}

/* =========================
   PAYMENT METHOD BADGE
========================= */

.method-box{
    display:flex;
    align-items:center;
    justify-content:space-between;

    gap:15px;

    padding:13px 15px;

    margin-bottom:12px;

    background:rgba(99,102,241,.08);

    border:1px solid rgba(99,102,241,.18);

    border-radius:14px;
}

.method-left{
    display:flex;
    align-items:center;

    gap:10px;
}

.method-icon{
    width:34px;
    height:34px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:10px;

    background:rgba(99,102,241,.16);

    color:#a5b4fc;
}

.method-label{
    color:#94a3b8;

    font-size:.65rem;

    margin-bottom:2px;
}

.method-name{
    color:#fff;

    font-size:.8rem;
    font-weight:700;
}

.method-status{
    color:#4ade80;

    font-size:.65rem;
    font-weight:700;
}

/* =========================
   DETAILS
========================= */

.details{
    font-size:.78rem;

    background:rgba(255,255,255,.025);

    border:1px solid var(--border);

    padding:14px 16px;

    border-radius:14px;

    margin:12px 0 20px;
}

.details div{
    display:flex;
    justify-content:space-between;

    gap:20px;

    padding:7px 0;

    color:#94a3b8;
}

.details div span:last-child{
    color:#fff;

    font-weight:600;

    text-align:right;
}

.details .total{
    margin-top:5px;

    padding-top:12px;

    border-top:1px solid var(--border);
}

.details .total span:first-child{
    color:#fff;

    font-weight:700;
}

.details .total span:last-child{
    color:#4ade80;

    font-size:1.1rem;

    font-weight:800;
}

/* =========================
   UPLOAD TITLE
========================= */

.upload-heading{
    display:flex;
    align-items:center;

    gap:8px;

    margin:22px 0 10px;

    color:#fff;

    font-size:.8rem;

    font-weight:700;
}

.upload-heading i{
    color:#818cf8;
}

/* =========================
   FILE UPLOAD
========================= */

.file-wrapper{
    position:relative;

    border:1px dashed rgba(129,140,248,.35);

    border-radius:14px;

    padding:15px;

    background:rgba(99,102,241,.04);

    transition:.2s;
}

.file-wrapper:hover{
    border-color:#818cf8;

    background:rgba(99,102,241,.08);
}

input[type="file"]{
    width:100%;

    color:#cbd5e1;

    font-size:.72rem;

    font-family:inherit;
}

input[type="file"]::file-selector-button{
    background:rgba(99,102,241,.16);

    color:#c7d2fe;

    border:1px solid rgba(99,102,241,.25);

    padding:9px 12px;

    border-radius:9px;

    cursor:pointer;

    margin-right:10px;

    font-family:inherit;

    font-size:.7rem;
    font-weight:700;
}

.file-note{
    margin-top:8px;

    color:#64748b;

    font-size:.62rem;

    line-height:1.5;
}

/* =========================
   BUTTON
========================= */

button{
    width:100%;

    margin-top:16px;

    padding:14px 20px;

    border:none;

    border-radius:50px;

    background:linear-gradient(
        135deg,
        #6366f1,
        #a855f7
    );

    color:#fff;

    font-family:inherit;

    font-size:.85rem;
    font-weight:800;

    cursor:pointer;

    box-shadow:
        0 8px 25px rgba(99,102,241,.25);

    transition:
        transform .2s,
        box-shadow .2s,
        opacity .2s;
}

button:hover{
    transform:translateY(-2px);

    box-shadow:
        0 12px 30px rgba(99,102,241,.35);
}

button:disabled{
    background:#374151;

    color:#9ca3af;

    cursor:not-allowed;

    transform:none;

    box-shadow:none;
}

/* =========================
   SECURITY NOTE
========================= */

.security-note{
    display:flex;
    justify-content:center;
    align-items:center;

    gap:6px;

    margin-top:14px;

    color:#64748b;

    font-size:.62rem;
}

.security-note i{
    color:#4ade80;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .navbar{
        min-height:70px;

        padding:10px 18px;
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

    .back-link{
        padding:8px 12px;

        font-size:.72rem;
    }

    .page{
        padding:30px 14px 50px;
    }

    .card{
        padding:24px 18px;

        border-radius:22px;
    }

    .steps{
        overflow-x:auto;

        justify-content:flex-start;

        padding-bottom:4px;
    }

    .step-line{
        width:18px;

        margin:0 5px;
    }

    .step-item{
        font-size:.6rem;
    }

    .qr-box{
        width:190px;
        height:190px;
    }
}

@media(max-width:480px){

    .brand-text{
        display:none;
    }

    .navbar{
        padding-left:14px;
        padding-right:14px;
    }

    .card{
        padding:22px 16px;
    }

    h2{
        font-size:1.35rem;
    }

    .details{
        padding:12px;
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

<nav class="navbar">

    <a
        href="dashboard.php"
        class="brand"
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

    <div class="nav-right">

        <a
            href="my_booking.php"
            class="back-link"
        >
            <i class="fa-solid fa-calendar-check"></i>
            My Booking
        </a>

    </div>

</nav>

<!-- =========================
     PAGE
========================= -->

<div class="page">

<div class="container">

    <!-- PAYMENT STEPS -->

    <div class="steps">

        <div class="step-item done">

            <div class="step-number">
                <i class="fa-solid fa-check"></i>
            </div>

            Select Method

        </div>

        <div class="step-line"></div>

        <div class="step-item done">

            <div class="step-number">
                <i class="fa-solid fa-check"></i>
            </div>

            Scan QR

        </div>

        <div class="step-line"></div>

        <div class="step-item active">

            <div class="step-number">
                3
            </div>

            Upload Receipt

        </div>

    </div>

    <!-- CARD -->

    <div class="card">

        <div class="secure-badge">

            <i class="fa-solid fa-shield-halved"></i>

            Secure Payment

        </div>

        <h2>
            Scan & Pay
        </h2>

        <div class="subtitle">
            Scan the DuitNow QR below using your selected
            payment method, then upload your receipt.
        </div>

        <!-- TIMER -->

        <div class="timer-wrap">

            <div class="timer">

                <i class="fa-regular fa-clock"></i>

                QR expires in

                <span id="time">
                    03:00
                </span>

            </div>

        </div>

        <!-- ERROR -->

        <?php if(isset($_GET['error'])): ?>

            <div class="error-box">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?= htmlspecialchars($_GET['error']) ?>

            </div>

        <?php endif; ?>

        <!-- QR -->

        <div class="qr-section">

            <div class="qr-box">

                <img
                    src="qr/qr.jpg"
                    alt="DuitNow QR Code"
                >

            </div>

            <div class="scan-text">

                <i class="fa-solid fa-mobile-screen-button"></i>

                Open your banking or e-wallet app to scan

            </div>

        </div>

        <!-- PAYMENT METHOD -->

        <div class="method-box">

            <div class="method-left">

                <div class="method-icon">

                    <i class="fa-solid fa-wallet"></i>

                </div>

                <div>

                    <div class="method-label">
                        Payment Method
                    </div>

                    <div class="method-name">
                        <?= htmlspecialchars($payment_method) ?>
                    </div>

                </div>

            </div>

            <div class="method-status">

                <i class="fa-solid fa-circle-check"></i>

                Selected

            </div>

        </div>

        <!-- BOOKING DETAILS -->

        <div class="details">

            <div>

                <span>
                    Court
                </span>

                <span>
                    <?= htmlspecialchars($booking['court_name']) ?>
                </span>

            </div>

            <div>

                <span>
                    Date
                </span>

                <span>
                    <?= htmlspecialchars($booking['booking_date']) ?>
                </span>

            </div>

            <div>

                <span>
                    Time
                </span>

                <span>
                    <?= htmlspecialchars($booking['booking_time']) ?>
                </span>

            </div>

            <div class="total">

                <span>
                    Amount
                </span>

                <span>
                    RM <?= number_format((float)$booking['price'], 2) ?>
                </span>

            </div>

        </div>

        <!-- UPLOAD -->

        <form
            action="payment_process.php"
            method="POST"
            enctype="multipart/form-data"
            id="form"
        >

            <input
                type="hidden"
                name="booking_id"
                value="<?= (int)$booking['id'] ?>"
            >

            <input
                type="hidden"
                name="payment_method"
                value="<?= htmlspecialchars($payment_method) ?>"
            >

            <input
                type="hidden"
                name="amount"
                value="<?= htmlspecialchars($booking['price']) ?>"
            >

            <div class="upload-heading">

                <i class="fa-solid fa-cloud-arrow-up"></i>

                Upload Payment Receipt

            </div>

            <div class="file-wrapper">

                <input
                    type="file"
                    name="receipt"
                    id="file"
                    accept="image/jpeg,image/png,image/webp,application/pdf"
                    required
                >

                <div class="file-note">

                    <i class="fa-solid fa-circle-info"></i>

                    Upload a clear screenshot or photo of your
                    successful payment receipt.

                </div>

            </div>

            <button
                type="submit"
                id="btn"
            >

                <i class="fa-solid fa-cloud-arrow-up"></i>

                &nbsp; Upload Receipt

            </button>

        </form>

        <div class="security-note">

            <i class="fa-solid fa-lock"></i>

            Your payment information is handled securely

        </div>

    </div>

</div>

</div>

<script>

let seconds = 180;

const timer = document.getElementById("time");
const btn = document.getElementById("btn");
const file = document.getElementById("file");

function updateTimer(){

    let m = Math.floor(seconds / 60);
    let s = seconds % 60;

    timer.textContent =
        String(m).padStart(2,'0')
        + ":"
        + String(s).padStart(2,'0');

    if(seconds <= 0){

        clearInterval(interval);

        btn.disabled = true;
        file.disabled = true;

        timer.textContent = "EXPIRED";

        alert("QR dah expired!");

        return;
    }

    seconds--;
}

updateTimer();

let interval = setInterval(
    updateTimer,
    1000
);

</script>

</body>

</html>