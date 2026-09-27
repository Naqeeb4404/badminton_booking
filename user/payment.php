<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$booking_id = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? ($_SESSION['latest_booking_id'] ?? 0));

if($booking_id > 0){
    $_SESSION['latest_booking_id'] = $booking_id;
}

// Ambil booking terakhir (must belong to this user)
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

if(!$booking){
    header("Location: my_booking.php");
    exit();
}

// Jika booking sudah dibayar / diproses,
// jangan benarkan pembayaran dibuat sekali lagi.
if($booking['status'] !== 'Pending'){
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
<html lang="ms">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Proceed to Payment - Badminton Kampung Panji</title>

<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap"
    rel="stylesheet"
>

<style>

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:'Inter',sans-serif;
    background:#120c1f;
    color:white;
    min-height:100vh;
}

/* =========================
   NAVBAR
========================= */

.navbar{
    width:100%;
    min-height:78px;
    padding:12px 40px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    background:rgba(9,10,15,.92);
    border-bottom:1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px);
}

/* =========================
   BRAND
========================= */

.brand{
    display:flex;
    align-items:center;
    gap:12px;

    text-decoration:none;
    color:white;
}

/* =========================
   LOGO
   SAMA MACAM DASHBOARD
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

    margin:0;
    padding:0;
}

.brand-logo-icon img{
    width:51px;
    height:51px;

    display:block;

    object-fit:contain;
    object-position:center;

    background:transparent;

    border:none;
    border-radius:9px;

    margin:0;
    padding:0;

    filter:none;
}

/* =========================
   BRAND TEXT
========================= */

.brand-text{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:flex-start;
}

.brand-text span{
    display:block;

    color:#f8fafc;

    font-size:.95rem;
    font-weight:800;

    letter-spacing:.5px;
    line-height:1.1;
}

.brand-text small{
    display:block;

    color:#a855f7;

    font-size:.65rem;
    font-weight:700;

    letter-spacing:1.5px;
    line-height:1;

    margin-top:5px;

    text-transform:uppercase;
}

/* =========================
   MY BOOKING
========================= */

.my-booking-link{
    color:#f8fafc;

    text-decoration:none;

    font-size:.88rem;
    font-weight:700;

    transition:.2s ease;
}

.my-booking-link:hover{
    color:#a855f7;
}

/* =========================
   PAGE
========================= */

.page{
    display:flex;
    justify-content:center;
    align-items:center;

    width:100%;

    padding:55px 20px;
}

/* =========================
   CONTAINER
========================= */

.container{
    width:100%;
    max-width:550px;
}

/* =========================
   CARD
========================= */

.card{
    background:#1e1435;

    padding:25px;

    border-radius:16px;

    box-shadow:
        0 8px 20px rgba(0,0,0,.3);
}

/* =========================
   TITLE
========================= */

.title{
    font-size:20px;
    font-weight:700;

    margin-bottom:15px;
}

/* =========================
   INFO
========================= */

.info-box{
    background:#271b45;

    border-radius:12px;

    padding:12px 14px;

    margin-bottom:16px;

    color:#ddd;

    font-size:13px;

    line-height:1.5;
}

.info-box strong{
    color:#fff;
}

/* =========================
   PAYMENT GRID
========================= */

.payment-grid{
    display:grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:12px;
}

/* =========================
   PAYMENT OPTION
========================= */

.payment-option{
    background:#271b45;

    padding:18px 10px;

    border-radius:12px;

    cursor:pointer;

    border:2px solid transparent;

    text-align:center;

    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:space-between;

    transition:all .2s ease;
}

.payment-option:hover{
    background:#2b1d4b;
}

.payment-option.selected{
    border-color:#8b5cf6;
    background:#2e1f52;
}

/* =========================
   PAYMENT LOGO
========================= */

.payment-logo{
    width:45px;
    height:45px;

    object-fit:contain;

    margin-bottom:10px;

    background:#ffffff;

    padding:6px;

    border-radius:10px;

    box-shadow:
        0 2px 5px rgba(0,0,0,.2);
}

/* =========================
   PAYMENT NAME
========================= */

.payment-name{
    font-size:13px;

    font-weight:bold;

    margin-bottom:6px;
}

/* =========================
   PAYMENT PRICE
========================= */

.payment-price{
    color:#4ade80;

    font-size:12px;

    font-weight:bold;
}

/* =========================
   SUMMARY
========================= */

.summary-box{
    margin-top:20px;

    background:#271b45;

    padding:15px;

    border-radius:12px;
}

.summary-box div{
    display:flex;

    justify-content:space-between;

    gap:20px;

    margin:8px 0;

    color:#ddd;
}

.summary-box div span:last-child{
    text-align:right;
}

/* =========================
   TOTAL
========================= */

.total span:last-child{
    color:#4ade80;

    font-size:18px;

    font-weight:bold;
}

/* =========================
   BUTTON
========================= */

