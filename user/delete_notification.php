<?php
session_start();

include __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) ||
    $_SESSION['user']['role'] != 'user'
) {

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);

    exit();
}

$user_id = (int)$_SESSION['user']['id'];

$notification_id =
    isset($_POST['notification_id'])
    ? (int)$_POST['notification_id']
    : 0;


if ($notification_id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid notification.'
    ]);

    exit();
}


$stmt = $conn->prepare("
    DELETE FROM notifications
    WHERE id = ?
    AND user_id = ?
");

$stmt->bind_param(
    "ii",
    $notification_id,
    $user_id
);

$stmt->execute();


if ($stmt->affected_rows > 0) {

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Notification not found.'
    ]);
}


$stmt->close();
?>