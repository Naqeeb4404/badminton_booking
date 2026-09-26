<?php
session_start();

include __DIR__ . '/../config/db.php';

if (
    !isset($_SESSION['user']) ||
    $_SESSION['user']['role'] !== 'admin'
) {
    header('Location: ../auth/login.php');
    exit();
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Manage Payments - Badminton Kampung Panji</title>

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

    <!-- Shared Sidebar -->
    <link
        rel="stylesheet"
        href="sidebar.css?v=20260926"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(59, 130, 246, 0.08),
                    transparent 35%
                ),
                #f1f5f9;

            font-family:
                Arial,
                sans-serif;

            color: #0f172a;
        }

        /* =========================
           MAIN CONTENT
        ========================= */

        .main-content {
            min-height: 100vh;
        }

        .content-body {
            padding-bottom: 40px;
        }

        /* =========================
           TOPBAR
        ========================= */

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;

            background: rgba(255, 255, 255, 0.82);

            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);

            padding: 15px 25px;

            border-radius: 18px;

            box-shadow:
                0 8px 30px rgba(15, 23, 42, 0.06);

            margin-bottom: 25px;

            border: 1px solid rgba(148, 163, 184, 0.20);
        }

        /* =========================
           SEARCH
        ========================= */

        .search-form {
            position: relative;

            display: flex;
            align-items: center;

            width: 320px;
        }

        .search-form i {
            position: absolute;

            left: 15px;

            color: #64748b;

            font-size: 0.9rem;

            z-index: 2;
        }

        .search-input {
            width: 100%;

            padding:
                11px
                15px
                11px
                42px;

            border:
                1px solid
                #e2e8f0;

            border-radius: 13px;

            font-size: 0.9rem;

            background: rgba(248, 250, 252, 0.85);

            outline: none;

            transition: all 0.25s ease;
        }

        .search-input:focus {
            border-color: #3b82f6;

            background: #ffffff;

            box-shadow:
                0 0 0 4px
                rgba(59, 130, 246, 0.10);
        }

        /* =========================
           USER PILL
        ========================= */

        .user-pill {
            display: flex;

            align-items: center;

            gap: 10px;

            background:
                rgba(248, 250, 252, 0.9);

            padding:
                6px
                14px
                6px
                6px;

            border-radius: 50px;

            border:
                1px solid
                #e2e8f0;
        }

        .user-avatar {
            width: 34px;
            height: 34px;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #334155
                );

            color: #fff;

            display: flex;

            align-items: center;
            justify-content: center;

            font-weight: 700;

            font-size: 0.85rem;

            overflow: hidden;

            box-shadow:
                0 4px 12px
                rgba(15, 23, 42, 0.15);
        }

        /* =========================
           PAGE HEADER
        ========================= */

        .page-title {
            font-size: 28px;

            letter-spacing: -0.5px;
        }

        .page-description {
            color: #64748b;

            font-size: 14px;
        }

        /* =========================
           FILTER CARD
        ========================= */

        .filter-card {
            background:
                rgba(255, 255, 255, 0.78);

            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);

            border:
                1px solid
                rgba(148, 163, 184, 0.18);

            box-shadow:
                0 10px 35px
                rgba(15, 23, 42, 0.06);

            border-radius: 18px;

            padding: 20px;
        }

        .filter-title {
            font-size: 15px;

            font-weight: 700;

            /* Ditukar kepada warna putih */
            color: #ffffff;
        }

        .filter-label {
            font-size: 12px;

            font-weight: 700;

            /* Ditukar kepada warna putih */
            color: #ffffff;

            margin-bottom: 7px;
        }

        .filter-select {
            border:
                1px solid
                #e2e8f0;

            border-radius: 12px;

            padding: 10px 12px;

            font-size: 13px;

            background-color: #fff;

            transition: all 0.2s ease;
        }

        .filter-select:focus {
            border-color: #3b82f6;

            box-shadow:
                0 0 0 3px
                rgba(59, 130, 246, 0.10);
        }

        .reset-btn {
            border-radius: 12px;

            padding: 10px 15px;

            font-weight: 600;

            font-size: 13px;

            transition: all 0.2s ease;
        }

        .reset-btn:hover {
            transform: translateY(-1px);
        }

        /* =========================
           TABLE CARD
        ========================= */

        .payment-card {
            background:
                rgba(255, 255, 255, 0.82);

            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);

            border:
                1px solid
                rgba(148, 163, 184, 0.18);

            box-shadow:
                0 10px 35px
                rgba(15, 23, 42, 0.06);

            border-radius: 18px;

            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .payment-table {
            margin: 0;

            min-width: 1050px;
        }

        .payment-table thead th {
            background:
                rgba(248, 250, 252, 0.9);

            /* Ditukar kepada warna hitam */
            color: #000000;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 0.6px;

            font-weight: 700;

            padding: 16px;

            border-bottom:
                1px solid
                #e2e8f0;

            white-space: nowrap;
        }

        .payment-table tbody td {
            padding: 17px 16px;

            vertical-align: middle;

            border-bottom:
                1px solid
                #f1f5f9;

            font-size: 13px;
            
            /* Tetapkan warna teks asas dalam jadual kepada hitam */
            color: #000000;
        }

        .payment-table tbody tr {
            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .payment-table tbody tr:hover {
            background:
                rgba(59, 130, 246, 0.035);
        }

        .payment-id {
            font-weight: 800;

            color: #000000;
        }

        .customer-name {
            font-weight: 700;

            color: #000000;
        }

        .customer-email {
            font-size: 11px;

            /* Ditukar kepada hitam pekat/hampir hitam untuk kebolehbacaan */
            color: #1e293b;
        }

        .booking-court {
            font-weight: 700;

            color: #000000;
        }

        .booking-info {
            font-size: 11px;

            color: #1e293b;
        }

        .amount {
            font-weight: 800;

            color: #000000;

            white-space: nowrap;
        }

        /* =========================
           STATUS BADGES
        ========================= */

        .status-badge {
            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding:
                6px
                10px;

            border-radius: 50px;

            font-size: 10px;

            font-weight: 700;

            white-space: nowrap;
        }

        .status-approved {
            background: #dcfce7;

            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;

            color: #991b1b;
        }

        .status-pending {
            background: #fef3c7;

            color: #92400e;
        }

        /* =========================
           RECEIPT BUTTON
        ========================= */

        .receipt-btn {
            border-radius: 9px;

            font-size: 11px;

            font-weight: 600;

            padding:
                6px
                10px;

            transition: all 0.2s ease;
        }

        .receipt-btn:hover {
            transform: translateY(-1px);
        }

        /* =========================
           MANAGE LINK
        ========================= */

        .manage-link {
            display: inline-block;

            margin-top: 5px;

            color: #2563eb;

            text-decoration: none;

            font-size: 11px;

            font-weight: 600;
        }

        .manage-link:hover {
            color: #1d4ed8;

            text-decoration: underline;
        }

        /* =========================
           NO RESULTS
        ========================= */

        #noResults {
            padding: 50px 20px;

            text-align: center;

            color: #94a3b8;
        }

        #noResults i {
            font-size: 32px;

            margin-bottom: 10px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            .topbar {
                flex-direction: column;

                align-items: stretch;

                gap: 15px;

                padding: 15px;
            }

            .search-form {
                width: 100%;
            }

            .topbar > div:last-child {
                justify-content: space-between;
            }

            .page-title {
                font-size: 23px;
            }

            .filter-card {
                padding: 15px;
            }

        }

    </style>

    <!-- Keep shared admin shell stable -->
    <style>
        body.admin-page .sidebar,
        body.admin-page .main-content {
            transition: none !important;
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

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="topSearch"
                class="search-input"
                placeholder="Search payments..."
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

                <div
                    class="fw-bold fs-7 pe-2 text-dark"
                >
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
                Logout
            </a>

        </div>

    </header>


    <div class="content-body">

        <!-- =========================
             PAGE HEADER
        ========================= -->

        <div class="mb-4">

            <h2 class="fw-bold page-title mb-1">
                Payment Management
            </h2>

            <p class="page-description mb-0">
                View payment receipts linked to each badminton booking.
            </p>

        </div>


        <!-- =========================
             FILTER
        ========================= -->

        <div class="filter-card mb-4">

            <div class="d-flex align-items-center mb-3">

                <div
                    class="d-flex align-items-center justify-content-center me-2"
                    style="
                        width:34px;
                        height:34px;
                        border-radius:10px;
                        background:#eff6ff;
                        color:#2563eb;
                    "
                >
                    <i class="fa-solid fa-filter"></i>
                </div>

                <div class="filter-title">
                    Filter Payments
                </div>

            </div>


            <div class="row g-3 align-items-end">

                <!-- SEARCH -->

                <div class="col-lg-4 col-md-6">

                    <label class="filter-label">
                        Search Customer / Email / Court
                    </label>

                    <div class="position-relative">

                        <i
                            class="fa-solid fa-magnifying-glass position-absolute"
                            style="
                                left:14px;
                                top:50%;
                                transform:translateY(-50%);
                                color:#94a3b8;
                                z-index:2;
                            "
                        ></i>

                        <input
                            type="text"
                            id="paymentSearch"
                            class="form-control filter-select ps-5"
                            placeholder="Type to search..."
                            autocomplete="off"
                        >

                    </div>

                </div>


                <!-- PAYMENT STATUS -->

                <div class="col-lg-2 col-md-6">

                    <label class="filter-label">
                        Payment Status
                    </label>

                    <select
                        id="paymentStatusFilter"
                        class="form-select filter-select"
                    >

                        <option value="">
                            All Status
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

                    <select
                        id="bookingStatusFilter"
                        class="form-select filter-select"
                    >

                        <option value="">
                            All Status
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

                    <select
                        id="paymentMethodFilter"
                        class="form-select filter-select"
                    >

                        <option value="">
                            All Methods
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

                <div class="col-lg-2 col-md-6">

                    <button
                        type="button"
                        id="resetFilters"
                        class="btn btn-outline-secondary reset-btn w-100"
                    >
                        <i class="fa-solid fa-rotate-left me-1"></i>
                        Reset
                    </button>

                </div>

            </div>

        </div>


        <!-- =========================
             PAYMENT TABLE
        ========================= -->

        <div class="payment-card">

            <div class="table-wrapper">

                <table
                    class="table payment-table"
                    id="paymentTable"
                >

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

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($r = mysqli_fetch_assoc($result)): ?>

                        <tr
                            data-payment-status="<?= htmlspecialchars($r['status']) ?>"
                            data-booking-status="<?= htmlspecialchars($r['booking_status']) ?>"
                            data-payment-method="<?= htmlspecialchars($r['payment_method'] ?? '-') ?>"
                        >

                            <!-- PAYMENT -->

                            <td>

                                <span class="payment-id">
                                    #<?= (int)$r['payment_id'] ?>
                                </span>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer-name">
                                    <?= htmlspecialchars($r['name']) ?>
                                </div>

                                <div class="customer-email">
                                    <?= htmlspecialchars($r['email']) ?>
                                </div>

                            </td>


                            <!-- BOOKING -->

                            <td>

                                <div class="booking-court">
                                    <?= htmlspecialchars($r['court_name']) ?>
                                </div>

                                <div class="booking-info">

                                    <i class="fa-regular fa-calendar me-1"></i>

                                    <?= htmlspecialchars($r['booking_date']) ?>

                                    &nbsp;

                                    <i class="fa-regular fa-clock me-1"></i>

                                    <?= htmlspecialchars(
                                        substr($r['booking_time'], 0, 5)
                                    ) ?>

                                </div>

                            </td>


                            <!-- AMOUNT -->

                            <td>

                                <span class="amount">
                                    RM <?= number_format(
                                        (float)$r['amount'],
                                        2
                                    ) ?>
                                </span>

                            </td>


                            <!-- METHOD -->

                            <td>

                                <span class="fw-semibold text-dark">
                                    <?= htmlspecialchars(
                                        $r['payment_method'] ?? '-'
                                    ) ?>
                                </span>

                            </td>


                            <!-- RECEIPT -->

                            <td>

                                <?php if ($r['receipt']): ?>

                                    <a
                                        target="_blank"
                                        href="../user/uploads/receipt/<?= rawurlencode($r['receipt']) ?>"
                                        class="btn btn-sm btn-outline-primary receipt-btn"
                                    >
                                        <i class="fa-solid fa-file-image me-1"></i>
                                        View
                                    </a>

                                <?php else: ?>

                                    <span class="text-muted">
                                        No Receipt
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- PAYMENT STATUS -->

                            <td>

                                <?php
                                if ($r['status'] === 'Approved') {
                                    $statusClass = 'status-approved';
                                    $statusIcon = 'fa-circle-check';
                                } elseif ($r['status'] === 'Rejected') {
                                    $statusClass = 'status-rejected';
                                    $statusIcon = 'fa-circle-xmark';
                                } else {
                                    $statusClass = 'status-pending';
                                    $statusIcon = 'fa-clock';
                                }
                                ?>

                                <span
                                    class="status-badge <?= $statusClass ?>"
                                >

                                    <i class="fa-solid <?= $statusIcon ?>"></i>

                                    <?= htmlspecialchars($r['status']) ?>

                                </span>

                            </td>


                            <!-- BOOKING STATUS -->

                            <td>

                                <?php
                                if ($r['booking_status'] === 'Approved') {
                                    $bookingStatusClass = 'status-approved';
                                    $bookingStatusIcon = 'fa-circle-check';
                                } elseif ($r['booking_status'] === 'Rejected') {
                                    $bookingStatusClass = 'status-rejected';
                                    $bookingStatusIcon = 'fa-circle-xmark';
                                } else {
                                    $bookingStatusClass = 'status-pending';
                                    $bookingStatusIcon = 'fa-clock';
                                }
                                ?>

                                <span
                                    class="status-badge <?= $bookingStatusClass ?>"
                                >

                                    <i
                                        class="fa-solid <?= $bookingStatusIcon ?>"
                                    ></i>

                                    <?= htmlspecialchars(
                                        $r['booking_status']
                                    ) ?>

                                </span>

                                <br>

                                <a
                                    href="manage_booking.php"
                                    class="manage-link"
                                >
                                    Manage →
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>


            <!-- NO RESULTS -->

            <div
                id="noResults"
                style="display:none;"
            >

                <i class="fa-solid fa-filter-circle-xmark d-block"></i>

                <strong>
                    No payments found
                </strong>

                <p class="mb-0 mt-1">
                    Try changing your search or filter.
                </p>

            </div>

        </div>

    </div>

</div>


<!-- =========================
     FILTER JAVASCRIPT
========================= -->

<script>

const searchInput =
    document.getElementById('paymentSearch');

const topSearch =
    document.getElementById('topSearch');

const paymentStatus =
    document.getElementById('paymentStatusFilter');

const bookingStatus =
    document.getElementById('bookingStatusFilter');

const paymentMethod =
    document.getElementById('paymentMethodFilter');

const resetButton =
    document.getElementById('resetFilters');


function filterPayments() {

    const search =
        searchInput.value
        .toLowerCase()
        .trim();

    const selectedPayment =
        paymentStatus.value;

    const selectedBooking =
        bookingStatus.value;

    const selectedMethod =
        paymentMethod.value;


    const rows =
        document.querySelectorAll(
            '#paymentTable tbody tr'
        );


    let visibleCount = 0;


    rows.forEach(row => {

        const rowText =
            row.innerText.toLowerCase();

        const rowPayment =
            row.dataset.paymentStatus;

        const rowBooking =
            row.dataset.bookingStatus;

        const rowMethod =
            row.dataset.paymentMethod;


        const matchSearch =
            search === '' ||
            rowText.includes(search);


        const matchPayment =
            selectedPayment === '' ||
            rowPayment === selectedPayment;


        const matchBooking =
            selectedBooking === '' ||
            rowBooking === selectedBooking;


        const matchMethod =
            selectedMethod === '' ||
            rowMethod === selectedMethod;


        if (
            matchSearch &&
            matchPayment &&
            matchBooking &&
            matchMethod
        ) {

            row.style.display = '';

            visibleCount++;

        } else {

            row.style.display = 'none';

        }

    });


    document.getElementById('noResults').style.display =
        visibleCount === 0
            ? 'block'
            : 'none';

}


/* Search inside filter */

searchInput.addEventListener(
    'input',
    filterPayments
);


/* Topbar search also filters */

topSearch.addEventListener(
    'input',
    function () {

        searchInput.value =
            topSearch.value;

        filterPayments();

    }
);


/* Dropdown filters */

paymentStatus.addEventListener(
    'change',
    filterPayments
);

bookingStatus.addEventListener(
    'change',
    filterPayments
);

paymentMethod.addEventListener(
    'change',
    filterPayments
);


/* Reset */

resetButton.addEventListener(
    'click',
    function () {

        searchInput.value = '';

        topSearch.value = '';

        paymentStatus.value = '';

        bookingStatus.value = '';

        paymentMethod.value = '';

        filterPayments();

    }
);

</script>


</body>
</html>