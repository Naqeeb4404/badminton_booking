<?php

session_start();

include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$booking_id = (int)($_POST['booking_id'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? ($_SESSION['selected_payment_method'] ?? 'MAE'));
$allowedMethods = ['MAE','Touch N Go','Bank Islam','Card Payment'];
if(!in_array($payment_method, $allowedMethods, true)){ $payment_method = 'MAE'; }

// Re-fetch the booking from the DB instead of trusting the amount the
// browser posted, and make sure it belongs to this user and is still
// awaiting payment.
$stmt = $conn->prepare("
SELECT bookings.id, bookings.status, 10.00 AS price
FROM bookings
JOIN courts ON bookings.court_id = courts.id
WHERE bookings.id = ? AND bookings.user_id = ?
LIMIT 1
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$booking){
    header("Location: my_booking.php?error=" . urlencode('Booking tidak dijumpai.'));
    exit();
}
if($booking['status'] !== 'Pending'){
    header("Location: my_booking.php?error=" . urlencode('Booking ini sudah diproses.'));
    exit();
}

// Prevent submitting a second receipt for a booking that already has one.
$dupStmt = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ? LIMIT 1");
$dupStmt->bind_param("i", $booking_id);
$dupStmt->execute();
$existingPayment = $dupStmt->get_result()->fetch_assoc();
$dupStmt->close();
if($existingPayment){
    header("Location: my_booking.php?error=" . urlencode('Resit untuk tempahan ini sudah dihantar.'));
    exit();
}

$amount = $booking['price'];

// Validate the uploaded receipt.
if(!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK){
    header("Location: payment_qr.php?error=" . urlencode('Sila muat naik resit pembayaran.'));
    exit();
}

$allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
$maxSizeBytes = 5 * 1024 * 1024; // 5MB

$original = $_FILES['receipt']['name'];
$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
$tmp = $_FILES['receipt']['tmp_name'];

if(!in_array($ext, $allowedExt, true)){
    header("Location: payment_qr.php?error=" . urlencode('Format fail tidak disokong. Sila muat naik JPG, PNG atau PDF.'));
    exit();
}
if($_FILES['receipt']['size'] > $maxSizeBytes){
    header("Location: payment_qr.php?error=" . urlencode('Saiz fail terlalu besar (maksimum 5MB).'));
    exit();
}
if(!is_uploaded_file($tmp)){
    header("Location: payment_qr.php?error=" . urlencode('Muat naik gagal. Sila cuba lagi.'));
    exit();
}

$file = 'receipt_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$folder = "uploads/receipt/";

if(!is_dir($folder)){
    mkdir($folder, 0755, true);
}

if(!move_uploaded_file($tmp, $folder . $file)){
    header("Location: payment_qr.php?error=" . urlencode('Muat naik gagal. Sila cuba lagi.'));
    exit();
}

// Payment status "Pending" here represents "Submitted, awaiting admin review".
// The booking itself stays "Pending" until the admin approves/rejects it
// (see admin/manage_booking.php), at which point this row is updated too.
$stmt = $conn->prepare("INSERT INTO payments (booking_id, user_id, amount, receipt, payment_method, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("iidss", $booking_id, $user_id, $amount, $file, $payment_method);

if($stmt->execute()){
    $stmt->close();
    unset($_SESSION['selected_payment_method']);
    $_SESSION['latest_booking_id'] = $booking_id;
    header("Location: my_booking.php?submitted=1&booking_id=" . $booking_id);
    exit();
}else{
    $stmt->close();
    header("Location: payment_qr.php?error=" . urlencode('Penghantaran gagal. Sila cuba lagi.'));
    exit();
}