button{
    margin-top:20px;

    width:100%;

    padding:14px;

    background:#7c3aed;

    border:none;

    border-radius:12px;

    color:white;

    font-size:16px;

    font-weight:bold;

    cursor:pointer;

    transition:
        background .2s,
        transform .2s;
}

button:hover{
    background:#6d28d9;

    transform:translateY(-1px);
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

        border-radius:9px;
    }

    .brand-logo-icon img{
        width:45px;
        height:45px;

        border-radius:8px;
    }

    .brand-text span{
        font-size:.85rem;
    }

    .brand-text small{
        font-size:.57rem;

        letter-spacing:1px;
    }

    .page{
        padding:35px 15px;
    }
}

@media(max-width:480px){

    .payment-grid{
        grid-template-columns:
            repeat(2,1fr);
    }

    .card{
        padding:20px 15px;
    }

    .navbar{
        padding-left:12px;
        padding-right:12px;
    }

    .brand{
        gap:8px;
    }

    .brand-text span{
        font-size:.78rem;
    }

    .brand-text small{
        font-size:.52rem;
    }

    .my-booking-link{
        font-size:.78rem;
    }
}

</style>

</head>

<body>

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

    <a
        href="my_booking.php"
        class="my-booking-link"
    >
        My Booking
    </a>

</nav>

<!-- =========================
     PAGE
========================= -->

<div class="page">

<div class="container">

    <div class="card">

        <div class="title">
            Proceed to Payment
        </div>

        <div class="info-box">

            <strong>
                Next step:
            </strong>

            Choose your payment method,
            continue to the QR page,
            then upload your payment receipt.

        </div>

        <form
            action="payment_qr.php"
            method="POST"
        >

            <div class="payment-grid">

                <!-- MAE -->
                <div
                    class="payment-option selected"
                    onclick="selectPayment(this,'MAE')"
                >

                    <img
                        src="qr/mae.png"
                        alt="MAE"
                        class="payment-logo"
                    >

                    <div class="payment-name">
                        MAE
                    </div>

                    <div class="payment-price">
                        RM <?= number_format((float)$booking['price'], 2); ?>
                    </div>

                </div>

                <!-- TOUCH N GO -->
                <div
                    class="payment-option"
                    onclick="selectPayment(this,'Touch N Go')"
                >

                    <img
                        src="qr/TouchAndgo.png"
                        alt="Touch N Go"
                        class="payment-logo"
                    >

                    <div class="payment-name">
                        Touch 'n Go
                    </div>

                    <div class="payment-price">
                        RM <?= number_format((float)$booking['price'], 2); ?>
                    </div>

                </div>

                <!-- BANK ISLAM -->
                <div
                    class="payment-option"
                    onclick="selectPayment(this,'Bank Islam')"
                >

                    <img
                        src="qr/bankislam.jpg"
                        alt="Bank Islam"
                        class="payment-logo"
                    >

                    <div class="payment-name">
                        Bank Islam
                    </div>

                    <div class="payment-price">
                        RM <?= number_format((float)$booking['price'], 2); ?>
                    </div>

                </div>

                <!-- CARD -->
                <div
                    class="payment-option"
                    onclick="selectPayment(this,'Card Payment')"
                >

                    <img
                        src="qr/card.jpg"
                        alt="Card Payment"
                        class="payment-logo"
                    >

                    <div class="payment-name">
                        Card Payment
                    </div>

                    <div class="payment-price">
                        RM <?= number_format((float)$booking['price'], 2); ?>
                    </div>

                </div>

            </div>

            <!-- HIDDEN DATA -->

            <input
                type="hidden"
                name="booking_id"
                value="<?= (int)$booking['id']; ?>"
            >

            <input
                type="hidden"
                name="amount"
                value="<?= htmlspecialchars($booking['price']); ?>"
            >

            <input
                type="hidden"
                name="payment_method"
                id="selectedMethod"
                value="MAE"
            >

            <!-- SUMMARY -->

            <div class="summary-box">

                <div>

                    <span>
                        Court
                    </span>

                    <span>
                        <?= htmlspecialchars($booking['court_name']); ?>
                    </span>

                </div>

                <div>

                    <span>
                        Date
                    </span>

                    <span>
                        <?= htmlspecialchars($booking['booking_date']); ?>
                    </span>

                </div>

                <div>

                    <span>
                        Time
                    </span>

                    <span>
                        <?= htmlspecialchars($booking['booking_time']); ?>
                    </span>

                </div>

                <div class="total">

                    <span>
                        Total
                    </span>

                    <span>
                        RM <?= number_format((float)$booking['price'], 2); ?>
                    </span>

                </div>

            </div>

            <button type="submit">
                Proceed to Payment
            </button>

        </form>

    </div>

</div>

</div>

<script>

function selectPayment(element, method){

    document
        .querySelectorAll('.payment-option')
        .forEach(item => {

            item.classList.remove('selected');

        });

    element.classList.add('selected');

    document.getElementById(
        'selectedMethod'
    ).value = method;

}

</script>

</body>
</html>