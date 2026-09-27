<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    $date = $_POST['date'] ?? $_GET['date'] ?? '';
    $time = $_POST['time'] ?? $_GET['time'] ?? '';
    $court = $_POST['court_id'] ?? $_GET['court_id'] ?? '';
    $durationLogin = (int)($_POST['duration'] ?? $_GET['duration'] ?? 1);
    header("Location: ../auth/login.php?return=booking&date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($durationLogin)."&court_id=".urlencode($court));
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$date = $_POST['date'] ?? $_GET['date'] ?? '';
$time = $_POST['time'] ?? $_GET['time'] ?? '';
$court_id = (int)($_POST['court_id'] ?? $_GET['court_id'] ?? 0);
$duration = (int)($_POST['duration'] ?? $_GET['duration'] ?? 1);
if ($duration < 1) $duration = 1;
if ($duration > 4) $duration = 4;

if(!$date || !$time || !$court_id || $date < date('Y-m-d')){
    header("Location: ../booking.php?court_id=".urlencode($court_id)."&error=".urlencode('Maklumat booking tidak lengkap.'));
    exit();
}

$startTimestamp = strtotime($date . ' ' . substr($time, 0, 5));
$endTimestamp = $startTimestamp + ($duration * 3600);
$openTimestamp = strtotime($date . ' 08:00');
$closeTimestamp = strtotime($date . ' 23:00');

if(!$startTimestamp || $startTimestamp < $openTimestamp || $endTimestamp > $closeTimestamp){
    header("Location: ../booking.php?date=".urlencode($date)."&duration=".urlencode($duration)."&error=".urlencode('Masa atau tempoh booking tidak sah.'));
    exit();
}

if($date === date('Y-m-d') && $startTimestamp <= time()){
    header("Location: ../booking.php?date=".urlencode($date)."&duration=".urlencode($duration)."&error=".urlencode('Slot masa ini sudah lepas.'));
    exit();
}

// Re-check court status and booking collision immediately before inserting.
// Everything below runs inside one transaction with row-level locks so that
// two users clicking "Confirm" for the same court/date/time at the same
// instant cannot both succeed (closes the check-then-insert race condition).
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT id, price, status FROM courts WHERE id=? LIMIT 1 FOR UPDATE");
    $stmt->bind_param("i", $court_id);
    $stmt->execute();
    $court = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$court || $court['status'] !== 'Available'){
        $conn->rollback();
        header("Location: ../booking.php?date=".urlencode($date)."&time=".urlencode($time)."&court_id=".urlencode($court_id)."&error=".urlencode('Gelanggang ini tidak tersedia.'));
        exit();
    }

    // Check overlapping active bookings for the whole selected duration.
    $selected_start = $date . ' ' . substr($time, 0, 5) . ':00';
    $selected_end = date('Y-m-d H:i:s', strtotime($selected_start . " +{$duration} hours"));
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE court_id=? AND booking_date=? AND status IN ('Pending','Approved') AND TIMESTAMP(booking_date, booking_time) < ? AND DATE_ADD(TIMESTAMP(booking_date, booking_time), INTERVAL COALESCE(duration,1) HOUR) > ? LIMIT 1 FOR UPDATE");
    $stmt->bind_param("isss", $court_id, $date, $selected_end, $selected_start);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($exists){
        $conn->rollback();
        header("Location: ../booking.php?date=".urlencode($date)."&time=".urlencode($time)."&court_id=".urlencode($court_id)."&error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id,court_id,booking_date,booking_time,duration,status) VALUES (?,?,?,?,?, 'Pending')");
    $stmt->bind_param("iissi", $user_id, $court_id, $date, $time, $duration);
    $stmt->execute();
    $booking_id = $conn->insert_id;
    $stmt->close();

    $conn->commit();

    $_SESSION['latest_booking_id'] = $booking_id;
    $_SESSION['selected_court_id'] = $court_id;
    header("Location: payment.php");
    exit();

}catch(mysqli_sql_exception $e){
    $conn->rollback();
    // 1062 = duplicate key on the active_slot_key unique index (see schema_updates.sql):
    // a second request for the exact same court/date/time slipped past the row
    // lock above and hit the database's own uniqueness guarantee instead.
    if($e->getCode() === 1062){
        header("Location: ../booking.php?date=".urlencode($date)."&time=".urlencode($time)."&court_id=".urlencode($court_id)."&error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
    }else{
        header("Location: ../booking.php?court_id=".urlencode($court_id)."&error=".urlencode('Booking gagal. Sila cuba lagi.'));
    }
    exit();
}
?>
