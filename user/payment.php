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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--bg:#090a0f;--card:#13151f;--border:rgba(255,255,255,.09);--accent:#6366f1;--purple:#a855f7;--muted:#94a3b8;--green:#4ade80}
*{box-sizing:border-box}
body{margin:0;font-family:'Plus Jakarta Sans',sans-serif;background:#090a0f;color:#fff;min-height:100vh;overflow-x:hidden}
.bg-video{position:fixed;inset:0;width:100vw;height:100vh;object-fit:cover;z-index:-3;filter:brightness(.22) saturate(.8)}
.bg-overlay{position:fixed;inset:0;z-index:-2;background:radial-gradient(circle at 15% 20%,rgba(99,102,241,.25),transparent 35%),radial-gradient(circle at 85% 70%,rgba(168,85,247,.20),transparent 35%),linear-gradient(180deg,rgba(9,10,15,.45),rgba(9,10,15,.94));pointer-events:none}
.glow{position:fixed;width:350px;height:350px;border-radius:50%;background:#6366f1;filter:blur(150px);opacity:.12;z-index:-1;pointer-events:none}
.glow-one{top:80px;left:-120px}.glow-two{bottom:-100px;right:-100px;background:#a855f7}
.navbar{min-height:78px;padding:12px 40px;display:flex;align-items:center;justify-content:space-between;background:rgba(9,10,15,.80);border-bottom:1px solid var(--border);backdrop-filter:blur(18px);position:relative;z-index:10}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}
.brand-logo-icon{width:53px;height:53px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:transparent;border:0;border-radius:10px;overflow:hidden}
.brand-logo-icon img{width:51px;height:51px;display:block;object-fit:contain;border-radius:9px}
.brand-text{display:flex;flex-direction:column}.brand-text span{color:#f8fafc;font-size:.95rem;font-weight:800;letter-spacing:.5px;line-height:1.1}.brand-text small{color:#a855f7;font-size:.65rem;font-weight:700;letter-spacing:1.5px;margin-top:5px;text-transform:uppercase}
.my-booking-link{color:#cbd5e1;text-decoration:none;font-size:.82rem;font-weight:700;padding:9px 16px;border:1px solid var(--border);border-radius:50px;background:rgba(255,255,255,.03);transition:.2s}.my-booking-link:hover{color:#fff;background:rgba(255,255,255,.08)}
.page{width:100%;display:flex;justify-content:center;padding:45px 20px 70px}.container{width:100%;max-width:590px}
.steps{display:flex;align-items:center;justify-content:center;margin-bottom:22px}.step-item{display:flex;align-items:center;gap:7px;font-size:.68rem;font-weight:700;color:#64748b;white-space:nowrap}.step-item.done{color:#a5b4fc}.step-item.active{color:#fff}.step-number{width:25px;height:25px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);font-size:.65rem}.step-item.done .step-number{background:rgba(99,102,241,.18);border-color:rgba(99,102,241,.45);color:#a5b4fc}.step-item.active .step-number{background:linear-gradient(135deg,#6366f1,#a855f7);border-color:transparent;box-shadow:0 0 20px rgba(99,102,241,.35)}.step-line{width:28px;height:1px;margin:0 8px;background:rgba(255,255,255,.10)}
.card{position:relative;background:linear-gradient(145deg,rgba(24,26,39,.94),rgba(15,16,25,.94));backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.10);border-radius:26px;padding:30px;box-shadow:0 30px 80px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.05);overflow:hidden}.card:before{content:"";position:absolute;top:-80px;right:-80px;width:180px;height:180px;border-radius:50%;background:#6366f1;filter:blur(80px);opacity:.12;pointer-events:none}
.secure-badge{width:max-content;margin:0 auto 12px;display:flex;align-items:center;gap:6px;padding:6px 11px;border-radius:50px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.20);color:#86efac;font-size:.65rem;font-weight:800;letter-spacing:.6px;text-transform:uppercase}
.title{text-align:center;font-size:1.55rem;font-weight:800;letter-spacing:-.5px;margin-bottom:7px}.subtitle{text-align:center;color:var(--muted);font-size:.78rem;line-height:1.5;margin-bottom:22px}
.info-box{background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.16);border-radius:14px;padding:12px 14px;margin-bottom:18px;color:#cbd5e1;font-size:.75rem;line-height:1.5}.info-box strong{color:#fff}
.payment-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.payment-option{position:relative;background:rgba(255,255,255,.025);padding:18px 10px;border-radius:16px;cursor:pointer;border:1px solid var(--border);text-align:center;display:flex;flex-direction:column;align-items:center;transition:.2s}.payment-option:hover{transform:translateY(-2px);border-color:rgba(129,140,248,.4);background:rgba(99,102,241,.06)}.payment-option.selected{border-color:#818cf8;background:linear-gradient(145deg,rgba(99,102,241,.15),rgba(168,85,247,.09));box-shadow:0 0 24px rgba(99,102,241,.12)}.payment-option.selected:after{content:"✓";position:absolute;top:9px;right:10px;width:21px;height:21px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6366f1,#a855f7);font-size:.65rem;font-weight:800}
.payment-logo{width:52px;height:52px;object-fit:contain;margin-bottom:10px;background:#fff;padding:7px;border-radius:13px;box-shadow:0 5px 15px rgba(0,0,0,.25)}.payment-name{font-size:.78rem;font-weight:800;margin-bottom:6px}.payment-price{color:var(--green);font-size:.72rem;font-weight:800}
.summary-box{margin-top:18px;background:rgba(255,255,255,.025);border:1px solid var(--border);padding:14px 16px;border-radius:14px}.summary-box div{display:flex;justify-content:space-between;gap:20px;padding:7px 0;color:var(--muted);font-size:.78rem}.summary-box div span:last-child{color:#fff;text-align:right;font-weight:600}.summary-box .total{margin-top:5px;padding-top:12px;border-top:1px solid var(--border)}.summary-box .total span:first-child{color:#fff;font-weight:700}.summary-box .total span:last-child{color:var(--green);font-size:1.1rem;font-weight:800}
button{margin-top:18px;width:100%;padding:14px 20px;background:linear-gradient(135deg,#6366f1,#a855f7);border:0;border-radius:50px;color:#fff;font-family:inherit;font-size:.85rem;font-weight:800;cursor:pointer;box-shadow:0 8px 25px rgba(99,102,241,.25);transition:.2s}button:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(99,102,241,.35)}
.security-note{display:flex;justify-content:center;align-items:center;gap:6px;margin-top:14px;color:#64748b;font-size:.62rem}.security-note i{color:#4ade80}
@media(max-width:768px){.navbar{min-height:70px;padding:10px 18px}.brand-logo-icon{width:47px;height:47px}.brand-logo-icon img{width:45px;height:45px}.brand-text span{font-size:.85rem}.brand-text small{font-size:.57rem}.page{padding:30px 14px 50px}.card{padding:24px 18px;border-radius:22px}.step-line{width:18px;margin:0 5px}.step-item{font-size:.6rem}}
@media(max-width:480px){.brand-text{display:none}.payment-grid{grid-template-columns:repeat(2,1fr)}.navbar{padding-left:14px;padding-right:14px}.card{padding:22px 16px}.title{font-size:1.35rem}}
</style>
</head>
<body>

<video autoplay loop muted playsinline class="bg-video">
    <source src="sports.mp4" type="video/mp4">
</video>
<div class="bg-overlay"></div>
<div class="glow glow-one"></div>
<div class="glow glow-two"></div>

<nav class="navbar">
    <a href="dashboard.php" class="brand">
        <div class="brand-logo-icon">
            <img src="../logo-badminton.png" alt="Badminton Kampung Panji">
        </div>
        <div class="brand-text">
            <span>BADMINTON</span>
            <small>KAMPUNG PANJI</small>
        </div>
    </a>
    <a href="my_booking.php" class="my-booking-link">
        <i class="fa-solid fa-calendar-check"></i> My Booking
    </a>
</nav>

<div class="page">
<div class="container">

    <div class="steps">
        <div class="step-item active">
            <div class="step-number">1</div>
            Select Method
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <div class="step-number">2</div>
            Scan QR
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <div class="step-number">3</div>
            Upload Receipt
        </div>
    </div>

    <div class="card">

        <div class="secure-badge">
            <i class="fa-solid fa-shield-halved"></i>
            Secure Payment
        </div>

        <div class="title">Choose Payment Method</div>

        <div class="subtitle">
            Select your preferred payment method to continue with your booking.
        </div>

        <div class="info-box">
            <strong><i class="fa-solid fa-circle-info"></i> Next step:</strong>
            Choose your payment method, continue to the QR page, then upload your payment receipt.
        </div>

        <form action="payment_qr.php" method="POST">

            <div class="payment-grid">

                <div class="payment-option selected" onclick="selectPayment(this,'MAE')">
                    <img src="qr/mae.png" alt="MAE" class="payment-logo">
                    <div class="payment-name">MAE</div>
                    <div class="payment-price">RM <?= number_format((float)$booking['price'], 2); ?></div>
                </div>

                <div class="payment-option" onclick="selectPayment(this,'Touch N Go')">
                    <img src="qr/TouchAndgo.png" alt="Touch N Go" class="payment-logo">
                    <div class="payment-name">Touch 'n Go</div>
                    <div class="payment-price">RM <?= number_format((float)$booking['price'], 2); ?></div>
                </div>

                <div class="payment-option" onclick="selectPayment(this,'Bank Islam')">
                    <img src="qr/bankislam.jpg" alt="Bank Islam" class="payment-logo">
                    <div class="payment-name">Bank Islam</div>
                    <div class="payment-price">RM <?= number_format((float)$booking['price'], 2); ?></div>
                </div>

                <div class="payment-option" onclick="selectPayment(this,'Card Payment')">
                    <img src="qr/card.jpg" alt="Card Payment" class="payment-logo">
                    <div class="payment-name">Card Payment</div>
                    <div class="payment-price">RM <?= number_format((float)$booking['price'], 2); ?></div>
                </div>

            </div>

            <input type="hidden" name="booking_id" value="<?= (int)$booking['id']; ?>">
            <input type="hidden" name="amount" value="<?= htmlspecialchars($booking['price']); ?>">
            <input type="hidden" name="payment_method" id="selectedMethod" value="MAE">

            <div class="summary-box">
                <div><span>Court</span><span><?= htmlspecialchars($booking['court_name']); ?></span></div>
                <div><span>Date</span><span><?= htmlspecialchars($booking['booking_date']); ?></span></div>
                <div><span>Time</span><span><?= htmlspecialchars($booking['booking_time']); ?></span></div>
                <div class="total"><span>Total</span><span>RM <?= number_format((float)$booking['price'], 2); ?></span></div>
            </div>

            <button type="submit">
                Continue to QR &nbsp;<i class="fa-solid fa-arrow-right"></i>
            </button>

        </form>

        <div class="security-note">
            <i class="fa-solid fa-lock"></i>
            Your booking and payment information is handled securely
        </div>

    </div>
</div>
</div>

<script>
function selectPayment(element, method){
    document.querySelectorAll('.payment-option').forEach(item=>{
        item.classList.remove('selected');
    });
    element.classList.add('selected');
    document.getElementById('selectedMethod').value = method;
}
</script>

</body>
</html>
