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
//
// This is the ONLY place a booking's status becomes Approved/Rejected.
// On Approve we re-check, inside a locked transaction, that no other
// Approved booking has taken the same court+date+time slot in the
// meantime (requirement: final availability check before approval).
// The linked payment row is kept in sync automatically so admins never
// have to separately "approve" a payment on a different screen.
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
                // A booking can only be approved after a payment receipt exists.
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

                // Final availability check.
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

// Pending bookings first so admins see what needs action right away.
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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Management - Badminton Kampung Panji</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{margin:0;background:#f1f5f9;font-family:Arial,sans-serif}
.sidebar{position:fixed;width:280px;height:100vh;background:#1c2434;color:#dee4ee;padding:25px 15px;overflow-y:auto}
.sidebar a{display:block;color:#dee4ee;text-decoration:none;padding:12px 15px;border-radius:10px;margin-bottom:5px}
.sidebar a:hover,.sidebar a.active{background:#333a48;color:#fff}
.main{margin-left:280px;padding:40px}
.card{border:0;border-radius:20px;box-shadow:0 4px 15px #0000000d}
.brand{font-size:20px;font-weight:800;color:#fff;margin:0 10px 25px}
.table td,.table th{vertical-align:middle}
.reason-input{width:140px;display:inline-block}
@media (max-width:900px){.sidebar{width:70px}.sidebar a span{display:none}.main{margin-left:70px;padding:20px}}
</style>
</head>
<body>
<aside class="sidebar">
    <div class="brand"><span>🏸 Badminton Kampung Panji</span></div>
    <a href="dashboard.php"><i class="fa fa-gauge me-2"></i><span>Dashboard</span></a>
    <a href="calendar.php"><i class="fa fa-calendar me-2"></i><span>Calendar</span></a>
    <a class="active" href="manage_booking.php"><i class="fa fa-book me-2"></i><span>Bookings</span></a>
    <a href="manage_payment.php"><i class="fa fa-credit-card me-2"></i><span>Payments</span></a>
    <a href="manage_court.php"><i class="fa fa-table-tennis-paddle-ball me-2"></i><span>Courts</span></a>
    <a href="manage_users.php"><i class="fa fa-users me-2"></i><span>Users</span></a>
    <a href="../auth/logout.php" class="text-danger mt-4"><i class="fa fa-right-from-bracket me-2"></i><span>Logout</span></a>
</aside>
<main class="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Booking Management</h2>
            <p class="text-muted mb-0">Review the payment receipt, then approve or reject the badminton court booking.</p>
        </div>
        <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="card bg-white p-4">
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
</main>
</body>
</html>
