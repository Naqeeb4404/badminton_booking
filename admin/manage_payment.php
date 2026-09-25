<?php
session_start();
include __DIR__ . '/../config/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: ../auth/login.php'); exit(); }
// Payment status is now driven entirely by the booking's Approve/Reject
// decision on the Bookings page (manage_booking.php), so that a booking
// and its payment can never disagree with each other. This page is a
// read-only record of receipts/payments for reference.
$result=mysqli_query($conn,"SELECT p.*, b.booking_date,b.booking_time,b.status AS booking_status,c.court_name,u.name,u.email FROM payments p JOIN bookings b ON p.booking_id=b.id JOIN courts c ON b.court_id=c.id JOIN users u ON p.user_id=u.id ORDER BY p.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Payments - Badminton Kampung Panji</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body {
        margin: 0;
        background: #f1f5f9;
        font-family: Arial, sans-serif;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    
    /* Topbar & Search Bar Fixing CSS */
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        padding: 15px 25px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
    }
    .search-form {
        position: relative;
        display: flex;
        align-items: center;
        width: 300px;
    }
    .search-form i {
        position: absolute;
        left: 14px;
        color: #6b7280;
        font-size: 0.9rem;
    }
    .search-input {
        width: 100%;
        padding: 10px 14px 10px 40px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.9rem;
        background-color: #f8fafc;
        outline: none;
        transition: all 0.2s ease;
    }
    .search-input:focus {
        border-color: #2563eb;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .user-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        padding: 6px 14px 6px 6px;
        border-radius: 50px;
        border: 1px solid #e5e7eb;
    }
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #0f172a;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
    }
</style>
<link rel="stylesheet" href="sidebar.css">
</head>
<body class="admin-page">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content">
    <header class="topbar">
        <div class="search-form">
            <i class="fa-solid fa-search"></i>
            <input type="text" class="form-control search-input" placeholder="Type to search..." autocomplete="off">
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="user-pill">
                <div class="user-avatar"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user']['name'] ?? 'A', 0, 1))); ?></div>
                <div class="fw-bold fs-7 pe-2 text-dark"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin'); ?></div>
            </div>
            <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </header>

    <div class="content-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Payment Management</h2>
                <p class="text-muted mb-0">View payment receipts linked to each badminton booking. Approval is controlled from Booking Management.</p>
            </div>
        </div>
        <div class="card bg-white p-4 border-0 shadow-sm rounded-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Payment</th>
                            <th>Customer</th>
                            <th>Booking</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Receipt</th>
                            <th>Payment Status</th>
                            <th>Booking Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r=mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?= (int)$r['payment_id'] ?></td>
                            <td><?= htmlspecialchars($r['name']) ?><br><small class="text-muted"><?= htmlspecialchars($r['email']) ?></small></td>
                            <td><?= htmlspecialchars($r['court_name']) ?><br><small><?= htmlspecialchars($r['booking_date']) ?> <?= htmlspecialchars(substr($r['booking_time'],0,5)) ?></small></td>
                            <td>RM <?= number_format((float)$r['amount'],2) ?></td>
                            <td><?= htmlspecialchars($r['payment_method'] ?? '-') ?></td>
                            <td><?php if($r['receipt']): ?><a target="_blank" href="../user/uploads/receipt/<?= rawurlencode($r['receipt']) ?>">View Receipt</a><?php else: ?>-<?php endif; ?></td>
                            <td><span class="badge <?= $r['status']==='Approved'?'bg-success':($r['status']==='Rejected'?'bg-danger':'bg-warning text-dark') ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td><span class="badge <?= $r['booking_status']==='Approved'?'bg-success':($r['booking_status']==='Rejected'?'bg-danger':'bg-warning text-dark') ?>"><?= htmlspecialchars($r['booking_status']) ?></span> <a href="manage_booking.php" class="small">manage &rarr;</a></td>
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