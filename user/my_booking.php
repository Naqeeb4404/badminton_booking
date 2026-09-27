<?php

session_start();



include __DIR__ . '/../config/db.php';



if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != "user"){

    header("Location: ../auth/login.php");

    exit();

}



$user_id = (int)$_SESSION['user']['id'];





/* =========================================================

   GET BOOKING DATA

\========================================================= */



$stmt = $conn->prepare("

    SELECT

        bookings.*,

        courts.court_name,

        courts.price AS price_per_hour,

        (courts.price * COALESCE(bookings.duration, 1)) AS price,

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





/* =========================================================

   LOYALTY REWARD

\========================================================= */



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





/* =========================================================

   GET USER NOTIFICATIONS

\========================================================= */



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





/* =========================================================

   COUNT UNREAD NOTIFICATIONS

\========================================================= */



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
<title>My Bookings - Badminton Kampung Panji</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#090a0f;--panel:#12141e;--panel2:#181a27;--border:rgba(255,255,255,.09);--text:#f8fafc;--muted:#94a3b8;--purple:#a855f7;--indigo:#6366f1;--green:#4ade80;--red:#fb7185;--amber:#fbbf24}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;color:var(--text);background:#090a0f;overflow-x:hidden}
body:before{content:"";position:fixed;inset:0;z-index:-3;background:radial-gradient(circle at 10% 5%,rgba(99,102,241,.22),transparent 30%),radial-gradient(circle at 90% 45%,rgba(168,85,247,.16),transparent 32%),linear-gradient(180deg,#090a0f,#0d0e15 55%,#090a0f)}
.orb{position:fixed;border-radius:50%;filter:blur(120px);opacity:.13;pointer-events:none;z-index:-2}.orb.one{width:320px;height:320px;background:#6366f1;left:-130px;top:170px}.orb.two{width:360px;height:360px;background:#a855f7;right:-160px;bottom:40px}
.nav{height:78px;padding:10px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:rgba(9,10,15,.82);backdrop-filter:blur(18px);position:sticky;top:0;z-index:20}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}.brand-logo{width:53px;height:53px;border-radius:10px;overflow:hidden;display:flex;align-items:center;justify-content:center}.brand-logo img{width:51px;height:51px;object-fit:contain;border-radius:9px}.brand-text{display:flex;flex-direction:column}.brand-text strong{font-size:.95rem;color:#fff;line-height:1.05}.brand-text span{font-size:.62rem;color:#a855f7;font-weight:800;letter-spacing:1.5px;margin-top:5px}
.nav-actions{display:flex;align-items:center;gap:9px}.nav-btn{height:40px;padding:0 15px;border-radius:50px;border:1px solid var(--border);background:rgba(255,255,255,.035);color:#dbe3ef;text-decoration:none;display:flex;align-items:center;gap:7px;font-size:.76rem;font-weight:700;transition:.2s}.nav-btn:hover{background:rgba(255,255,255,.08);color:#fff;transform:translateY(-1px)}.bell{position:relative;width:40px;padding:0;justify-content:center}.notification-count{position:absolute;right:-4px;top:-5px;min-width:19px;height:19px;padding:0 5px;border-radius:30px;background:#ef4444;color:#fff;border:2px solid #090a0f;display:flex;align-items:center;justify-content:center;font-size:.58rem;font-weight:800}
.wrapper{width:min(1180px,calc(100% - 30px));margin:0 auto;padding:38px 0 70px}
.hero{position:relative;padding:29px 30px;margin-bottom:22px;border:1px solid var(--border);border-radius:25px;overflow:hidden;background:linear-gradient(135deg,rgba(25,27,41,.96),rgba(14,15,23,.94));box-shadow:0 24px 65px rgba(0,0,0,.3)}.hero:after{content:"🏸";position:absolute;right:25px;bottom:-38px;font-size:9rem;opacity:.055;transform:rotate(-18deg)}.eyebrow{display:inline-flex;align-items:center;gap:7px;padding:6px 11px;border-radius:50px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.23);color:#a5b4fc;font-size:.61rem;font-weight:800;letter-spacing:1px;text-transform:uppercase}.hero h1{font-size:2rem;letter-spacing:-1px;margin:12px 0 7px}.hero p{color:var(--muted);font-size:.8rem;margin:0;max-width:580px;line-height:1.6}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:11px;margin-top:20px;max-width:570px}.stat{padding:12px 14px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.025)}.stat small{display:block;color:#64748b;font-size:.57rem;text-transform:uppercase;font-weight:800;letter-spacing:.7px;margin-bottom:4px}.stat strong{font-size:.9rem}
.section{margin-top:24px}.section-head{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:13px}.section-title{display:flex;align-items:center;gap:10px;margin:0;font-size:1.05rem;font-weight:800}.section-title .icon{width:35px;height:35px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(168,85,247,.14));border:1px solid rgba(99,102,241,.18);color:#a5b4fc}.count-pill{padding:6px 10px;border-radius:50px;background:rgba(255,255,255,.05);border:1px solid var(--border);font-size:.62rem;font-weight:800;color:#94a3b8}
.notice-actions{display:flex;align-items:center;gap:8px}.delete-all{border:1px solid rgba(251,113,133,.22);background:rgba(251,113,133,.08);color:#fda4af;padding:8px 12px;border-radius:50px;font-family:inherit;font-size:.65rem;font-weight:800;cursor:pointer;transition:.2s}.delete-all:hover{background:#e11d48;color:#fff}.delete-all:disabled{opacity:.45;cursor:not-allowed;background:rgba(251,113,133,.05);color:#fda4af}
.notifications{border:1px solid var(--border);border-radius:20px;overflow:hidden;background:rgba(18,20,30,.86);box-shadow:0 18px 45px rgba(0,0,0,.22)}.notification-item{display:flex;align-items:flex-start;gap:13px;padding:15px 17px;border-bottom:1px solid var(--border);cursor:pointer;transition:.2s}.notification-item:last-child{border-bottom:0}.notification-item:hover{background:rgba(255,255,255,.025)}.notification-unread{background:rgba(99,102,241,.055)}.notification-icon{width:39px;height:39px;min-width:39px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.12);color:#a5b4fc}.notification-content{flex:1;min-width:0}.notification-title{font-size:.76rem;font-weight:800;color:#fff;margin-bottom:3px}.notification-message{font-size:.69rem;color:#94a3b8;line-height:1.5}.notification-time{font-size:.58rem;color:#64748b;margin-top:5px}.notification-delete{width:34px;height:34px;min-width:34px;border:0;border-radius:10px;background:rgba(251,113,133,.08);color:#fb7185;cursor:pointer;transition:.2s}.notification-delete:hover{background:#e11d48;color:#fff}.notification-empty,.empty-booking{padding:34px 20px;text-align:center;color:#64748b}
.reward{position:relative;overflow:hidden;padding:23px 25px;border-radius:21px;border:1px solid rgba(168,85,247,.17);background:linear-gradient(135deg,rgba(99,102,241,.12),rgba(168,85,247,.08),rgba(18,20,30,.92));margin-top:22px}.reward:after{content:"🎁";position:absolute;right:20px;bottom:-18px;font-size:6rem;opacity:.07}.reward-badge{display:inline-flex;align-items:center;gap:6px;color:#c4b5fd;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px}.reward h3{font-size:1.02rem;margin:9px 0 5px}.reward p{font-size:.68rem;color:#94a3b8;margin:0 0 13px}.progress-line{height:7px;border-radius:20px;background:rgba(255,255,255,.08);overflow:hidden;max-width:550px}.progress-fill{height:100%;background:linear-gradient(90deg,#6366f1,#a855f7);border-radius:20px}
.booking-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.booking-card{position:relative;overflow:hidden;padding:18px;border-radius:20px;border:1px solid var(--border);background:linear-gradient(145deg,rgba(24,26,39,.95),rgba(15,16,24,.94));box-shadow:0 16px 40px rgba(0,0,0,.18);transition:.22s}.booking-card:hover{transform:translateY(-3px);border-color:rgba(99,102,241,.25)}.booking-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:15px}.court-wrap{display:flex;align-items:center;gap:11px}.court-icon{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(168,85,247,.13));color:#a5b4fc;border:1px solid rgba(99,102,241,.15)}.court-name small{display:block;color:#64748b;font-size:.56rem;text-transform:uppercase;font-weight:800;letter-spacing:.8px;margin-bottom:2px}.court-name strong{font-size:.88rem}.booking-id{font-size:.6rem;color:#64748b;font-weight:800;padding:5px 8px;border:1px solid var(--border);border-radius:50px}
.booking-info{display:grid;grid-template-columns:1fr 1fr 1fr;gap:7px;margin-bottom:13px}.info-box{padding:9px;border-radius:11px;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.055)}.info-box small{display:block;color:#64748b;font-size:.52rem;text-transform:uppercase;font-weight:800;margin-bottom:3px}.info-box strong{font-size:.66rem}.booking-price{color:#4ade80!important}
.status-row{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}.status{display:inline-flex;align-items:center;gap:5px;padding:6px 9px;border-radius:50px;font-size:.56rem;font-weight:800}.approved{background:rgba(74,222,128,.1);color:#86efac;border:1px solid rgba(74,222,128,.17)}.rejected{background:rgba(251,113,133,.1);color:#fda4af;border:1px solid rgba(251,113,133,.17)}.pending{background:rgba(251,191,36,.09);color:#fcd34d;border:1px solid rgba(251,191,36,.16)}
.reason{font-size:.62rem;color:#fda4af;background:rgba(251,113,133,.06);padding:8px 9px;border-radius:9px;margin-bottom:10px}.card-action{display:flex}.action-btn{width:100%;min-height:38px;border-radius:11px;border:1px solid var(--border);background:rgba(255,255,255,.035);color:#e2e8f0;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;font-size:.62rem;font-weight:800;transition:.2s}.action-btn:hover{background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;border-color:transparent}
.flash{margin:0 0 14px;padding:12px 14px;border-radius:13px;font-size:.7rem;font-weight:700}.success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.17);color:#86efac}.error{background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.17);color:#fda4af}
.pagination-wrap{display:flex;align-items:center;justify-content:flex-end;gap:9px;margin-top:15px}.page-btn{min-width:94px;height:40px;padding:0 15px;border-radius:50px;border:1px solid var(--border);background:rgba(255,255,255,.035);color:#e2e8f0;font-family:inherit;font-size:.67rem;font-weight:800;display:flex;align-items:center;justify-content:center;gap:7px;cursor:pointer;transition:.2s}.page-btn:hover:not(:disabled){background:linear-gradient(135deg,#6366f1,#a855f7);border-color:transparent;color:#fff;transform:translateY(-1px)}.page-btn:disabled{opacity:.35;cursor:not-allowed}.page-indicator{min-width:82px;text-align:center;color:#94a3b8;font-size:.65rem;font-weight:800}.booking-card.page-hidden{display:none}
@media(max-width:800px){.nav{height:70px;padding:8px 15px}.brand-logo{width:47px;height:47px}.brand-logo img{width:45px;height:45px}.brand-text{display:none}.nav-btn span{display:none}.nav-btn{width:40px;padding:0;justify-content:center}.wrapper{width:min(100% - 20px,1180px);padding-top:24px}.hero{padding:23px 19px}.hero h1{font-size:1.55rem}.stats{grid-template-columns:1fr}.booking-grid{grid-template-columns:1fr}.booking-info{grid-template-columns:1fr 1fr 1fr}}
@media(max-width:480px){.section-head{align-items:flex-start}.notice-actions{flex-direction:column;align-items:flex-end}.booking-info{grid-template-columns:1fr}.hero:after{display:none}}
</style>
</head>
<body>
<div class="orb one"></div><div class="orb two"></div>

<nav class="nav">
    <a href="dashboard.php" class="brand">
        <div class="brand-logo"><img src="../logo-badminton.png" alt="Badminton Kampung Panji"></div>
        <div class="brand-text"><strong>BADMINTON</strong><span>KAMPUNG PANJI</span></div>
    </a>
    <div class="nav-actions">
        <a href="#notifications" class="nav-btn bell" title="Notifications">
            <i class="fa-solid fa-bell"></i>
            <?php if($unreadCount > 0): ?><span class="notification-count"><?= $unreadCount ?></span><?php endif; ?>
        </a>
        <a href="dashboard.php" class="nav-btn"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
    </div>
</nav>

<main class="wrapper">
    <section class="hero">
        <span class="eyebrow"><i class="fa-solid fa-ticket"></i> Player Hub</span>
        <h1>My Bookings</h1>
        <p>Semua tempahan, pembayaran dan status court anda dalam satu tempat.</p>
        <div class="stats">
            <div class="stat"><small>Total Bookings</small><strong><?= count($bookingRows) ?></strong></div>
            <div class="stat"><small>Approved</small><strong><?= $approved_count ?></strong></div>
            <div class="stat"><small>Loyalty Progress</small><strong><?= $progress_count ?>/10</strong></div>
        </div>
    </section>

    <section id="notifications" class="section">
        <div class="section-head">
            <h2 class="section-title"><span class="icon"><i class="fa-solid fa-bell"></i></span>Notifications</h2>
            <div class="notice-actions">
                <?php if($unreadCount > 0): ?><span id="unreadTextBadge" class="count-pill"><?= $unreadCount ?> Unread</span><?php endif; ?>
                <button type="button" class="delete-all" onclick="deleteAllNotifications()" <?= count($notifications) === 0 ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-trash-can"></i> Delete Notifications
                </button>
            </div>
        </div>

        <div class="notifications notification-card">
            <?php if(count($notifications) > 0): ?>
                <?php foreach($notifications as $notification): ?>
                    <div class="notification-item <?= $notification['status']==='Unread'?'notification-unread':'' ?>" data-notification-id="<?= (int)$notification['id'] ?>" onclick="markNotificationRead(this)">
                        <div class="notification-icon"><i class="fa-solid <?= $notification['status']==='Unread'?'fa-bell':'fa-check' ?>"></i></div>
                        <div class="notification-content">
                            <div class="notification-title"><?= htmlspecialchars($notification['title']) ?></div>
                            <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                            <div class="notification-time"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars(date('d M Y, h:i A',strtotime($notification['created_at']))) ?></div>
                        </div>
                        <button type="button" class="notification-delete" onclick="deleteNotification(event,this)" title="Delete Notification"><i class="fa-solid fa-trash"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notification-empty"><i class="fa-regular fa-bell-slash fa-2x"></i><div style="font-weight:800;margin-top:10px">No notifications yet</div><div style="font-size:.68rem;margin-top:4px">Your booking updates will appear here.</div></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="reward">
        <span class="reward-badge"><i class="fa-solid fa-gift"></i> Loyalty Reward</span>
        <?php if($has_voucher): ?>
            <h3>🎉 Congratulations! You Get 50% Voucher!</h3>
            <p>You have completed <?= $approved_count ?> approved bookings. Promo code: <strong style="color:#86efac">SMASH50</strong></p>
            <div class="progress-line"><div class="progress-fill" style="width:100%"></div></div>
        <?php else: ?>
            <h3>Play 10 Times, Get 50% Voucher!</h3>
            <p>Complete 10 approved bookings to unlock your reward. <?= $progress_count ?>/10 bookings completed.</p>
            <div class="progress-line"><div class="progress-fill" style="width:<?= ($progress_count/10)*100 ?>%"></div></div>
        <?php endif; ?>
    </section>

    <section class="section">
        <div class="section-head">
            <h2 class="section-title"><span class="icon"><i class="fa-solid fa-calendar-check"></i></span>My Bookings</h2>
            <span class="count-pill"><?= count($bookingRows) ?> Bookings</span>
        </div>

        <?php if(isset($_GET['submitted'])): ?>
            <div class="flash success"><i class="fa-solid fa-circle-check"></i> Payment receipt submitted. Your booking is pending while the admin reviews it.</div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="flash error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="booking-grid">
        <?php if(count($bookingRows) > 0): ?>
            <?php foreach($bookingRows as $row): ?>
                <?php
                if($row['payment_status']==='Approved'){
                    $paymentClass='approved'; $paymentText='Payment Approved'; $paymentIcon='fa-check';
                }elseif($row['payment_status']==='Rejected'){
                    $paymentClass='rejected'; $paymentText='Payment Rejected'; $paymentIcon='fa-xmark';
                }else{
                    $paymentClass='pending'; $paymentText='Payment Pending'; $paymentIcon='fa-credit-card';
                }
                if($row['status']==='Approved'){
                    $bookingClass='approved'; $bookingText='Booking Approved'; $bookingIcon='fa-check';
                }elseif($row['status']==='Rejected'){
                    $bookingClass='rejected'; $bookingText='Booking Rejected'; $bookingIcon='fa-xmark';
                }else{
                    $bookingClass='pending'; $bookingText='Booking Pending'; $bookingIcon='fa-hourglass-half';
                }
                ?>
                <article class="booking-card" id="booking-<?= (int)$row['id'] ?>">
                    <div class="booking-top">
                        <div class="court-wrap">
                            <div class="court-icon"><i class="fa-solid fa-table-tennis-paddle-ball"></i></div>
                            <div class="court-name"><small>Selected Court</small><strong><?= htmlspecialchars($row['court_name']) ?></strong></div>
                        </div>
                        <span class="booking-id">#<?= (int)$row['id'] ?></span>
                    </div>

                    <div class="booking-info">
                        <div class="info-box"><small>Date</small><strong><?= htmlspecialchars(date('d M Y',strtotime($row['booking_date']))) ?></strong></div>
                        <div class="info-box"><small>Time</small><strong><?= htmlspecialchars(substr($row['booking_time'],0,5)) ?></strong></div>
                        <div class="info-box"><small>Total</small><strong class="booking-price">RM <?= number_format((float)$row['price'],2) ?></strong></div>
                    </div>

                    <div class="status-row">
                        <span class="status <?= $paymentClass ?>"><i class="fa-solid <?= $paymentIcon ?>"></i><?= $paymentText ?></span>
                        <span class="status <?= $bookingClass ?>"><i class="fa-solid <?= $bookingIcon ?>"></i><?= $bookingText ?></span>
                    </div>

                    <?php if($row['status']==='Rejected' && !empty($row['rejection_reason'])): ?>
                        <div class="reason"><strong>Reason:</strong> <?= htmlspecialchars($row['rejection_reason']) ?></div>
                    <?php endif; ?>

                    <div class="card-action">
                        <?php if($row['payment_receipt']): ?>
                            <a href="uploads/receipt/<?= rawurlencode($row['payment_receipt']) ?>" target="_blank" class="action-btn"><i class="fa-solid fa-receipt"></i> View Receipt</a>
                        <?php elseif($row['status']==='Pending'): ?>
                            <a href="payment.php?booking_id=<?= (int)$row['id'] ?>" class="action-btn"><i class="fa-solid fa-credit-card"></i> Proceed to Payment</a>
                        <?php elseif($row['status']==='Approved'): ?>
                            <a href="?booking_id=<?= (int)$row['id'] ?>#booking-<?= (int)$row['id'] ?>" class="action-btn"><i class="fa-solid fa-eye"></i> View Booking Details</a>
                        <?php else: ?>
                            <span class="action-btn" style="opacity:.45">No Action Required</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notifications" style="grid-column:1/-1"><div class="empty-booking"><i class="fa-solid fa-calendar-xmark fa-2x"></i><div style="font-weight:800;margin-top:10px">No booking records yet</div><div style="font-size:.68rem;margin-top:4px">Your future bookings will appear here.</div></div></div>
        <?php endif; ?>
        </div>

        <?php if(count($bookingRows) > 0): ?>
        <div class="pagination-wrap" id="bookingPagination">
            <button type="button" class="page-btn" id="prevBookingBtn" onclick="changeBookingPage(-1)">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <div class="page-indicator" id="bookingPageInfo">Page 1 / 1</div>
            <button type="button" class="page-btn" id="nextBookingBtn" onclick="changeBookingPage(1)">
                Next <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </section>
</main>

<script>
function updateUnreadCount(){
    const unreadItems=document.querySelectorAll('.notification-item.notification-unread').length;
    const topBadge=document.querySelector('.notification-count');
    const textBadge=document.getElementById('unreadTextBadge');
    if(topBadge){
        if(unreadItems<=0) topBadge.remove();
        else topBadge.textContent=unreadItems;
    }
    if(textBadge){
        if(unreadItems<=0) textBadge.remove();
        else textBadge.textContent=unreadItems+' Unread';
    }
}

function markNotificationRead(element){
    const notificationId=element.dataset.notificationId;
    if(!notificationId || !element.classList.contains('notification-unread')) return;
    fetch('mark_notification_read.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'notification_id='+encodeURIComponent(notificationId)
    })
    .then(r=>r.json())
    .then(data=>{
        if(data.success){
            element.classList.remove('notification-unread');
            const icon=element.querySelector('.notification-icon i');
            if(icon) icon.className='fa-solid fa-check';
            updateUnreadCount();
        }
    })
    .catch(err=>console.error('Notification error:',err));
}

function deleteNotification(event,button){
    event.stopPropagation();
    const item=button.closest('.notification-item');
    if(!item) return;
    const id=item.dataset.notificationId;
    if(!id || !confirm('Delete this notification?')) return;
    button.disabled=true;
    fetch('delete_notification.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'notification_id='+encodeURIComponent(id)
    })
    .then(r=>r.json())
    .then(data=>{
        if(!data.success){button.disabled=false;alert(data.message||'Failed to delete notification.');return;}
        item.remove();
        updateUnreadCount();
        showEmptyNotificationsIfNeeded();
    })
    .catch(()=>{button.disabled=false;alert('Failed to delete notification.');});
}

function showEmptyNotificationsIfNeeded(){
    const card=document.querySelector('.notification-card');
    if(!card || card.querySelectorAll('.notification-item').length>0) return;
    card.innerHTML='<div class="notification-empty"><i class="fa-regular fa-bell-slash fa-2x"></i><div style="font-weight:800;margin-top:10px">No notifications yet</div><div style="font-size:.68rem;margin-top:4px">Your booking updates will appear here.</div></div>';
    const deleteAll=document.querySelector('.delete-all');
    if(deleteAll) deleteAll.disabled=true;
}

async function deleteAllNotifications(){
    const items=[...document.querySelectorAll('.notification-item')];
    if(!items.length || !confirm('Delete all notifications?')) return;
    const button=document.querySelector('.delete-all');
    if(button) button.disabled=true;

    let failed=0;
    for(const item of items){
        try{
            const body='notification_id='+encodeURIComponent(item.dataset.notificationId);
            const response=await fetch('delete_notification.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body
            });
            const data=await response.json();
            if(data.success) item.remove();
            else failed++;
        }catch(e){failed++;}
    }

    updateUnreadCount();
    showEmptyNotificationsIfNeeded();
    if(failed>0){
        if(button) button.disabled=false;
        alert(failed+' notification(s) could not be deleted.');
    }
}

const BOOKING_PER_PAGE = 6;
let currentBookingPage = 1;

function renderBookingPage(){
    const cards=[...document.querySelectorAll('.booking-grid .booking-card')];
    const pagination=document.getElementById('bookingPagination');
    if(!cards.length){
        if(pagination) pagination.style.display='none';
        return;
    }

    const totalPages=Math.max(1,Math.ceil(cards.length/BOOKING_PER_PAGE));
    if(currentBookingPage>totalPages) currentBookingPage=totalPages;
    if(currentBookingPage<1) currentBookingPage=1;

    const start=(currentBookingPage-1)*BOOKING_PER_PAGE;
    const end=start+BOOKING_PER_PAGE;

    cards.forEach((card,index)=>{
        card.classList.toggle('page-hidden',index<start || index>=end);
    });

    const info=document.getElementById('bookingPageInfo');
    const prev=document.getElementById('prevBookingBtn');
    const next=document.getElementById('nextBookingBtn');

    if(info) info.textContent='Page '+currentBookingPage+' / '+totalPages;
    if(prev) prev.disabled=currentBookingPage===1;
    if(next) next.disabled=currentBookingPage===totalPages;
}

function changeBookingPage(direction){
    const cards=document.querySelectorAll('.booking-grid .booking-card');
    const totalPages=Math.max(1,Math.ceil(cards.length/BOOKING_PER_PAGE));
    const target=currentBookingPage+direction;

    if(target<1 || target>totalPages) return;

    currentBookingPage=target;
    renderBookingPage();

    const bookingSection=document.querySelector('.booking-grid');
    if(bookingSection){
        bookingSection.scrollIntoView({behavior:'smooth',block:'start'});
    }
}

document.addEventListener('DOMContentLoaded',renderBookingPage);

</script>
</body>
</html>