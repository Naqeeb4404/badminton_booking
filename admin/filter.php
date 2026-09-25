<?php
session_start();
include "../config/db.php";

// Pastikan hanya admin boleh masuk
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil nilai filter
$date = $_GET['date'] ?? '';
$month = $_GET['month'] ?? '';
$court_id = $_GET['court_id'] ?? '';
$status = $_GET['status'] ?? '';
$customer = $_GET['customer'] ?? '';

// Ambil senarai court
$courts = mysqli_query($conn, "SELECT id, court_name FROM courts ORDER BY court_name ASC");

// Query booking
$sql = "
    SELECT 
        b.booking_date,
        b.booking_time,
        b.duration,
        b.status,
        b.rejection_reason,
        c.court_name,
        u.name AS customer_name,
        u.email,
        p.amount,
        p.status AS payment_status
    FROM bookings b
    LEFT JOIN courts c ON b.court_id = c.id
    LEFT JOIN users u ON b.user_id = u.id
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE 1=1
";

// Filter date
if (!empty($date)) {
    $date_safe = mysqli_real_escape_string($conn, $date);
    $sql .= " AND b.booking_date = '$date_safe'";
}

// Filter month
if (!empty($month)) {
    $month_safe = mysqli_real_escape_string($conn, $month);
    $sql .= " AND DATE_FORMAT(b.booking_date, '%Y-%m') = '$month_safe'";
}

// Filter court
if (!empty($court_id)) {
    $court_safe = (int)$court_id;
    $sql .= " AND b.court_id = $court_safe";
}

// Filter status
if (!empty($status)) {
    $status_safe = mysqli_real_escape_string($conn, $status);
    $sql .= " AND b.status = '$status_safe'";
}

// Filter customer
if (!empty($customer)) {
    $customer_safe = mysqli_real_escape_string($conn, $customer);
    $sql .= " AND u.name LIKE '%$customer_safe%'";
}

$sql .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Filter Booking | Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
            font-family: Arial, sans-serif;
        }

        .container-box {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .filter-card {
            padding: 25px;
            margin-bottom: 25px;
        }

        .table-card {
            padding: 25px;
        }

        .btn-filter {
            min-width: 120px;
        }

        table {
            vertical-align: middle;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }
    </style>
</head>

<body>

<div class="container-box">

    <!-- TITLE -->
    <div class="mb-4">
        <h2 class="page-title">Filter Booking</h2>
        <p class="text-muted">
            Search and filter badminton court bookings.
        </p>
    </div>

    <!-- FILTER -->
    <div class="card filter-card">

        <form method="GET">

            <div class="row g-3">

                <!-- DATE -->
                <div class="col-md-3">
                    <label class="form-label">Booking Date</label>
                    <input
                        type="date"
                        name="date"
                        class="form-control"
                        value="<?= htmlspecialchars($date) ?>"
                    >
                </div>

                <!-- MONTH -->
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input
                        type="month"
                        name="month"
                        class="form-control"
                        value="<?= htmlspecialchars($month) ?>"
                    >
                </div>

                <!-- COURT -->
                <div class="col-md-3">
                    <label class="form-label">Court</label>

                    <select name="court_id" class="form-select">
                        <option value="">All Courts</option>

                        <?php while ($court = mysqli_fetch_assoc($courts)): ?>

                            <option
                                value="<?= $court['id'] ?>"
                                <?= ($court_id == $court['id']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($court['court_name']) ?>
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <!-- STATUS -->
                <div class="col-md-3">
                    <label class="form-label">Booking Status</label>

                    <select name="status" class="form-select">

                        <option value="">All Status</option>

                        <option value="Pending"
                            <?= ($status == 'Pending') ? 'selected' : '' ?>>
                            Pending
                        </option>

                        <option value="Approved"
                            <?= ($status == 'Approved') ? 'selected' : '' ?>>
                            Approved
                        </option>

                        <option value="Rejected"
                            <?= ($status == 'Rejected') ? 'selected' : '' ?>>
                            Rejected
                        </option>

                    </select>
                </div>

                <!-- CUSTOMER -->
                <div class="col-md-6">

                    <label class="form-label">Customer Name</label>

                    <input
                        type="text"
                        name="customer"
                        class="form-control"
                        placeholder="Search customer..."
                        value="<?= htmlspecialchars($customer) ?>"
                    >

                </div>

                <!-- BUTTON -->
                <div class="col-md-6 d-flex align-items-end gap-2">

                    <button
                        type="submit"
                        class="btn btn-dark btn-filter"
                    >
                        Filter
                    </button>

                    <a
                        href="filter.php"
                        class="btn btn-outline-secondary btn-filter"
                    >
                        Reset
                    </a>

                </div>

            </div>

        </form>

    </div>

    <!-- RESULT -->
    <div class="card table-card">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>
                <h5 class="mb-1">Booking Results</h5>
                <small class="text-muted">
                    Showing filtered booking records
                </small>
            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Court</th>
                        <th>Duration</th>
                        <th>Booking Status</th>
                        <th>Amount</th>
                        <th>Payment</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($result && mysqli_num_rows($result) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($row['booking_date']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['booking_time']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['customer_name'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['email'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['court_name'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['duration']) ?> hour
                            </td>

                            <td>

                                <?php if ($row['status'] === 'Approved'): ?>

                                    <span class="badge bg-success">
                                        Approved
                                    </span>

                                <?php elseif ($row['status'] === 'Rejected'): ?>

                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                RM <?= number_format((float)($row['amount'] ?? 0), 2) ?>
                            </td>

                            <td>

                                <?php if (($row['payment_status'] ?? '') === 'Approved'): ?>

                                    <span class="badge bg-success">
                                        Paid
                                    </span>

                                <?php elseif (($row['payment_status'] ?? '') === 'Rejected'): ?>

                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            No booking found.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>