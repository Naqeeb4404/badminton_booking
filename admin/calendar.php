<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

$user = $_SESSION['user'];

// Ambil bulan & tahun daripada parameter URL, tukar kepada integer untuk elak ralat key
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Kawalan jika bulan melebihi 12 atau kurang dari 1 (untuk tukar tahun automatik)
if ($month > 12) {
    $month = 1;
    $year++;
} elseif ($month < 1) {
    $month = 12;
    $year--;
}

// Format bulan & tahun dengan betul (contoh: 01, 02)
$monthFormatted = str_pad($month, 2, "0", STR_PAD_LEFT);

// Jumlah hari dalam bulan terpilih (Menggunakan date('t') untuk mengelakkan ralat cal_days_in_month)
$daysInMonth = intval(date('t', strtotime("$year-$monthFormatted-01")));

// Ambil booking mengikut bulan dan tahun yang dipilih
$query = "SELECT * FROM bookings WHERE MONTH(booking_date) = $month AND YEAR(booking_date) = $year";
$result = mysqli_query($conn, $query);

// Simpan dalam array dengan format tarikh yang seragam
$bookings = [];
if ($result) {
    while($row = mysqli_fetch_assoc($result)){
        $cleanDate = date('Y-m-d', strtotime($row['booking_date']));
        $bookings[$cleanDate][] = $row;
    }
}

// Nama bulan dalam Bahasa Melayu (kunci integer)
$monthNames = [
    1 => 'Januari', 
    2 => 'Februari', 
    3 => 'Mac', 
    4 => 'April', 
    5 => 'Mei', 
    6 => 'Jun', 
    7 => 'Julai', 
    8 => 'Ogos', 
    9 => 'September', 
    10 => 'Oktober', 
    11 => 'November', 
    12 => 'Disember'
];

// Semakan keselamatan bagi nama bulan dengan memastikan integer digunakan
$currentMonthKey = intval($month);
$currentMonthName = isset($monthNames[$currentMonthKey]) ? $monthNames[$currentMonthKey] : 'Bulan';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Calendar - Badminton Kampung Panji</title>

    <!-- Bootstrap 5 CSS & FontAwesome -->
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
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* SIDEBAR STYLING */
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

        /* MAIN CONTENT AREA */
        .main-content {
            margin-left: 280px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* TOPBAR STYLING */
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
            box-shadow: none !important;
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

        .month-nav-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .month-nav-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .calendar-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .calendar-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-align: center;
            padding: 14px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-weight: 700;
        }

        .calendar-table td {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            height: 125px;
            width: 14.28%;
            vertical-align: top;
            padding: 10px;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .calendar-table td:hover {
            border-color: #94a3b8;
            background-color: #f8fafc;
        }

        .calendar-date {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 8px;
            display: inline-block;
            background: #f1f5f9;
            width: 26px;
            height: 26px;
            text-align: center;
            line-height: 26px;
            border-radius: 50%;
        }

        .booking-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #64748b;
            border-radius: 6px;
            margin-top: 4px;
            padding: 5px 8px;
            font-size: 0.72rem;
            color: #334155;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .booking-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            border-left-color: #0f172a;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .sidebar-brand span, .sidebar .menu-label, .sidebar .sidebar-nav-link span, .sidebar .badge { display: none; }
            .main-content { margin-left: 70px; }
            .topbar { padding: 0 20px; }
            .search-form { display: none; }
            .calendar-table td { height: 100px; padding: 6px; font-size: 0.65rem; }
            .calendar-date { width: 22px; height: 22px; line-height: 22px; font-size: 0.75rem; }
        }
    </style>
    <link rel="stylesheet" href="sidebar.css?v=20260926">

    <!-- Stable shared admin shell -->
    <style>body.admin-page .sidebar, body.admin-page .main-content { transition: none !important; }</style>
</head>

<body class="admin-page">

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="main-content">
        
        <!-- TOPBAR -->
        <header class="topbar">
            <form action="" method="GET" class="search-form">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" class="form-control search-input" placeholder="Taip untuk cari..." autocomplete="off">
            </form>

            <div class="d-flex align-items-center gap-3">
                <div class="user-pill">
                    <div class="user-avatar" style="<?php echo $admin_photo_style; ?>"><?php echo $admin_photo === '' ? htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') : ''; ?></div>
                    <div class="fw-bold fs-7 pe-2"><?php echo htmlspecialchars($current_admin['name'] ?? $user['name'] ?? 'Admin'); ?></div>
                </div>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Log Keluar
                </a>
            </div>
        </header>

        <!-- CONTENT BODY -->
        <div class="content-body">
            
            <div class="card shadow">
                <div class="card-body p-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">📅 Booking Calendar</h2>
                            <p class="text-muted fs-7 mb-0">Paparan kalendar bulanan untuk rekod tempahan gelanggang.</p>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3">
                            <?php
                                $prevMonth = $month - 1;
                                $prevYear = $year;
                                if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

                                $nextMonth = $month + 1;
                                $nextYear = $year;
                                if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                            ?>
                            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="month-nav-btn" title="Bulan Sebelumnya">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>

                            <div class="badge bg-light text-dark border px-3 py-2 fw-bold fs-6">
                                <?php echo $currentMonthName . " " . $year; ?>
                            </div>

                            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="month-nav-btn" title="Bulan Seterusnya">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <hr class="text-muted opacity-25 mb-4">

                    <div class="table-responsive">
                        <table class="calendar-table">
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                            <tr>
                            <?php
                            $firstDay = date('w', strtotime("$year-$monthFormatted-01"));

                            for($i = 0; $i < $firstDay; $i++){
                                echo "<td style='background:#f8fafc; border-color:transparent;'></td>";
                            }

                            for($day = 1; $day <= $daysInMonth; $day++){
                                $dayFormatted = str_pad($day, 2, "0", STR_PAD_LEFT);
                                $date = "$year-$monthFormatted-$dayFormatted";

                                echo "<td>";
                                echo "<div class='calendar-date'>$day</div>";

                                if(isset($bookings[$date])){
                                    foreach($bookings[$date] as $b){
                                        echo "<div class='booking-item'>";
                                        echo "Court: ".htmlspecialchars($b['court_id'])."<br>";
                                        echo "Time: ".htmlspecialchars($b['booking_time'])."<br>";
                                        echo "Status: ".htmlspecialchars($b['status']);
                                        echo "</div>";
                                    }
                                }

                                echo "</td>";

                                if(($day + $firstDay) % 7 == 0){
                                    echo "</tr><tr>";
                                }
                            }
                            ?>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>