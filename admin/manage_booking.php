<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// ---------------------------------------------------------------------
// Approve / Reject a booking.
// ---------------------------------------------------------------------
if (isset($_POST['booking_action'], $_POST['booking_id'])) {
    $id = (int)$_POST['booking_id'];
    $action = $_POST['booking_action'];
    $reason = trim($_POST['reason'] ?? '');

    if (in_array($action, ['Approved', 'Rejected'], true)) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT id, court_id, booking_date, booking_time, status FROM bookings WHERE id=? LIMIT 1 FOR UPDATE");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $b = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$b) {
                $conn->rollback();
                header("Location: manage_booking.php?error=" . urlencode("Booking not found."));
                exit();
            }
            if ($b['status'] !== 'Pending') {
                $conn->rollback();
                header("Location: manage_booking.php?error=" . urlencode("This booking has already been processed."));
                exit();
            }

            if ($action === 'Approved') {
                $payCheck = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id=? LIMIT 1 FOR UPDATE");
                $payCheck->bind_param("i", $id);
                $payCheck->execute();
                $paymentRow = $payCheck->get_result()->fetch_assoc();
                $payCheck->close();

                if (!$paymentRow) {
                    $conn->rollback();
                    header("Location: manage_booking.php?error=" . urlencode("Payment receipt is required before approving this booking."));
                    exit();
                }

                $chk = $conn->prepare("SELECT id FROM bookings WHERE court_id=? AND booking_date=? AND booking_time=? AND status='Approved' AND id<>? LIMIT 1 FOR UPDATE");
                $chk->bind_param("issi", $b['court_id'], $b['booking_date'], $b['booking_time'], $id);
                $chk->execute();
                $conflict = $chk->get_result()->fetch_assoc();
                $chk->close();

                if ($conflict) {
                    $conn->rollback();
                    header("Location: manage_booking.php?error=" . urlencode("This court and time slot has already been booked."));
                    exit();
                }

                $upd = $conn->prepare("UPDATE bookings SET status='Approved', rejection_reason=NULL WHERE id=?");
                $upd->bind_param("i", $id);
                $upd->execute();
                $upd->close();

                $updPay = $conn->prepare("UPDATE payments SET status='Approved' WHERE booking_id=?");
                $updPay->bind_param("i", $id);
                $updPay->execute();
                $updPay->close();
            } else {
                $upd = $conn->prepare("UPDATE bookings SET status='Rejected', rejection_reason=? WHERE id=?");
                $upd->bind_param("si", $reason, $id);
                $upd->execute();
                $upd->close();

                $updPay = $conn->prepare("UPDATE payments SET status='Rejected' WHERE booking_id=?");
                $updPay->bind_param("i", $id);
                $updPay->execute();
                $updPay->close();
            }

            $conn->commit();
            header("Location: manage_booking.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            header("Location: manage_booking.php?error=" . urlencode("Action failed. Please try again."));
            exit();
        }
    }
    header('Location: manage_booking.php');
    exit();
}

