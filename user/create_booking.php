<?php
session_start();
include __DIR__ . '/../config/db.php';

$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$court = $_POST['court_id'] ?? '';
$duration = isset($_POST['duration']) ? max(1, (int)$_POST['duration']) : 1;

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php?return=booking&date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&court_id=".urlencode($court));
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$court_id = (int)($court ?? 0);

if(!$date || !$time || !$court_id || $date < date('Y-m-d')){
    header("Location: ../booking.php?error=".urlencode('Maklumat booking tidak lengkap.'));
    exit();
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT id, price, status FROM courts WHERE id=? LIMIT 1 FOR UPDATE");
    $stmt->bind_param("i", $court_id);
    $stmt->execute();
    $court_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$court_data || $court_data['status'] !== 'Available'){
        $conn->rollback();
        header("Location: ../booking.php?date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&error=".urlencode('Gelanggang ini tidak tersedia.'));
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM bookings WHERE court_id=? AND booking_date=? AND booking_time=? AND status IN ('Pending','Approved') LIMIT 1 FOR UPDATE");
    $stmt->bind_param("iss", $court_id, $date, $time);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($exists){
        $conn->rollback();
        header("Location: ../booking.php?date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, court_id, booking_date, booking_time, duration, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iissi", $user_id, $court_id, $date, $time, $duration);
    $stmt->execute();
    $booking_id = $conn->insert_id;
    $stmt->close();

    $conn->commit();

    $_SESSION['latest_booking_id'] = $booking_id;
    header("Location: ../payment.php?booking_id=" . $booking_id);
    exit();

}catch(mysqli_sql_exception $e){
    $conn->rollback();
    if($e->getCode() === 1062){
        header("Location: ../booking.php?date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
    }else{
        header("Location: ../booking.php?error=".urlencode('Booking gagal. Sila cuba lagi.'));
    }
    exit();
}
?>