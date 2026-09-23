<?php

session_start();

include __DIR__ . '/../config/db.php';


if(!isset($_SESSION['user']) || $_SESSION['user']['role']!="user"){

    header("Location: ../auth/login.php");
    exit();

}


$user_id = (int)$_SESSION['user']['id'];

// LEFT JOIN payments: a booking may not have a receipt uploaded yet
// ("Payment Pending"), which is how we tell that state apart from
// "Booking Pending" (receipt submitted, waiting on the admin).
$stmt = $conn->prepare("
SELECT
    bookings.*,
    courts.court_name,
    courts.price,
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
// LOGIK TAMBAHAN: KIRA JUMLAH APPROVED BOOKINGS UNTUK VOUCHER
// =========================================================
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'Approved'");
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$count_data = $countStmt->get_result()->fetch_assoc();
$countStmt->close();

$approved_count = $count_data['total'];
$progress_count = $approved_count % 10; // Baki menuju ke 10
$has_voucher = ($approved_count > 0 && $progress_count == 0); // Capai gandaan 10
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking - AceTime</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-app: #f4f4f4;
            --card-dark: #181818;
            --accent-green: #22c55e;
            --accent-orange: #d9622b;
            --text-dark: #111111;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #382319, #120e0c);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 30px 15px;
        }

        /* Main Container */
        .app-container {
            max-width: 1100px;
            margin: 0 auto;
            background-color: var(--bg-app);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            padding: 20px 40px 50px 40px;
        }

        /* Header Navigation */
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

        /* Loyalty Voucher Banner Card */
        .reward-card {
            background: linear-gradient(135deg, #181818 0%, #2a2a2a 100%);
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

        .progress-custom {
            height: 10px;
            background-color: rgba(255,255,255,0.15);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-custom {
            background: linear-gradient(90deg, #ccff00, #22c55e);
            border-radius: 10px;
        }

        /* Booking History Section */
        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #000;
            margin-bottom: 20px;
        }

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

        /* Status Badges */
        .badge-status {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-block;
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
    </style>
</head>

<body>

    <div class="app-container">

        <!-- Header -->
        <nav class="custom-navbar">
            <a href="#" class="brand-logo">AceTime</a>
            <a href="dashboard.php" class="btn-black">
                <i class="fa-solid fa-gauge me-1"></i> Dashboard
            </a>
        </nav>

        <!-- BAHAGIAN GANJARAN & VOUCHER 50% -->
        <div class="reward-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="voucher-badge">
                        <i class="fa-solid fa-gift me-1"></i> Loyalty Reward Program
                    </span>

                    <?php if($has_voucher): ?>
                        <h2 class="fw-bold mb-2 text-warning">
                            🎉 Tahniah! Anda Mendapat Voucher 50%!
                        </h2>
                        <p class="text-white-50 mb-3">
                            Anda telah melengkapkan <strong><?php echo $approved_count; ?> kali</strong> kehadiran berdaftar. Gunakan kod promo di bawah untuk tempahan seterusnya:
                        </p>
                        <div class="d-inline-block bg-white text-dark px-3 py-2 rounded-3 fw-bold fs-5 border border-warning">
                            PROMO: <span class="text-success">SMASH50</span>
                        </div>
                    <?php else: ?>
                        <h3 class="fw-bold mb-1">
                            Main 10 Kali, Dapat Voucher 50%!
                        </h3>
                        <p class="text-white-50 small mb-3">
                            Lengkapkan 10 kali tempahan yang diluluskan (Approved) untuk mem buka tawaran diskaun 50%.
                        </p>
                        
                        <!-- Progress Bar -->
                        <div class="d-flex justify-content-between align-items-center mb-1 small fw-bold">
                            <span>Kemajuan Kehadiran</span>
                            <span class="text-warning"><?php echo $progress_count; ?> / 10 Tempahan</span>
                        </div>
                        <div class="progress progress-custom">
                            <div class="progress-bar progress-bar-custom" 
                                 role="progressbar" 
                                 style="width: <?php echo ($progress_count / 10) * 100; ?>%;">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- REKOD SEJARAH TEMPAHAN -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="section-title mb-0">📅 My Bookings</h3>
            <span class="badge bg-secondary rounded-pill px-3 py-2">
                Total: <?php echo count($bookingRows); ?>
            </span>
        </div>

        <?php if(isset($_GET['submitted'])): ?>
        <div class="alert alert-success mb-3">
            <i class="fa-solid fa-circle-check me-1"></i> Payment receipt submitted. Payment Pending. Your Booking is now Booking Pending while the admin reviews it.
        </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-3"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table custom-table text-center align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="text-start">Court Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Price</th>
                            <th>Payment</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(count($bookingRows) > 0){
                            foreach($bookingRows as $row){
                                // Show payment and booking status separately so the user
                                // always knows which step is waiting.
                                if($row['payment_status'] === 'Approved'){
                                    $paymentBadge = "<span class='badge-status badge-approved'><i class='fa-solid fa-check me-1'></i> Payment Approved</span>";
                                }elseif($row['payment_status'] === 'Rejected'){
                                    $paymentBadge = "<span class='badge-status badge-rejected'><i class='fa-solid fa-xmark me-1'></i> Payment Rejected</span>";
                                }else{
                                    $paymentBadge = "<span class='badge-status badge-pending'><i class='fa-solid fa-credit-card me-1'></i> Payment Pending</span>";
                                }

                                if($row['status'] === 'Approved'){
                                    $bookingBadge = "<span class='badge-status badge-approved'><i class='fa-solid fa-check me-1'></i> Booking Approved</span>";
                                }elseif($row['status'] === 'Rejected'){
                                    $bookingBadge = "<span class='badge-status badge-rejected'><i class='fa-solid fa-xmark me-1'></i> Booking Rejected</span>";
                                }else{
                                    $bookingBadge = "<span class='badge-status badge-pending'><i class='fa-solid fa-hourglass-half me-1'></i> Booking Pending</span>";
                                }
                        ?>
                        <tr id="booking-<?php echo (int)$row['id']; ?>">
                            <td class="text-muted">#<?php echo (int)$row['id']; ?></td>

                            <td class="text-start fw-bold">
                                <i class="fa-solid fa-circle-dot text-success me-2 fs-6"></i>
                                <?php echo htmlspecialchars($row['court_name']); ?>
                            </td>

                            <td>
                                <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                <?php echo htmlspecialchars($row['booking_date']); ?>
                            </td>

                            <td>
                                <i class="fa-regular fa-clock me-1 text-muted"></i>
                                <?php echo htmlspecialchars(substr($row['booking_time'], 0, 5)); ?>
                            </td>

                            <td>RM <?php echo number_format((float)$row['price'], 2); ?></td>

                            <td>
                                <?php if($row['payment_receipt']): ?>
                                    <a href="uploads/receipt/<?php echo rawurlencode($row['payment_receipt']); ?>" target="_blank">View receipt</a>
                                <?php elseif($row['status'] === 'Pending'): ?>
                                    <a href="payment.php?booking_id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-dark">Proceed to Payment</a>
                                <?php elseif($row['status'] === 'Approved'): ?>
                                    <a href="?booking_id=<?php echo (int)$row['id']; ?>#booking-<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-dark">View Booking Details</a>
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo $paymentBadge; ?><br><?php echo $bookingBadge; ?>
                                <?php if($row['status'] === 'Rejected' && !empty($row['rejection_reason'])): ?>
                                    <div class="small text-muted mt-1">Reason: <?php echo htmlspecialchars($row['rejection_reason']); ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                            }
                        }else{
                            echo "
                            <tr>
                                <td colspan='7' class='py-5 text-muted fw-normal'>
                                    <i class='fa-solid fa-folder-open display-6 d-block mb-2 opacity-50'></i>
                                    Tiada rekod tempahan lagi.
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

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>