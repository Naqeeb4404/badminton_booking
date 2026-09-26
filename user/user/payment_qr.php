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
if(!in_array($payment_method, $allowedMethods, true)){ $payment_method = 'MAE'; }
$_SESSION['latest_booking_id'] = $booking_id;
$_SESSION['selected_payment_method'] = $payment_method;

// ambil booking terakhir user (must belong to this user)
$stmt = $conn->prepare("
SELECT bookings.*, courts.court_name, courts.price
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
$payStmt = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ? LIMIT 1");
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
<title>Upload Receipt</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:'Inter',sans-serif;
    background:#120c1f;
    color:#fff;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
    position: relative;
    overflow-x: hidden;
}

/* Background Video Styling (Betulkan posisi & saiz) */
.bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    object-fit: cover;
    z-index: -1;
    filter: brightness(0.3); /* Gelapkan sikit video supaya kad nampak terang */
}

.container{
    width:100%;
    max-width:450px;
    z-index: 1;
}

.card{
    background: rgba(30, 20, 53, 0.85); /* Agak telus sikit supaya video nampak samar di belakang */
    backdrop-filter: blur(10px);
    border-radius:16px;
    padding:24px;
    box-shadow:0 8px 24px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
}

h2{
    text-align:center;
    margin-top:0;
    margin-bottom:8px;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
}

.timer{
    text-align:center;
    color:#f43f5e;
    font-weight:600;
    font-size: 14px;
    margin-bottom:20px;
}

.qr-box{
    text-align:center;
    margin:15px 0;
}

.qr-box img{
    width:180px;
    height:180px;
    border:1px dashed rgba(255,255,255,0.2);
    padding:10px;
    border-radius:12px;
    background: #271b45;
}

.details{
    font-size:14px;
    background: #271b45;
    padding: 14px 16px;
    border-radius: 12px;
    margin: 20px 0;
}

.details div{
    display:flex;
    justify-content:space-between;
    margin:6px 0;
    color:#9ca3af;
}

.details div span:last-child {
    color: #fff;
    font-weight: 600;
}

.details .total span:last-child{
    color:#4ade80;
    font-size:16px;
    font-weight:700;
}

input[type="file"]{
    width:100%;
    max-width:100%;
    box-sizing: border-box;
    padding: 12px;
    background: #271b45;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    font-family: inherit;
    margin-bottom: 16px;
    outline: none;
    cursor: pointer;
}

input[type="file"]::file-selector-button {
    background: #7c3aed;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    margin-right: 10px;
    font-weight: 600;
    transition: background 0.2s;
}

input[type="file"]::file-selector-button:hover {
    background: #6d28d9;
}

button{
    width:100%;
    padding:14px;
    background:#7c3aed;
    color:#fff;
    border:none;
    border-radius:12px;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition: background 0.2s;
}

button:hover{
    background:#6d28d9;
}

button:disabled{
    background:#374151;
    color:#9ca3af;
    cursor:not-allowed;
}
</style>
</head>

<body>

<!-- Background Video (Diletakkan di luar kad supaya jadi latar belakang skrin) -->
<video autoplay loop muted playsinline class="bg-video">
    <source src="sports.mp4" type="sports/mp4">
    Browser anda tidak menyokong tag video.
</video>

<div class="container">
    <div class="card">
        <h2>DuitNow QR</h2>

        <div class="timer">
            Scan dalam: <span id="time">03:00</span>
        </div>

        <?php if(isset($_GET['error'])): ?>
        <div style="background:#f43f5e22;border:1px solid #f43f5e55;color:#fecaca;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:14px;text-align:center;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
        <?php endif; ?>

        <div class="qr-box">
            <img src="qr/qr.jpg" alt="QR Code">
        </div>

        <div class="details">
            <div><span>Court</span><span><?= $booking['court_name']; ?></span></div>
            <div><span>Date</span><span><?= $booking['booking_date']; ?></span></div>
            <div><span>Time</span><span><?= $booking['booking_time']; ?></span></div>
            <div class="total"><span>Amount</span><span>RM <?= $booking['price']; ?></span></div>
        </div>

        <div class="details" style="margin-top:12px;">
            <div><span>Payment Method</span><span><?= htmlspecialchars($payment_method) ?></span></div>
        </div>

        <form action="payment_process.php" method="POST" enctype="multipart/form-data" id="form">
            <input type="hidden" name="booking_id" value="<?= $booking['id']; ?>">
            <input type="hidden" name="payment_method" value="<?= htmlspecialchars($payment_method) ?>">
            <input type="hidden" name="amount" value="<?= $booking['price']; ?>">

            <input type="file" name="receipt" id="file" required>

            <button type="submit" id="btn">Upload Receipt</button>
        </form>
    </div>
</div>

<script>
let seconds = 180;
let timer = document.getElementById("time");
let btn = document.getElementById("btn");
let file = document.getElementById("file");

let interval = setInterval(()=>{
    let m = Math.floor(seconds/60);
    let s = seconds%60;

    timer.innerHTML =
        String(m).padStart(2,'0') + ":" +
        String(s).padStart(2,'0');

    if(seconds <= 0){
        clearInterval(interval);
        btn.disabled = true;
        file.disabled = true;
        alert("QR dah expired!");
    }

    seconds--;
},1000);
</script>

</body>
</html>