$result = $conn->query("
    SELECT bookings.*, courts.court_name, courts.price,
           users.name AS user_name, users.email,
           payments.receipt AS payment_receipt,
           payments.status AS payment_status,
           payments.amount AS payment_amount
    FROM bookings
    JOIN courts ON bookings.court_id = courts.id
    JOIN users ON bookings.user_id = users.id
    LEFT JOIN payments ON payments.booking_id = bookings.id
    ORDER BY (bookings.status = 'Pending') DESC, bookings.id DESC
");
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Booking Management - Badminton Kampung Panji</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --sidebar-bg: #1c2434;
        --sidebar-text: #dee4ee;
        --sidebar-hover: #333a48;
        --accent-lime: #ccff00;
        --text-dark: #111111;
        --body-bg: #f1f5f9;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--body-bg);
        color: var(--text-dark);
        min-height: 100vh;
        margin: 0;
        display: flex;
    }

    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .sidebar {
        width: 280px;
        background-color: var(--sidebar-bg);
        color: var(--sidebar-text);
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 100;
        transition: all 0.3s ease;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
    }

    .sidebar-brand {
        padding: 25px 20px;
        font-size: 1.25rem;
        font-weight: 800;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        text-decoration: none;
    }

    .sidebar-menu {
        padding: 20px 15px;
        overflow-y: auto;
        flex-grow: 1;
    }

    .menu-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #8a99ad;
        margin-bottom: 10px;
        padding-left: 10px;
        font-weight: 700;
    }

    .sidebar-nav-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        color: var(--sidebar-text);
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 5px;
        transition: all 0.2s ease;
    }

    .sidebar-nav-link-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-nav-link:hover, .sidebar-nav-link.active {
        background-color: var(--sidebar-hover);
        color: #fff;
    }

    .sidebar-nav-link i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }

    .main-content {
        margin-left: 280px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .topbar {
        height: 80px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        position: sticky;
        top: 0;
        z-index: 99;
    }

    .search-form {
        position: relative;
        width: 350px;
    }

    .search-input {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 50px !important;
        padding: 10px 20px 10px 45px !important;
        font-size: 0.85rem !important;
        width: 100% !important;
        color: #1e293b !important;
        outline: none !important;
    }

    .search-form i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 5;
        pointer-events: none;
    }

    .user-pill {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        padding: 6px 16px 6px 6px;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--sidebar-bg);
        color: var(--accent-lime);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
    }

    .content-body {
        padding: 40px;
        flex-grow: 1;
    }

    .card {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02) !important;
        background: #ffffff;
    }

    .table td, .table th {
        vertical-align: middle;
    }

    .reason-input {
        width: 140px;
        display: inline-block;
    }

    @media (max-width: 768px) {
        .sidebar { width: 70px; }
        .sidebar .sidebar-brand span, .sidebar .menu-label, .sidebar .sidebar-nav-link span, .sidebar .badge { display: none; }
        .main-content { margin-left: 70px; }
        .topbar { padding: 0 20px; }
        .search-form { display: none; }
    }
</style>
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <header class="topbar">
            <div class="search-form">
                <i class="fa-solid fa-search"></i>
                <input type="text" class="form-control search-input" placeholder="Type to search..." autocomplete="off">
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="user-pill">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                    <div class="fw-bold fs-7 pe-2"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </header>

        <div class="content-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Booking Management</h2>
                    <p class="text-muted mb-0">Review the payment receipt, then approve or reject the badminton court booking.</p>
                </div>
            </div>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="card p-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Court</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Total</th>
                                <th>Receipt</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = $result->fetch_assoc()):
                            $endTime = date('H:i', strtotime($r['booking_time']) + 3600);
                        ?>
                            <tr>
                                <td>#<?= (int)$r['id'] ?></td>
                                <td><?= htmlspecialchars($r['user_name']) ?><br><small class="text-muted"><?= htmlspecialchars($r['email']) ?></small></td>
                                <td><?= htmlspecialchars($r['court_name']) ?></td>
                                <td><?= htmlspecialchars($r['booking_date']) ?></td>
                                <td><?= htmlspecialchars(substr($r['booking_time'],0,5)) ?> - <?= $endTime ?></td>
                                <td>RM <?= number_format((float)$r['price'], 2) ?></td>
                                <td>
                                    <?php if($r['payment_receipt']): ?>
                                        <a target="_blank" href="../user/uploads/receipt/<?= rawurlencode($r['payment_receipt']) ?>">View Receipt</a>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($r['payment_status']): ?>
                                        <span class="badge <?= $r['payment_status']==='Approved'?'bg-success':($r['payment_status']==='Rejected'?'bg-danger':'bg-warning text-dark') ?>"><?= htmlspecialchars($r['payment_status']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No receipt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $r['status']==='Approved'?'bg-success':($r['status']==='Rejected'?'bg-danger':'bg-warning text-dark') ?>"><?= htmlspecialchars($r['status']) ?></span>
                                    <?php if($r['status']==='Rejected' && !empty($r['rejection_reason'])): ?>
                                        <div class="small text-muted mt-1"><?= htmlspecialchars($r['rejection_reason']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($r['status']==='Pending'): ?>
                                        <form method="post" class="d-flex flex-column gap-1">
                                            <input type="hidden" name="booking_id" value="<?= (int)$r['id'] ?>">
                                            <div class="d-flex gap-1">
                                                <button name="booking_action" value="Approved" class="btn btn-success btn-sm" <?= $r['payment_receipt'] ? '' : 'disabled title="Waiting for receipt"' ?>>Approve Booking</button>
                                                <button name="booking_action" value="Rejected" class="btn btn-danger btn-sm">Reject Booking</button>
                                            </div>
                                            <input type="text" name="reason" class="form-control form-control-sm reason-input" placeholder="Reason for rejection (optional)">
                                        </form>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>