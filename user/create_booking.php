<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $court = $_POST['court_id'] ?? '';
    header("Location: ../auth/login.php?return=booking&date=".urlencode($date)."&time=".urlencode($time)."&court_id=".urlencode($court));
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$court_id = (int)($_POST['court_id'] ?? 0);

if(!$date || !$time || !$court_id || $date < date('Y-m-d')){
    header("Location: dashboard.php?error=".urlencode('Maklumat booking tidak lengkap.'));
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
        header("Location: dashboard.php?error=".urlencode('Gelanggang ini tidak tersedia.'));
        exit();
    }

    // Lock any existing conflicting rows for this exact court/date/time so a
    // concurrent request has to wait for this transaction to finish first.
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE court_id=? AND booking_date=? AND booking_time=? AND status IN ('Pending','Approved') LIMIT 1 FOR UPDATE");
    $stmt->bind_param("iss", $court_id, $date, $time);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($exists){
        $conn->rollback();
        header("Location: dashboard.php?error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id,court_id,booking_date,booking_time,status) VALUES (?,?,?,?, 'Pending')");
    $stmt->bind_param("iiss", $user_id, $court_id, $date, $time);
    $stmt->execute();
    $booking_id = $conn->insert_id;
    $stmt->close();

    $conn->commit();

    $_SESSION['latest_booking_id'] = $booking_id;
    header("Location: payment.php");
    exit();

}catch(mysqli_sql_exception $e){
    $conn->rollback();
    // 1062 = duplicate key on the active_slot_key unique index (see schema_updates.sql):
    // a second request for the exact same court/date/time slipped past the row
    // lock above and hit the database's own uniqueness guarantee instead.
    if($e->getCode() === 1062){
        header("Location: dashboard.php?error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
    }else{
        header("Location: dashboard.php?error=".urlencode('Booking gagal. Sila cuba lagi.'));
    }
    exit();
}
?>
