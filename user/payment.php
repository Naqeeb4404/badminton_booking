<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$booking_id = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? ($_SESSION['latest_booking_id'] ?? 0));
if($booking_id > 0){ $_SESSION['latest_booking_id'] = $booking_id; }

// ambil booking terakhir (must belong to this user)
$stmt = $conn->prepare("
SELECT bookings.*, courts.court_name, 10.00 AS price
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

// If this booking has already been paid, reviewed, or a receipt was already
// uploaded for it, don't let the user pay/upload again for the same booking.
if($booking['status'] !== 'Pending'){
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
<html>
<head>
<title>Proceed to Payment</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body{
    margin:0;
    font-family:'Inter',sans-serif;
    background:#120c1f;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
}

.container{
    width:100%;
    max-width:550px;
}

.card{
    background:#1e1435;
    padding:25px;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.3);
}

.title{
    font-size:20px;
    font-weight:700;
    margin-bottom:15px;
}

.payment-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
}

.payment-option{
    background:#271b45;
    padding:18px 10px;
    border-radius:12px;
    cursor:pointer;
    border:2px solid transparent;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}

.payment-option.selected{
    border-color:#8b5cf6;
    background:#2e1f52;
}

/* Penambahbaikan untuk kekemasan logo */
.payment-logo {
    width: 45px;
    height: 45px;
    object-fit: contain; /* Memastikan gambar tidak pecah atau herot */
    margin-bottom: 10px;
    background: #ffffff;
    padding: 6px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.payment-name{
    font-size:13px;
    font-weight:bold;
    margin-bottom: 6px;
}

.payment-price{
    color:#4ade80;
    font-size: 12px;
    font-weight:bold;
}

.summary-box{
    margin-top:20px;
    background:#271b45;
    padding:15px;
    border-radius:12px;
}

.summary-box div{
    display:flex;
    justify-content:space-between;
    margin:8px 0;
    color:#ddd;
}

.total span:last-child{
    color:#4ade80;
    font-size:18px;
    font-weight:bold;
}

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
    transition: background 0.2s;
}

button:hover{
    background:#6d28d9;
}
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="title">
            Proceed to Payment
        </div>
        <div style="background:#271b45;border-radius:12px;padding:12px 14px;margin-bottom:16px;color:#ddd;font-size:13px;line-height:1.5;">
            <strong style="color:#fff;">Next step:</strong> Choose your payment method, continue to the QR page, then upload your payment receipt.
        </div>

        <form action="payment_qr.php" method="POST">
            <div class="payment-grid">

                <!-- MAE Option -->
                <div class="payment-option selected" onclick="selectPayment(this,'MAE')">
                    <img src="qr/mae.png" alt="MAE" class="payment-logo">
                    <div class="payment-name">MAE</div>
                    <div class="payment-price">RM <?= $booking['price']; ?></div>
                </div>

                <!-- Touch 'n Go Option -->
                <div class="payment-option" onclick="selectPayment(this,'Touch N Go')">
                    <img src="qr/TouchAndgo.png" alt="Touch N Go" class="payment-logo">
                    <div class="payment-name">Touch 'n Go</div>
                    <div class="payment-price">RM <?= $booking['price']; ?></div>
                </div>

                <!-- Bank Islam Option -->
                <div class="payment-option" onclick="selectPayment(this,'Bank Islam')">
                    <img src="qr/bankislam.jpg" alt="Bank Islam" class="payment-logo">
                    <div class="payment-name">Bank Islam</div>
                    <div class="payment-price">RM <?= $booking['price']; ?></div>
                </div>

                <!-- Card Payment Option -->
                <div class="payment-option" onclick="selectPayment(this,'Card Payment')">
                    <img src="qr/card.jpg" alt="Card" class="payment-logo">
                    <div class="payment-name">Card Payment</div>
                    <div class="payment-price">RM <?= $booking['price']; ?></div>
                </div>

            </div>

            <input type="hidden" name="booking_id" value="<?= $booking['id']; ?>">
            <input type="hidden" name="amount" value="<?= $booking['price']; ?>">
            <input type="hidden" name="payment_method" id="selectedMethod" value="MAE">

            <div class="summary-box">
                <div>
                    <span>Court</span>
                    <span><?= $booking['court_name']; ?></span>
                </div>
                <div>
                    <span>Date</span>
                    <span><?= $booking['booking_date']; ?></span>
                </div>
                <div>
                    <span>Time</span>
                    <span><?= htmlspecialchars($booking['booking_time']); ?> (<?= (int)($booking['duration_hours'] ?? 1); ?> hour<?= ((int)($booking['duration_hours'] ?? 1) > 1 ? 's' : ''); ?>)</span>
                </div>
                <div class="total">
                    <span>Total</span>
                    <span>RM <?= $booking['price']; ?></span>
                </div>
            </div>

            <button type="submit">
                Proceed to Payment
            </button>
        </form>
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