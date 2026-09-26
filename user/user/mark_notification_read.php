<?php

session_start();

header('Content-Type: application/json');

include __DIR__ . '/../config/db.php';


// =========================================================
// CHECK LOGIN
// =========================================================

if (
    !isset($_SESSION['user']) ||
    $_SESSION['user']['role'] !== 'user'
) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);

    exit();
}


// =========================================================
// GET USER ID
// =========================================================

$user_id = (int)$_SESSION['user']['id'];


// =========================================================
// GET NOTIFICATION ID
// =========================================================

$notification_id = (int)($_POST['notification_id'] ?? 0);


if ($notification_id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid notification'
    ]);

    exit();
}


// =========================================================
// MARK AS READ
// =========================================================

$stmt = $conn->prepare("
    UPDATE notifications
    SET status = 'Read'
    WHERE id = ?
    AND user_id = ?
    AND status = 'Unread'
");

$stmt->bind_param(
    "ii",
    $notification_id,
    $user_id
);

$stmt->execute();


// =========================================================
// RESPONSE
// =========================================================

if ($stmt->affected_rows > 0) {

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Notification already read or not found'
    ]);
}


$stmt->close();

?>