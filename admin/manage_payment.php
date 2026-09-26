```php
<?php
session_start();

include __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| GET PAYMENT DATA
|--------------------------------------------------------------------------
| Payment status is driven by the booking approval/rejection.
| This page is read-only for payment records.
*/
$result = mysqli_query(
    $conn,
    "SELECT 
        p.*,
        b.booking_date,
        b.booking_time,
        b.status AS booking_status,
        c.court_name,
        u.name,
        u.email
     FROM payments p
     JOIN bookings b ON p.booking_id = b.id
     JOIN courts c ON b.court_id = c.id
     JOIN users u ON p.user_id = u.id
     ORDER BY p.payment_date DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Management | Badminton Kampung Panji</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="sidebar.css?v=20260926">

    <style>

        /* =========================================================
           GENERAL
        ========================================================= */

        body {
            background: #eef3f8;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        /* =========================================================
           TOP BAR
        ========================================================= */

        .topbar {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 18px;
            padding: 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .topbar-search {
            border: 1px solid #d5dce5;
            border-radius: 12px;
            padding: 10px 15px;
            background: #ffffff;
            color: #000000;
            outline: none;
            width: 280px;
        }

        .topbar-search::placeholder {
            color: #64748b;
        }

        .topbar-search:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .admin-pill {
            background: #f1f5f9;
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            padding: 8px 14px;
            color: #000000;
            font-weight: 600;
        }

        .logout-btn {
            border-radius: 10px;
            padding: 9px 15px;
            font-weight: 600;
        }

        /* =========================================================
           PAGE TITLE
        ========================================================= */

        .page-title {
            color: #000000;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: #000000;
            opacity: 0.65;
            margin-bottom: 25px;
        }

        /* =========================================================
           FILTER CARD
        ========================================================= */

        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #dbe3ec;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .filter-title {
            color: #000000 !important;
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .filter-label {
            color: #000000 !important;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 7px;
        }

        .filter-select,
        .search-input {
            width: 100%;
            height: 44px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            color: #000000 !important;
            padding: 0 13px;
            outline: none;
        }

        .filter-select:focus,
        .search-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .search-input::placeholder {
            color: #64748b;
        }

        .reset-btn {
            height: 44px;
            border-radius: 10px;
            font-weight: 700;
            padding: 0 18px;
        }

        /* =========================================================
           PAYMENT TABLE CARD
        ========================================================= */

        .table-card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid #dbe3ec;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
        }

        .table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-header h5 {
            color: #000000 !important;
            font-weight: 800;
            margin: 0;
        }

        .payment-table {
            margin: 0;
            width: 100%;
        }

        /* TABLE HEADER - BLACK */

        .payment-table thead th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            font-weight: 800 !important;
            border-bottom: 1px solid #cbd5e1;
            padding: 15px;
            white-space: nowrap;
        }

        /* TABLE CONTENT - BLACK */

        .payment-table tbody td {
            color: #000000 !important;
            padding: 16px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .payment-table tbody tr:hover {
            background: #f8fafc;
        }

        /* =========================================================
           PAYMENT DETAILS - BLACK
        ========================================================= */

        .payment-id {
            color: #000000 !important;
            font-weight: 800;
        }

        .customer-name {
            color: #000000 !important;
            font-weight: 700;
        }

        .customer-email {
            color: #000000 !important;
            font-size: 13px;
        }

        .booking-court {
            color: #000000 !important;
            font-weight: 700;
        }

        .booking-info {
            color: #000000 !important;
            font-size: 13px;
        }

        .amount {
            color: #000000 !important;
            font-weight: 800;
            white-space: nowrap;
        }

        .payment-method {
            color: #000000 !important;
            font-weight: 600;
        }

        /* Bootstrap text-muted inside payment table */
        .payment-table .text-muted {
            color: #000000 !important;
        }

        /* =========================================================
           STATUS BADGES
        ========================================================= */

        .status-badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-approved {
            background: #dcfce7 !important;
            color: #166534 !important;
        }

        .status-rejected {
            background: #fee2e2 !important;
            color: #991b1b !important;
        }

        .status-pending {
            background: #fef3c7 !important;
            color: #92400e !important;
        }

        /* =========================================================
           RECEIPT BUTTON
        ========================================================= */

        .receipt-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #2563eb !important;
            text-decoration: none;
            font-weight: 700;
        }

        .receipt-btn:hover {
            color: #1d4ed8 !important;
            text-decoration: underline;
        }

        /* =========================================================
           MANAGE LINK
        ========================================================= */

        .manage-link {
            color: #2563eb !important;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }

        .manage-link:hover {
            color: #1d4ed8 !important;
            text-decoration: underline;
        }

        /* =========================================================
           NO RESULT
        ========================================================= */

        .no-result {
            text-align: center;
            padding: 40px !important;
            color: #000000 !important;
            font-weight: 600;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 992px) {

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .topbar-search {
                width: 200px;
            }

            .table-card {
                overflow-x: auto;
            }

            .payment-table {
                min-width: 1100px;
            }
        }

        @media (max-width: 576px) {

            .main-content {
                padding: 15px;
            }

            .topbar {
                padding: 12px;
            }

            .topbar-search {
                width: 100%;
                margin-bottom: 10px;
            }

            .page-title {
                font-size: 23px;
            }
        }

    </style>
</head>

<body>

    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <?php include __DIR__ . '/sidebar.php'; ?>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <div class="main-content">

        <!-- TOP BAR -->

        <div class="topbar">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <input
                        type="text"
                        id="topSearch"
                        class="topbar-search"
                        placeholder="Search payment, customer or court...">
                </div>

                <div class="d-flex align-items-center gap-2">

                    <div class="admin-pill">
                        <i class="fa-solid fa-user-shield me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin'); ?>
                    </div>

                    <a
                        href="../auth/logout.php"
                        class="btn btn-outline-danger logout-btn">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>
                        Logout
                    </a>

                </div>

            </div>

        </div>


        <!-- PAGE TITLE -->

        <div class="page-title">
            <i class="fa-solid fa-credit-card me-2"></i>
            Payment Management
        </div>

        <div class="page-subtitle">
            View and filter customer payment records.
        </div>


        <!-- =====================================================
             FILTER
        ====================================================== -->

        <div class="filter-card">

            <div class="filter-title">
                <i class="fa-solid fa-filter me-2"></i>
                Filter Payments
            </div>

            <div class="row g-3">

                <!-- SEARCH -->

                <div class="col-lg-3 col-md-6">

                    <label class="filter-label">
                        Search Customer / Email / Court
                    </label>

                    <input
                        type="text"
                        id="paymentSearch"
                        class="search-input"
                        placeholder="Type to search...">

                </div>


                <!-- PAYMENT STATUS -->

                <div class="col-lg-2 col-md-6">

                    <label class="filter-label">
                        Payment Status
                    </label>

                    <select id="paymentStatusFilter" class="filter-select">

                        <option value="all">
                            All
                        </option>

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Approved">
                            Approved
                        </option>

                        <option value="Rejected">
                            Rejected
                        </option>

                    </select>

                </div>


                <!-- BOOKING STATUS -->

                <div class="col-lg-2 col-md-6">

                    <label class="filter-label">
                        Booking Status
                    </label>

                    <select id="bookingStatusFilter" class="filter-select">

                        <option value="all">
                            All
                        </option>

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Approved">
                            Approved
                        </option>

                        <option value="Rejected">
                            Rejected
                        </option>

                    </select>

                </div>


                <!-- PAYMENT METHOD -->

                <div class="col-lg-2 col-md-6">

                    <label class="filter-label">
                        Payment Method
                    </label>

                    <select id="paymentMethodFilter" class="filter-select">

                        <option value="all">
                            All
                        </option>

                        <option value="MAE">
                            MAE
                        </option>

                        <option value="Touch N Go">
                            Touch N Go
                        </option>

                        <option value="Bank Islam">
                            Bank Islam
                        </option>

                        <option value="Card Payment">
                            Card Payment
                        </option>

                    </select>

                </div>


                <!-- RESET -->

                <div class="col-lg-3 col-md-6 d-flex align-items-end">

                    <button
                        type="button"
                        id="resetFilters"
                        class="btn btn-secondary reset-btn">

                        <i class="fa-solid fa-rotate-left me-2"></i>
                        Reset Filter

                    </button>

                </div>

            </div>

        </div>


        <!-- =====================================================
             PAYMENT TABLE
        ====================================================== -->

        <div class="table-card">

            <div class="table-header">

                <h5>
                    <i class="fa-solid fa-receipt me-2"></i>
                    Payment Records
                </h5>

            </div>


            <div class="table-responsive">

                <table class="table payment-table">

                    <thead>

                        <tr>

                            <th>
                                Payment
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Booking
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Method
                            </th>

                            <th>
                                Receipt
                            </th>

                            <th>
                                Payment Status
                            </th>

                            <th>
                                Booking Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody id="paymentTableBody">

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <?php

                            $paymentStatus = $row['status'] ?? 'Pending';
                            $bookingStatus = $row['booking_status'] ?? 'Pending';
                            $paymentMethod = $row['payment_method'] ?? '-';

                            $paymentStatusClass = 'status-pending';

                            if ($paymentStatus === 'Approved') {
                                $paymentStatusClass = 'status-approved';
                            } elseif ($paymentStatus === 'Rejected') {
                                $paymentStatusClass = 'status-rejected';
                            }

                            $bookingStatusClass = 'status-pending';

                            if ($bookingStatus === 'Approved') {
                                $bookingStatusClass = 'status-approved';
                            } elseif ($bookingStatus === 'Rejected') {
                                $bookingStatusClass = 'status-rejected';
                            }

                            ?>

                            <tr
                                class="payment-row"
                                data-payment-status="<?php echo htmlspecialchars($paymentStatus); ?>"
                                data-booking-status="<?php echo htmlspecialchars($bookingStatus); ?>"
                                data-payment-method="<?php echo htmlspecialchars($paymentMethod); ?>"
                                data-search="<?php
                                    echo htmlspecialchars(
                                        strtolower(
                                            ($row['id'] ?? '') . ' ' .
                                            ($row['name'] ?? '') . ' ' .
                                            ($row['email'] ?? '') . ' ' .
                                            ($row['court_name'] ?? '') . ' ' .
                                            ($paymentMethod ?? '')
                                        )
                                    );
                                ?>">

                                <!-- PAYMENT -->

                                <td>

                                    <div class="payment-id">
                                        #<?php echo htmlspecialchars($row['id']); ?>
                                    </div>

                                    <?php if (!empty($row['payment_date'])): ?>

                                        <small class="text-muted">
                                            <?php
                                            echo htmlspecialchars(
                                                date(
                                                    'd M Y, h:i A',
                                                    strtotime($row['payment_date'])
                                                )
                                            );
                                            ?>
                                        </small>

                                    <?php endif; ?>

                                </td>


                                <!-- CUSTOMER -->

                                <td>

                                    <div class="customer-name">
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </div>

                                    <div class="customer-email">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </div>

                                </td>


                                <!-- BOOKING -->

                                <td>

                                    <div class="booking-court">
                                        <?php echo htmlspecialchars($row['court_name']); ?>
                                    </div>

                                    <div class="booking-info">

                                        <?php
                                        echo htmlspecialchars(
                                            date(
                                                'd M Y',
                                                strtotime($row['booking_date'])
                                            )
                                        );
                                        ?>

                                        <br>

                                        <?php echo htmlspecialchars($row['booking_time']); ?>

                                    </div>

                                </td>


                                <!-- AMOUNT -->

                                <td>

                                    <div class="amount">

                                        RM
                                        <?php
                                        echo number_format(
                                            (float)$row['amount'],
                                            2
                                        );
                                        ?>

                                    </div>

                                </td>


                                <!-- PAYMENT METHOD -->

                                <td>

                                    <span class="payment-method">

                                        <?php
                                        echo htmlspecialchars($paymentMethod);
                                        ?>

                                    </span>

                                </td>


                                <!-- RECEIPT -->

                                <td>

                                    <?php if (!empty($row['receipt'])): ?>

                                        <a
                                            href="../user/uploads/receipt/<?php echo htmlspecialchars($row['receipt']); ?>"
                                            target="_blank"
                                            class="receipt-btn">

                                            <i class="fa-solid fa-file-image"></i>
                                            View Receipt

                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No Receipt
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- PAYMENT STATUS -->

                                <td>

                                    <span
                                        class="status-badge <?php echo $paymentStatusClass; ?>">

                                        <?php
                                        echo htmlspecialchars($paymentStatus);
                                        ?>

                                    </span>

                                </td>


                                <!-- BOOKING STATUS -->

                                <td>

                                    <span
                                        class="status-badge <?php echo $bookingStatusClass; ?>">

                                        <?php
                                        echo htmlspecialchars($bookingStatus);
                                        ?>

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="manage_booking.php"
                                        class="manage-link">

                                        Manage
                                        <i class="fa-solid fa-arrow-right ms-1"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr id="noDatabaseResult">

                            <td colspan="9" class="no-result">

                                <i class="fa-solid fa-receipt fa-2x mb-3"></i>

                                <br>

                                No payment records found.

                            </td>

                        </tr>

                    <?php endif; ?>


                    <!-- FILTER NO RESULT -->

                    <tr id="noFilterResult" style="display:none;">

                        <td colspan="9" class="no-result">

                            <i class="fa-solid fa-magnifying-glass fa-2x mb-3"></i>

                            <br>

                            No payment records match your filter.

                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script>

        const paymentSearch =
            document.getElementById('paymentSearch');

        const topSearch =
            document.getElementById('topSearch');

        const paymentStatusFilter =
            document.getElementById('paymentStatusFilter');

        const bookingStatusFilter =
            document.getElementById('bookingStatusFilter');

        const paymentMethodFilter =
            document.getElementById('paymentMethodFilter');

        const resetFilters =
            document.getElementById('resetFilters');

        const noFilterResult =
            document.getElementById('noFilterResult');


        function filterPayments() {

            const searchValue =
                paymentSearch.value.trim().toLowerCase();

            const paymentStatus =
                paymentStatusFilter.value;

            const bookingStatus =
                bookingStatusFilter.value;

            const paymentMethod =
                paymentMethodFilter.value;


            const rows =
                document.querySelectorAll('.payment-row');


            let visibleCount = 0;


            rows.forEach(function(row) {

                const rowSearch =
                    row.dataset.search.toLowerCase();

                const rowPaymentStatus =
                    row.dataset.paymentStatus;

                const rowBookingStatus =
                    row.dataset.bookingStatus;

                const rowPaymentMethod =
                    row.dataset.paymentMethod;


                const matchSearch =
                    searchValue === '' ||
                    rowSearch.includes(searchValue);


                const matchPaymentStatus =
                    paymentStatus === 'all' ||
                    rowPaymentStatus === paymentStatus;


                const matchBookingStatus =
                    bookingStatus === 'all' ||
                    rowBookingStatus === bookingStatus;


                const matchPaymentMethod =
                    paymentMethod === 'all' ||
                    rowPaymentMethod === paymentMethod;


                if (
                    matchSearch &&
                    matchPaymentStatus &&
                    matchBookingStatus &&
                    matchPaymentMethod
                ) {

                    row.style.display = '';

                    visibleCount++;

                } else {

                    row.style.display = 'none';

                }

            });


            if (visibleCount === 0 && rows.length > 0) {

                noFilterResult.style.display = '';

            } else {

                noFilterResult.style.display = 'none';

            }

        }


        /* SEARCH FILTER */

        paymentSearch.addEventListener(
            'input',
            filterPayments
        );


        /* TOPBAR SEARCH */

        topSearch.addEventListener(
            'input',
            function() {

                paymentSearch.value =
                    topSearch.value;

                filterPayments();

            }
        );


        /* DROPDOWN FILTERS */

        paymentStatusFilter.addEventListener(
            'change',
            filterPayments
        );


        bookingStatusFilter.addEventListener(
            'change',
            filterPayments
        );


        paymentMethodFilter.addEventListener(
            'change',
            filterPayments
        );


        /* RESET */

        resetFilters.addEventListener(
            'click',
            function() {

                paymentSearch.value = '';

                topSearch.value = '';

                paymentStatusFilter.value = 'all';

                bookingStatusFilter.value = 'all';

                paymentMethodFilter.value = 'all';

                filterPayments();

            }
        );

    </script>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>
```
