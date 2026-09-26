```php
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
            $stmt = $conn->prepare("
                SELECT id, court_id, booking_date, booking_time, status
                FROM bookings
                WHERE id=?
                LIMIT 1
                FOR UPDATE
            ");
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

                $payCheck = $conn->prepare("
                    SELECT payment_id
                    FROM payments
                    WHERE booking_id=?
                    LIMIT 1
                    FOR UPDATE
                ");
                $payCheck->bind_param("i", $id);
                $payCheck->execute();
                $paymentRow = $payCheck->get_result()->fetch_assoc();
                $payCheck->close();

                if (!$paymentRow) {
                    $conn->rollback();
                    header("Location: manage_booking.php?error=" . urlencode("Payment receipt is required before approving this booking."));
                    exit();
                }

                $chk = $conn->prepare("
                    SELECT id
                    FROM bookings
                    WHERE court_id=?
                    AND booking_date=?
                    AND booking_time=?
                    AND status='Approved'
                    AND id<>?
                    LIMIT 1
                    FOR UPDATE
                ");

                $chk->bind_param(
                    "issi",
                    $b['court_id'],
                    $b['booking_date'],
                    $b['booking_time'],
                    $id
                );

                $chk->execute();
                $conflict = $chk->get_result()->fetch_assoc();
                $chk->close();

                if ($conflict) {
                    $conn->rollback();
                    header("Location: manage_booking.php?error=" . urlencode("This court and time slot has already been booked."));
                    exit();
                }

                $upd = $conn->prepare("
                    UPDATE bookings
                    SET status='Approved', rejection_reason=NULL
                    WHERE id=?
                ");
                $upd->bind_param("i", $id);
                $upd->execute();
                $upd->close();

                $updPay = $conn->prepare("
                    UPDATE payments
                    SET status='Approved'
                    WHERE booking_id=?
                ");
                $updPay->bind_param("i", $id);
                $updPay->execute();
                $updPay->close();

            } else {

                $upd = $conn->prepare("
                    UPDATE bookings
                    SET status='Rejected', rejection_reason=?
                    WHERE id=?
                ");

                $upd->bind_param("si", $reason, $id);
                $upd->execute();
                $upd->close();

                $updPay = $conn->prepare("
                    UPDATE payments
                    SET status='Rejected'
                    WHERE booking_id=?
                ");

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


// ---------------------------------------------------------------------
// Filter & Pagination
// ---------------------------------------------------------------------

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

$whereSql = "";

if (in_array($filter, ['Pending', 'Approved', 'Rejected'], true)) {
    $whereSql = " WHERE bookings.status = '" .
        $conn->real_escape_string($filter) . "'";
}

$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;


// ---------------------------------------------------------------------
// Total Records
// ---------------------------------------------------------------------

$totalQuery = "
    SELECT COUNT(*) AS total
    FROM bookings
    JOIN courts
        ON bookings.court_id = courts.id
    JOIN users
        ON bookings.user_id = users.id
    LEFT JOIN payments
        ON payments.booking_id = bookings.id
    " . $whereSql;

$totalResult = $conn->query($totalQuery);

$totalRow = $totalResult->fetch_assoc();

$totalRecords = $totalRow['total'];

$totalPages = ceil($totalRecords / $limit);


// ---------------------------------------------------------------------
// Main Query
// ---------------------------------------------------------------------

$query = "
    SELECT
        bookings.*,
        courts.court_name,
        courts.price,
        users.name AS user_name,
        users.email,
        payments.receipt AS payment_receipt,
        payments.status AS payment_status,
        payments.amount AS payment_amount

    FROM bookings

    JOIN courts
        ON bookings.court_id = courts.id

    JOIN users
        ON bookings.user_id = users.id

    LEFT JOIN payments
        ON payments.booking_id = bookings.id

    " . $whereSql . "

    ORDER BY
        (bookings.status = 'Pending') DESC,
        bookings.id DESC

    LIMIT $limit
    OFFSET $offset
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="ms">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Booking Management - Badminton Kampung Panji</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link
href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
rel="stylesheet">


<style>

/* =========================================================
   ORIGINAL THEME
   ========================================================= */

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


/* SCROLLBAR */

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


/* SIDEBAR */

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

    transition: all .3s ease;

    box-shadow: 4px 0 10px rgba(0,0,0,.05);

}


.sidebar-brand {

    padding: 25px 20px;

    font-size: 1.25rem;

    font-weight: 800;

    color: #fff;

    display: flex;

    align-items: center;

    gap: 12px;

    border-bottom: 1px solid rgba(255,255,255,.08);

    text-decoration: none;

}


.sidebar-menu {

    padding: 20px 15px;

    overflow-y: auto;

    flex-grow: 1;

}


.menu-label {

    font-size: .75rem;

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

    font-size: .9rem;

    margin-bottom: 5px;

    transition: all .2s ease;

}


.sidebar-nav-link-content {

    display: flex;

    align-items: center;

    gap: 12px;

}


.sidebar-nav-link:hover,
.sidebar-nav-link.active {

    background-color: var(--sidebar-hover);

    color: #fff;

}


.sidebar-nav-link i {

    font-size: 1.1rem;

    width: 20px;

    text-align: center;

}


/* MAIN */

.main-content {

    margin-left: 280px;

    flex-grow: 1;

    display: flex;

    flex-direction: column;

    min-height: 100vh;

}


/* TOPBAR */

.topbar {

    height: 80px;

    background: #fff;
```
