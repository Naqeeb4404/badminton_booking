<?php

session_start();

include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != "user"){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user']['id'];


// =========================================================
// GET BOOKING DATA
// =========================================================

$stmt = $conn->prepare("
SELECT
    bookings.*,
    courts.court_name,
    10.00 AS price,
    payments.status AS payment_status,
    payments.receipt AS payment_receipt
FROM bookings
JOIN courts ON bookings.court_id = courts.id
LEFT JOIN payments ON payments.booking_id = bookings.id
WHERE bookings.user_id = ?
ORDER BY bookings.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$bookingRows = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();


// =========================================================
// LOYALTY REWARD
// =========================================================

$countStmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM bookings
    WHERE user_id = ?
    AND status = 'Approved'
");

$countStmt->bind_param("i", $user_id);
$countStmt->execute();

$count_data = $countStmt->get_result()->fetch_assoc();

$countStmt->close();

$approved_count = (int)($count_data['total'] ?? 0);

$progress_count = $approved_count % 10;

$has_voucher = ($approved_count > 0 && $progress_count == 0);


// =========================================================
// GET USER NOTIFICATIONS
// =========================================================

$notificationStmt = $conn->prepare("
    SELECT 
        id,
        title,
        message,
        status,
        created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 10
");

$notificationStmt->bind_param("i", $user_id);
$notificationStmt->execute();

$notificationResult = $notificationStmt->get_result();

$notifications = $notificationResult->fetch_all(MYSQLI_ASSOC);

$notificationStmt->close();


// =========================================================
// COUNT UNREAD NOTIFICATIONS
// =========================================================

$unreadStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND status = 'Unread'
");

$unreadStmt->bind_param("i", $user_id);
$unreadStmt->execute();

$unreadData = $unreadStmt->get_result()->fetch_assoc();

$unreadCount = (int)($unreadData['total'] ?? 0);

$unreadStmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Booking - Badminton Kampung Panji</title>

    <!-- Bootstrap -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <!-- Google Font -->
    <link 
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" 
        rel="stylesheet"
    >


<style>

/* =========================================================
   ROOT
========================================================= */

:root {
    --bg-app: #f4f4f4;
    --card-dark: #181818;
    --accent-green: #22c55e;
    --accent-orange: #d9622b;
    --text-dark: #111111;
}


/* =========================================================
   BODY
========================================================= */

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: radial-gradient(circle at top right, #382319, #120e0c);
    color: var(--text-dark);
    min-height: 100vh;
    padding: 30px 15px;
}


/* =========================================================
   MAIN CONTAINER
========================================================= */

.app-container {
    max-width: 1100px;
    margin: 0 auto;
    background-color: var(--bg-app);
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    padding: 20px 40px 50px 40px;
}


/* =========================================================
   HEADER NAVIGATION
========================================================= */

.custom-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 0 30px 0;
}

.brand-logo {
    font-size: 1.6rem;
    font-weight: 800;
    color: #000;
    text-decoration: none;
    letter-spacing: -0.5px;
}


/* =========================================================
   HEADER RIGHT SIDE
========================================================= */

.nav-right {
    display: flex;
    align-items: center;
    gap: 12px;
}


/* =========================================================
   NOTIFICATION BUTTON
========================================================= */

.notification-btn {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    color: #111111;

    display: flex;
    align-items: center;
    justify-content: center;

    text-decoration: none;
    font-size: 18px;

    box-shadow: 0 4px 12px rgba(0,0,0,0.08);

    transition: 0.2s ease;
}

.notification-btn:hover {
    transform: translateY(-2px);
    color: #111111;
}


/* =========================================================
   NOTIFICATION RED NUMBER
========================================================= */

.notification-count {
    position: absolute;
    top: -4px;
    right: -4px;

    min-width: 20px;
    height: 20px;

    padding: 0 5px;

    border-radius: 50px;

    background: #dc3545;
    color: #ffffff;

    font-size: 11px;
    font-weight: 800;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 2px solid #ffffff;
}


/* =========================================================
   BLACK BUTTON
========================================================= */

.btn-black {
    background-color: var(--card-dark);
    color: #fff;

    border-radius: 50px;

    padding: 10px 24px;

    font-weight: 700;
    font-size: 0.9rem;

    text-decoration: none;

    transition: all 0.3s ease;
}

.btn-black:hover {
    background-color: #333;
    color: #fff;
    transform: translateY(-2px);
}


/* =========================================================
   REWARD CARD
========================================================= */

.reward-card {
    background: linear-gradient(
        135deg,
        #181818 0%,
        #2a2a2a 100%
    );

    border-radius: 24px;

    padding: 30px;

    color: #ffffff;

    margin-bottom: 35px;

    position: relative;

    overflow: hidden;

    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.reward-card::after {
    content: "🏸";

    position: absolute;

    right: -20px;
    bottom: -30px;

    font-size: 10rem;

    opacity: 0.06;

    pointer-events: none;
}


/* =========================================================
   VOUCHER BADGE
========================================================= */

.voucher-badge {
    background-color: #ccff00;
    color: #000;

    font-weight: 800;

    padding: 6px 16px;

    border-radius: 50px;

    font-size: 0.75rem;

    text-transform: uppercase;

    display: inline-block;

    margin-bottom: 12px;
}


/* =========================================================
   PROGRESS BAR
========================================================= */

.progress-custom {
    height: 10px;

    background-color: rgba(255,255,255,0.15);

    border-radius: 10px;

    overflow: hidden;
}

.progress-bar-custom {
    background: linear-gradient(
        90deg,
        #ccff00,
        #22c55e
    );

    border-radius: 10px;
}


/* =========================================================
   SECTION TITLE
========================================================= */

.section-title {
    font-size: 1.5rem;

    font-weight: 800;

    color: #000;

    margin-bottom: 20px;
}


/* =========================================================
   TABLE
========================================================= */

.table-card {
    background: #ffffff;

    border-radius: 20px;

    padding: 10px;

    box-shadow: 0 10px 30px rgba(0,0,0,0.03);

    border: 1px solid rgba(0, 0, 0, 0.05);
}

.custom-table {
    margin-bottom: 0;

    vertical-align: middle;
}

.custom-table thead th {
    background-color: transparent;

    color: #777;

    font-size: 0.8rem;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    border-bottom: 2px solid #f0f0f0;

    padding: 15px;
}

.custom-table tbody td {
    padding: 18px 15px;

    font-weight: 600;

    font-size: 0.95rem;

    color: #222;

    border-bottom: 1px solid #f6f6f6;
}

.custom-table tbody tr:last-child td {
    border-bottom: none;
}


/* =========================================================
   STATUS BADGES
========================================================= */

.badge-status {
    padding: 8px 16px;

    border-radius: 50px;

    font-size: 0.8rem;

    font-weight: 700;

    display: inline-block;

    margin-bottom: 5px;
}

.badge-approved {
    background-color: rgba(34, 197, 94, 0.15);
    color: #15803d;
}

.badge-rejected {
    background-color: rgba(239, 68, 68, 0.15);
    color: #b91c1c;
}

.badge-pending {
    background-color: rgba(245, 158, 11, 0.15);
    color: #b45309;
}


/* =========================================================
   NOTIFICATION SECTION
========================================================= */

.notification-section {
    margin-bottom: 35px;
}

.notification-card {
    background: #ffffff;

    border-radius: 20px;

    padding: 0;

    border: 1px solid rgba(0,0,0,0.05);

    box-shadow: 0 10px 30px rgba(0,0,0,0.03);

    overflow: hidden;
}

.notification-item {
    display: flex;

    align-items: flex-start;

    gap: 15px;

    padding: 18px 20px;

    border-bottom: 1px solid #eeeeee;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-icon {
    width: 42px;
    height: 42px;

    min-width: 42px;

    border-radius: 50%;

    background: #111111;
    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 16px;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: 0.95rem;

    font-weight: 800;

    color: #111111;

    margin-bottom: 4px;
}

.notification-message {
    font-size: 0.85rem;

    color: #555555;

    margin-bottom: 5px;

    line-height: 1.5;
}

.notification-time {
    font-size: 0.72rem;

    color: #999999;
}

.notification-unread {
    background: #fffdf2;
}

.notification-empty {
    padding: 35px 20px;

    text-align: center;

    color: #888888;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    body {
        padding: 15px 10px;
    }

    .app-container {
        padding: 15px 15px 35px 15px;

        border-radius: 22px;
    }

    .custom-navbar {
        padding-bottom: 20px;
    }

    .brand-logo {
        font-size: 1.2rem;
    }

    .notification-btn {
        width: 40px;
        height: 40px;
    }

    .btn-black {
        padding: 9px 15px;
        font-size: 0.8rem;
    }

    .notification-item {
        padding: 15px;
    }

}

</style>

</head>


<body>

<div class="app-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <nav class="custom-navbar">

        <a href="#" class="brand-logo">
            Badminton Kampung Panji
        </a>


        <div class="nav-right">

            <!-- Notification Bell -->

            <a href="#notifications" class="notification-btn">

                <i class="fa-solid fa-bell"></i>

                <?php if ($unreadCount > 0): ?>

                    <span class="notification-count">
                        <?= $unreadCount ?>
                    </span>

                <?php endif; ?>

            </a>


            <!-- Dashboard -->

            <a href="dashboard.php" class="btn-black">
                <i class="fa-solid fa-house me-1"></i>
                Dashboard
            </a>

        </div>

    </nav>


    <!-- =====================================================
         NOTIFICATIONS
    ====================================================== -->

    <div id="notifications" class="notification-section">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h3 class="section-title mb-0">
                <i class="fa-solid fa-bell me-2"></i>
                Notifications
            </h3>

            <?php if ($unreadCount > 0): ?>

                <span class="badge bg-danger rounded-pill px-3 py-2">
                    <?= $unreadCount ?> Unread
                </span>

            <?php endif; ?>

        </div>


        <div class="notification-card">

            <?php if(count($notifications) > 0): ?>

                <?php foreach($notifications as $notification): ?>

                  <div 
    class="notification-item <?php echo ($notification['status'] === 'Unread') ? 'notification-unread' : ''; ?>"
    data-notification-id="<?php echo (int)$notification['id']; ?>"
    onclick="markNotificationRead(this)"
    style="cursor: pointer;"
>

                        <div class="notification-icon">

                            <?php if($notification['status'] === 'Unread'): ?>

                                <i class="fa-solid fa-bell"></i>

                            <?php else: ?>

                                <i class="fa-solid fa-check"></i>

                            <?php endif; ?>

                        </div>


                        <div class="notification-content">

                            <div class="notification-title">

                                <?php echo htmlspecialchars($notification['title']); ?>

                            </div>


                            <div class="notification-message">

                                <?php echo htmlspecialchars($notification['message']); ?>

                            </div>


                            <div class="notification-time">

                                <i class="fa-regular fa-clock me-1"></i>

                                <?php 
                                    echo htmlspecialchars(
                                        date(
                                            'd M Y, h:i A',
                                            strtotime($notification['created_at'])
                                        )
                                    ); 
                                ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="notification-empty">

                    <i class="fa-regular fa-bell-slash fa-2x mb-3"></i>

                    <div class="fw-bold">
                        No notifications yet
                    </div>

                    <div class="small mt-1">
                        Your booking updates will appear here.
                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         REWARD CARD
    ====================================================== -->

    <div class="reward-card">

        <div class="row align-items-center">

            <div class="col-md-8">

                <span class="voucher-badge">

                    <i class="fa-solid fa-gift me-1"></i>

                    Loyalty Reward Program

                </span>


                <?php if($has_voucher): ?>

                    <h2 class="fw-bold mb-2 text-warning">

                        🎉 Congratulations! You Get 50% Voucher!

                    </h2>


                    <p class="text-white-50 mb-3">

                        You have completed 
                        <strong><?php echo $approved_count; ?> approved bookings</strong>.

                        Use the promo code below for your next booking:

                    </p>


                    <div class="d-inline-block bg-white text-dark px-3 py-2 rounded-3 fw-bold fs-5 border border-warning">

                        PROMO:

                        <span class="text-success">
                            SMASH50
                        </span>

                    </div>


                <?php else: ?>

                    <h3 class="fw-bold mb-1">

                        Play 10 Times, Get 50% Voucher!

                    </h3>


                    <p class="text-white-50 small mb-3">

                        Complete 10 approved bookings to unlock a 50% discount offer.

                    </p>


                    <!-- Progress -->

                    <div class="d-flex justify-content-between align-items-center mb-1 small fw-bold">

                        <span>
                            Booking Progress
                        </span>

                        <span class="text-warning">

                            <?php echo $progress_count; ?> / 10 Bookings

                        </span>

                    </div>


                    <div class="progress progress-custom">

                        <div 
                            class="progress-bar progress-bar-custom"
                            role="progressbar"
                            style="width: <?php echo ($progress_count / 10) * 100; ?>%;"
                        >
                        </div>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MY BOOKINGS
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3 class="section-title mb-0">

            📅 My Bookings

        </h3>


        <span class="badge bg-secondary rounded-pill px-3 py-2">

            Total:
            <?php echo count($bookingRows); ?>

        </span>

    </div>


    <!-- PAYMENT SUCCESS MESSAGE -->

    <?php if(isset($_GET['submitted'])): ?>

        <div class="alert alert-success mb-3">

            <i class="fa-solid fa-circle-check me-1"></i>

            Payment receipt submitted.
            Payment Pending.
            Your Booking is now Booking Pending while the admin reviews it.

        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if(isset($_GET['error'])): ?>

        <div class="alert alert-danger mb-3">

            <?php echo htmlspecialchars($_GET['error']); ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         BOOKING TABLE
    ====================================================== -->

    <div class="table-card">

        <div class="table-responsive">

            <table class="table custom-table text-center align-middle">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th class="text-start">
                            Court Name
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Time
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Payment
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                if(count($bookingRows) > 0){

                    foreach($bookingRows as $row){

                        // =================================================
                        // PAYMENT STATUS
                        // =================================================

                        if($row['payment_status'] === 'Approved'){

                            $paymentBadge = "
                                <span class='badge-status badge-approved'>
                                    <i class='fa-solid fa-check me-1'></i>
                                    Payment Approved
                                </span>
                            ";

                        }elseif($row['payment_status'] === 'Rejected'){

                            $paymentBadge = "
                                <span class='badge-status badge-rejected'>
                                    <i class='fa-solid fa-xmark me-1'></i>
                                    Payment Rejected
                                </span>
                            ";

                        }else{

                            $paymentBadge = "
                                <span class='badge-status badge-pending'>
                                    <i class='fa-solid fa-credit-card me-1'></i>
                                    Payment Pending
                                </span>
                            ";

                        }


                        // =================================================
                        // BOOKING STATUS
                        // =================================================

                        if($row['status'] === 'Approved'){

                            $bookingBadge = "
                                <span class='badge-status badge-approved'>
                                    <i class='fa-solid fa-check me-1'></i>
                                    Booking Approved
                                </span>
                            ";

                        }elseif($row['status'] === 'Rejected'){

                            $bookingBadge = "
                                <span class='badge-status badge-rejected'>
                                    <i class='fa-solid fa-xmark me-1'></i>
                                    Booking Rejected
                                </span>
                            ";

                        }else{

                            $bookingBadge = "
                                <span class='badge-status badge-pending'>
                                    <i class='fa-solid fa-hourglass-half me-1'></i>
                                    Booking Pending
                                </span>
                            ";

                        }

                ?>

                    <tr id="booking-<?php echo (int)$row['id']; ?>">

                        <!-- ID -->

                        <td class="text-muted">

                            #<?php echo (int)$row['id']; ?>

                        </td>


                        <!-- COURT -->

                        <td class="text-start fw-bold">

                            <i class="fa-solid fa-circle-dot text-success me-2 fs-6"></i>

                            <?php echo htmlspecialchars($row['court_name']); ?>

                        </td>


                        <!-- DATE -->

                        <td>

                            <i class="fa-regular fa-calendar me-1 text-muted"></i>

                            <?php echo htmlspecialchars($row['booking_date']); ?>

                        </td>


                        <!-- TIME -->

                        <td>

                            <i class="fa-regular fa-clock me-1 text-muted"></i>

                            <?php echo htmlspecialchars(substr($row['booking_time'], 0, 5)); ?>

                        </td>


                        <!-- PRICE -->

                        <td>

                            RM <?php echo number_format((float)$row['price'], 2); ?>

                        </td>


                        <!-- PAYMENT -->

                        <td>

                            <?php if($row['payment_receipt']): ?>

                                <a 
                                    href="uploads/receipt/<?php echo rawurlencode($row['payment_receipt']); ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-dark"
                                >
                                    <i class="fa-solid fa-receipt me-1"></i>
                                    View Receipt
                                </a>


                            <?php elseif($row['status'] === 'Pending'): ?>

                                <a 
                                    href="payment.php?booking_id=<?php echo (int)$row['id']; ?>"
                                    class="btn btn-sm btn-outline-dark"
                                >
                                    <i class="fa-solid fa-credit-card me-1"></i>
                                    Proceed to Payment
                                </a>


                            <?php elseif($row['status'] === 'Approved'): ?>

                                <a 
                                    href="?booking_id=<?php echo (int)$row['id']; ?>#booking-<?php echo (int)$row['id']; ?>"
                                    class="btn btn-sm btn-outline-dark"
                                >
                                    <i class="fa-solid fa-eye me-1"></i>
                                    View Booking Details
                                </a>


                            <?php else: ?>

                                &mdash;

                            <?php endif; ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php echo $paymentBadge; ?>

                            <br>

                            <?php echo $bookingBadge; ?>


                            <?php if(
                                $row['status'] === 'Rejected' &&
                                !empty($row['rejection_reason'])
                            ): ?>

                                <div class="small text-muted mt-1">

                                    Reason:

                                    <?php echo htmlspecialchars($row['rejection_reason']); ?>

                                </div>

                            <?php endif; ?>

                        </td>

                    </tr>


                <?php

                    }

                }else{

                    echo "

                    <tr>

                        <td 
                            colspan='7'
                            class='py-5 text-muted fw-normal'
                        >

                            <i 
                                class='fa-solid fa-folder-open display-6 d-block mb-2 opacity-50'
                            ></i>

                            No booking records yet.

                        </td>

                    </tr>

                    ";

                }

                ?>

                </tbody>

            </table>

        </div>

    </div>


</div>


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function markNotificationRead(element) {

    const notificationId = element.dataset.notificationId;

    if (!notificationId) {
        return;
    }

    // Already read
    if (!element.classList.contains('notification-unread')) {
        return;
    }

    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'notification_id=' + encodeURIComponent(notificationId)
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            // Remove unread background
            element.classList.remove('notification-unread');

            // Change icon
            const icon = element.querySelector('.notification-icon i');

            if (icon) {
                icon.className = 'fa-solid fa-check';
            }

            // Update notification count
            const countBadge = document.querySelector('.notification-count');

            if (countBadge) {

                let currentCount = parseInt(countBadge.textContent.trim()) || 0;

                currentCount--;

                if (currentCount <= 0) {
                    countBadge.remove();
                } else {
                    countBadge.textContent = currentCount;
                }
            }

            // Update "X Unread" badge
            const unreadBadge = document.querySelector(
                '#notifications .badge.bg-danger'
            );

            if (unreadBadge) {

                let currentUnread = parseInt(
                    unreadBadge.textContent.trim()
                ) || 0;

                currentUnread--;

                if (currentUnread <= 0) {
                    unreadBadge.remove();
                } else {
                    unreadBadge.textContent = currentUnread + ' Unread';
                }
            }
        }

    })
    .catch(error => {
        console.error('Notification error:', error);
    });
}

</script>
</body>

</html>