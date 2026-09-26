<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    $date = $_POST['date'] ?? $_GET['date'] ?? '';
    $time = $_POST['time'] ?? $_GET['time'] ?? '';
    $court = $_POST['court_id'] ?? $_GET['court_id'] ?? '';
    header("Location: ../auth/login.php?return=booking&date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&court_id=".urlencode($court));
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$date = $_POST['date'] ?? $_GET['date'] ?? '';
$time = $_POST['time'] ?? $_GET['time'] ?? '';
$duration = max(1, min(3, (int)($_POST['duration'] ?? $_GET['duration'] ?? $_SESSION['selected_duration'] ?? 1)));
$court_id = (int)($_POST['court_id'] ?? $_GET['court_id'] ?? 0);

if(!$date || !$time || !$court_id || $date < date('Y-m-d')){
    header("Location: dashboard.php?court_id=".urlencode($court_id)."&error=".urlencode('Maklumat booking tidak lengkap.'));
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
        header("Location: dashboard.php?date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&court_id=".urlencode($court_id)."&error=".urlencode('Gelanggang ini tidak tersedia.'));
        exit();
    }

    // Check every hourly segment covered by this booking.
    $stmt = $conn->prepare("SELECT id, booking_time, COALESCE(duration_hours,1) AS duration_hours FROM bookings WHERE court_id=? AND booking_date=? AND status IN ('Pending','Approved') FOR UPDATE");
    $stmt->bind_param("is", $court_id, $date);
    $stmt->execute();
    $rows = $stmt->get_result();
    $requestedStart = strtotime($date . ' ' . $time);
    $requestedEnd = $requestedStart + ($duration * 3600);
    $conflict = false;
    while($existing = $rows->fetch_assoc()){
        $existingStart = strtotime($date . ' ' . $existing['booking_time']);
        $existingEnd = $existingStart + ((int)$existing['duration_hours'] * 3600);
        if($requestedStart < $existingEnd && $requestedEnd > $existingStart){
            $conflict = true;
            break;
        }
    }
    $stmt->close();

    if($conflict){
        $conn->rollback();
        header("Location: dashboard.php?date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&court_id=".urlencode($court_id)."&error=".urlencode('Slot dalam tempoh pilihan anda sudah ditempah. Sila pilih masa atau tempoh lain.'));
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id,court_id,booking_date,booking_time,duration_hours,status) VALUES (?,?,?,?,?,'Pending')");
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
        header("Location: dashboard.php?date=".urlencode($date)."&time=".urlencode($time)."&duration=".urlencode($duration)."&court_id=".urlencode($court_id)."&error=".urlencode('Slot baru sahaja ditempah oleh pengguna lain.'));
    }else{
        header("Location: dashboard.php?court_id=".urlencode($court_id)."&error=".urlencode('Booking gagal. Sila cuba lagi.'));
    }
    exit();
}
?>
