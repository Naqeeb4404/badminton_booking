<?php
session_start();
include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

date_default_timezone_set('Asia/Kuala_Lumpur');

/* =========================
   FILTER
========================= */
$reportDate = $_GET['date'] ?? date('Y-m-d');
$statusFilter = $_GET['status'] ?? 'All';

$allowedStatus = ['All', 'Approved', 'Pending', 'Rejected'];

if (!in_array($statusFilter, $allowedStatus, true)) {
    $statusFilter = 'All';
}

/* Back / Next date */
$previousDate = date('Y-m-d', strtotime($reportDate . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($reportDate . ' +1 day'));

/* =========================
   STATISTIK HARIAN
========================= */
$summaryQuery = $conn->prepare("
    SELECT
        COUNT(DISTINCT b.id) AS total_bookings,

        COUNT(DISTINCT CASE
            WHEN b.status = 'Approved'
            THEN b.id
        END) AS approved_bookings,

        COUNT(DISTINCT CASE
            WHEN b.status = 'Pending'
            THEN b.id
        END) AS pending_bookings,

        COUNT(DISTINCT CASE
            WHEN b.status = 'Rejected'
            THEN b.id
        END) AS rejected_bookings,

        COALESCE(SUM(
            CASE
                WHEN b.status = 'Approved'
                AND p.status = 'Approved'
                THEN p.amount
                ELSE 0
            END
        ), 0) AS total_revenue,

        COUNT(DISTINCT b.user_id) AS total_customers

    FROM bookings b

    LEFT JOIN payments p
        ON b.id = p.booking_id

    WHERE b.booking_date = ?
");

$summaryQuery->bind_param("s", $reportDate);
$summaryQuery->execute();

$stats = $summaryQuery
    ->get_result()
    ->fetch_assoc();

/* =========================
   COURT USAGE
========================= */

if ($statusFilter === 'All') {

    $courtUsageQuery = $conn->prepare("
        SELECT
            c.court_name,
            COUNT(b.id) AS count_booked

        FROM courts c

        LEFT JOIN bookings b
            ON c.id = b.court_id
            AND b.booking_date = ?
            AND b.status = 'Approved'

        WHERE c.status <> 'Deleted'

        GROUP BY c.id, c.court_name
        ORDER BY c.id ASC
    ");

    $courtUsageQuery->bind_param(
        "s",
        $reportDate
    );

} else {

    $courtUsageQuery = $conn->prepare("
        SELECT
            c.court_name,
            COUNT(b.id) AS count_booked

        FROM courts c

        LEFT JOIN bookings b
            ON c.id = b.court_id
            AND b.booking_date = ?
            AND b.status = ?

        WHERE c.status <> 'Deleted'

        GROUP BY c.id, c.court_name
        ORDER BY c.id ASC
    ");

    $courtUsageQuery->bind_param(
        "ss",
        $reportDate,
        $statusFilter
    );
}

$courtUsageQuery->execute();
$courtUsageResult = $courtUsageQuery->get_result();

/* =========================
   BOOKING DETAIL
========================= */

if ($statusFilter === 'All') {

    $bookingQuery = $conn->prepare("
        SELECT
            b.id,
            b.booking_date,
            b.booking_time,
            b.duration,
            b.status,
            u.name AS customer_name,
            c.court_name

        FROM bookings b

        LEFT JOIN users u
            ON b.user_id = u.id

        LEFT JOIN courts c
            ON b.court_id = c.id

        WHERE b.booking_date = ?

        ORDER BY b.booking_time ASC
    ");

    $bookingQuery->bind_param(
        "s",
        $reportDate
    );

} else {

    $bookingQuery = $conn->prepare("
        SELECT
            b.id,
            b.booking_date,
            b.booking_time,
            b.duration,
            b.status,
            u.name AS customer_name,
            c.court_name

        FROM bookings b

        LEFT JOIN users u
            ON b.user_id = u.id

        LEFT JOIN courts c
            ON b.court_id = c.id

        WHERE b.booking_date = ?
        AND b.status = ?

        ORDER BY b.booking_time ASC
    ");

    $bookingQuery->bind_param(
        "ss",
        $reportDate,
        $statusFilter
    );
}

$bookingQuery->execute();
$bookingResult = $bookingQuery->get_result();
?>

<!DOCTYPE html>
<html lang="ms">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Laporan Harian - Admin</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<link
    rel="stylesheet"
    href="sidebar.css?v=20260926"
>

<style>

body.admin-page .sidebar,
body.admin-page .main-content {
    transition: none !important;
}

/* =========================
   REPORT HEADER
========================= */

.report-title {
    font-weight: 800;
    margin-bottom: 4px;
}

.report-date {
    color: #94a3b8;
    font-size: 14px;
}

/* =========================
   FILTER
========================= */

.report-filter {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.report-filter .form-control,
.report-filter .form-select {
    min-height: 38px;
    border-radius: 9px;
}

/* =========================
   NAVIGATION BUTTON
========================= */

.date-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.date-navigation .btn {
    min-width: 110px;
    border-radius: 9px;
    font-weight: 700;
}

.current-date-box {
    background: #17233a;
    border: 1px solid #263550;
    color: #ffffff;
    padding: 9px 20px;
    border-radius: 9px;
    text-align: center;
    font-weight: 700;
}

/* =========================
   STATUS
========================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 85px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    color: #000 !important;
}

.status-approved {
    background: #86efac;
}

.status-pending {
    background: #fde047;
}

.status-rejected {
    background: #fca5a5;
}

/* =========================
   TABLE
========================= */

.report-table th {
    white-space: nowrap;
}

.report-table td {
    vertical-align: middle;
}

.empty-data {
    padding: 30px !important;
    text-align: center;
    color: #94a3b8 !important;
}

</style>

</head>

<body class="admin-page">

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content">

<!-- =========================
     TOPBAR
========================= -->

<header class="topbar">

    <div class="search-form">

        <i class="fa-solid fa-search"></i>

        <input
            type="text"
            class="form-control search-input"
            placeholder="Taip untuk cari..."
            autocomplete="off"
        >

    </div>

    <div class="d-flex align-items-center gap-3">

        <div class="user-pill">

            <div
                class="user-avatar"
                style="<?php echo $admin_photo_style; ?>"
            >

                <?php
                echo $admin_photo === ''
                    ? htmlspecialchars(
                        $admin_initial,
                        ENT_QUOTES,
                        'UTF-8'
                    )
                    : '';
                ?>

            </div>

            <div class="fw-bold fs-7 pe-2">

                <?php
                echo htmlspecialchars(
                    $current_admin['name']
                    ?? $_SESSION['user']['name']
                    ?? 'Admin'
                );
                ?>

            </div>

        </div>

        <a
            href="../auth/logout.php"
            class="btn btn-danger btn-sm rounded-pill fw-bold px-3"
        >

            <i class="fa-solid fa-right-from-bracket me-1"></i>
            Log Keluar

        </a>

    </div>

</header>

<!-- =========================
     CONTENT
========================= -->

<main class="content-body">

<!-- TITLE + FILTER -->

<div
    class="d-flex justify-content-between align-items-start
           flex-wrap gap-3 mb-4"
>

    <div>

        <h2 class="report-title">
            Laporan Harian Tempahan
        </h2>

        <div class="report-date">

            Laporan untuk

            <strong>
                <?= htmlspecialchars($reportDate) ?>
            </strong>

        </div>

    </div>

    <form
        method="GET"
        class="report-filter"
    >

        <input
            type="date"
            name="date"
            value="<?= htmlspecialchars($reportDate) ?>"
            class="form-control form-control-sm"
        >

        <select
            name="status"
            class="form-select form-select-sm"
        >

            <option
                value="All"
                <?= $statusFilter === 'All' ? 'selected' : '' ?>
            >
                Semua Status
            </option>

            <option
                value="Approved"
                <?= $statusFilter === 'Approved' ? 'selected' : '' ?>
            >
                Approved
            </option>

            <option
                value="Pending"
                <?= $statusFilter === 'Pending' ? 'selected' : '' ?>
            >
                Pending
            </option>

            <option
                value="Rejected"
                <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>
            >
                Rejected
            </option>

        </select>

        <button
            type="submit"
            class="btn btn-primary btn-sm px-3"
        >

            <i class="fa-solid fa-filter me-1"></i>
            Filter

        </button>

    </form>

</div>

<!-- =========================
     BACK / NEXT
========================= -->

<div class="date-navigation">

    <a
        href="?date=<?= urlencode($previousDate) ?>&status=<?= urlencode($statusFilter) ?>"
        class="btn btn-outline-primary"
    >

        <i class="fa-solid fa-chevron-left me-1"></i>
        Back

    </a>

    <div class="current-date-box">

        <i class="fa-solid fa-calendar-day me-2"></i>

        <?= date(
            'd M Y',
            strtotime($reportDate)
        ) ?>

    </div>

    <a
        href="?date=<?= urlencode($nextDate) ?>&status=<?= urlencode($statusFilter) ?>"
        class="btn btn-primary"
    >

        Next
        <i class="fa-solid fa-chevron-right ms-1"></i>

    </a>

</div>

<!-- =========================
     SUMMARY
========================= -->

<div class="row g-3 mb-4">

    <div class="col-md-4">

        <div class="card border-0 shadow-sm p-3">

            <span class="text-muted small">
                Jumlah Tempahan
            </span>

            <h4 class="fw-bold mb-0">

                <?= (int)($stats['total_bookings'] ?? 0) ?>

            </h4>

        </div>

    </div>

    <div class="col-md-4">

        <div
            class="card border-0 shadow-sm p-3
                   border-start border-success border-4"
        >

            <span class="text-muted small">
                Tempahan Diluluskan
            </span>

            <h4 class="fw-bold text-success mb-0">

                <?= (int)($stats['approved_bookings'] ?? 0) ?>

            </h4>

        </div>

    </div>

    <div class="col-md-4">

        <div
            class="card border-0 shadow-sm p-3
                   border-start border-danger border-4"
        >

            <span class="text-muted small">
                Pendapatan Harian
            </span>

            <h4 class="fw-bold text-danger mb-0">

                RM
                <?= number_format(
                    $stats['total_revenue'] ?? 0,
                    2
                ) ?>

            </h4>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm p-3">

            <span class="text-muted small">
                Dalam Proses (Pending)
            </span>

            <h4 class="fw-bold text-warning mb-0">

                <?= (int)($stats['pending_bookings'] ?? 0) ?>

            </h4>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm p-3">

            <span class="text-muted small">
                Ditolak
            </span>

            <h4 class="fw-bold text-secondary mb-0">

                <?= (int)($stats['rejected_bookings'] ?? 0) ?>

            </h4>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm p-3">

            <span class="text-muted small">
                Bilangan Pelanggan Unik
            </span>

            <h4 class="fw-bold text-info mb-0">

                <?= (int)($stats['total_customers'] ?? 0) ?>

            </h4>

        </div>

    </div>

</div>

<!-- =========================
     BOOKING DETAIL
========================= -->

<div class="card border-0 shadow-sm mb-4">

    <div
        class="card-header bg-white py-3
               d-flex justify-content-between align-items-center"
    >

        <h5 class="mb-0">

            <i class="fa-solid fa-list me-2"></i>
            Senarai Tempahan

        </h5>

        <span class="badge bg-primary">

            <?= htmlspecialchars($statusFilter) ?>

        </span>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table
                class="table table-bordered align-middle report-table"
            >

                <thead class="table-light">

                    <tr>

                        <th>#</th>
                        <th>Pelanggan</th>
                        <th>Gelanggang</th>
                        <th>Masa</th>
                        <th>Tempoh</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($bookingResult->num_rows > 0): ?>

                    <?php
                    $number = 1;

                    while (
                        $booking =
                        $bookingResult->fetch_assoc()
                    ):
                    ?>

                    <tr>

                        <td>
                            <?= $number++ ?>
                        </td>

                        <td class="fw-semibold">

                            <?= htmlspecialchars(
                                $booking['customer_name']
                                ?? 'Unknown'
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $booking['court_name']
                                ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <?= date(
                                'h:i A',
                                strtotime(
                                    $booking['booking_time']
                                )
                            ) ?>

                        </td>

                        <td>

                            <?= (int)$booking['duration'] ?>
                            Jam

                        </td>

                        <td>

                            <?php
                            $status =
                                $booking['status'];

                            $statusClass =
                                $status === 'Approved'
                                    ? 'status-approved'
                                    : (
                                        $status === 'Pending'
                                            ? 'status-pending'
                                            : 'status-rejected'
                                    );
                            ?>

                            <span
                                class="status-badge <?= $statusClass ?>"
                            >

                                <?= htmlspecialchars($status) ?>

                            </span>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="6"
                            class="empty-data"
                        >

                            <i
                                class="fa-solid fa-calendar-xmark
                                       fs-3 d-block mb-2"
                            ></i>

                            Tiada tempahan dijumpai untuk
                            tarikh dan filter ini.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================
     COURT USAGE
========================= -->

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white py-3">

        <h5 class="mb-0">

            Status Penggunaan Gelanggang pada
            <?= htmlspecialchars($reportDate) ?>

        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table
                class="table table-bordered align-middle"
            >

                <thead class="table-light">

                    <tr>

                        <th>
                            Nama Gelanggang
                        </th>

                        <th>
                            Jumlah Tempahan
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php
                while (
                    $c =
                    $courtUsageResult->fetch_assoc()
                ):
                ?>

                    <tr>

                        <td class="fw-bold">

                            <?= htmlspecialchars(
                                $c['court_name']
                            ) ?>

                        </td>

                        <td>

                            <?= (int)$c['count_booked'] ?>
                            sesi

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</main>
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>