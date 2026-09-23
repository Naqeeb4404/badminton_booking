<?php
session_start();
include __DIR__ . '/../config/db.php';

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// NOTE: This page used to insert bookings directly, which skipped every
// availability/overlap check and made double-booking possible. Booking is
// now always done through booking.php, which validates court status and
// slot conflicts (both on selection and again on submit). This form simply
// forwards the chosen date/time/court into that flow.
if(isset($_POST['court'])) {
    $court_id = $_POST['court'] ?? '';
    $booking_date = $_POST['date'] ?? '';
    $booking_time = $_POST['time'] ?? '';

    header("Location: ../booking.php?date=".urlencode($booking_date)."&time=".urlencode($booking_time)."&court_id=".urlencode($court_id));
    exit();
}
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Court - AceTime</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-outer: #1a120b;
            --bg-app: #1f1610;
            --card-dark: #2a1e17;
            --accent-orange: #d9622b;
            --text-dark: #f8f9fa;
            --text-muted: #b0a8a0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #382319, #120e0c);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 30px 15px;
        }

        .app-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--bg-app);
            border: 1px solid rgba(217, 98, 43, 0.2);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7);
            padding: 20px 40px 50px 40px;
        }

        .top-info-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0 20px 0;
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 30px;
        }

        .brand-logo-wrapper {
            display: flex;
            flex-direction: column;
        }

        .brand-logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-sub {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0 12px;
            transition: opacity 0.2s;
        }

        .nav-links a:hover {
            opacity: 0.6;
        }

        .btn-book-now {
            background-color: var(--card-dark);
            color: #f1d06b;
            border: 1px solid #d4af37;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-book-now:hover {
            background-color: #d4af37;
            color: #000;
        }

        .hero-banner {
            position: relative;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=1200&auto=format&fit=crop') center/cover no-repeat;
            border-radius: 24px;
            padding: 50px 40px;
            color: #ffffff;
            margin-bottom: 25px;
            border: 1px solid rgba(217, 98, 43, 0.3);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: -1px;
            max-width: 650px;
            margin-bottom: 15px;
        }

        .hero-subtitle {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 480px;
            margin-bottom: 20px;
        }

        .section-heading {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .step-label {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-number {
            background-color: #f1d06b;
            color: #000;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 800;
        }

        /* Tanggal / Date Horizontal Scroller */
        .date-scroller {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 25px;
            scrollbar-width: thin;
        }

        .date-card {
            background: var(--card-dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 12px 16px;
            text-align: center;
            min-width: 75px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #fff;
        }

        .date-card input[type="radio"] {
            display: none;
        }

        .date-card:hover {
            border-color: var(--accent-orange);
        }

        /* Masa Slot */
        .time-slots {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .time-slot-btn {
            background: var(--card-dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            color: #fff;
        }

        .time-slot-btn input[type="radio"] {
            display: none;
        }

        .time-slot-btn:hover {
            border-color: var(--accent-orange);
        }

        /* Custom Table Styling for Courts */
        .table-custom {
            background-color: var(--card-dark);
            color: #fff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-custom th {
            background-color: #140e0a;
            color: #f1d06b;
            font-weight: 700;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table-custom td {
            padding: 15px;
            vertical-align: middle;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .table-custom tr.unavailable {
            opacity: 0.5;
            filter: grayscale(80%);
        }

        .btn-book {
            background-color: var(--accent-orange);
            color: #ffffff;
            font-weight: 700;
            border-radius: 10px;
            padding: 8px 16px;
            border: none;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .btn-book:hover {
            background-color: #f1d06b;
            color: #000;
        }

        .alert-custom {
            border-radius: 16px;
            font-weight: 700;
            border: none;
            background-color: #1b382b;
            color: #51cf66;
        }
    </style>
</head>

<body>

    <div class="app-container" id="home-section">

        <!-- BAR ATAS (INFO LOKASI & TELEFON) -->
        <div class="top-info-bar">
            <div>
                <span class="me-3"><i class="fa-solid fa-phone me-1 text-warning"></i> +60 16 835 5533</span>
                <span><i class="fa-solid fa-location-dot me-1 text-danger"></i> SUNGAI BANGAT Warehouse, near Savemore Superstore</span>
            </div>
            <div>
                <span class="text-white fw-bold">BM</span>
            </div>
        </div>

        <!-- Header / Navigation -->
        <nav class="custom-navbar">
            <div class="brand-logo-wrapper">
                <a href="dashboard.php" class="brand-logo">
                    <i class="fa-solid fa-shuttlecock text-warning"></i> SPORTS CENTER
                </a>
                <span class="brand-sub">BADMINTON &bull; LABUAN F.T</span>
            </div>

            <div class="nav-links d-none d-md-block">
                <a href="feedback_report.php">Feedback</a>
                <a href="my_booking.php">My Booking</a>
                <a href="profile.php">Profile</a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="#booking-section" class="btn-book-now">Book Now</a>
                <a href="dashboard.php" class="btn btn-outline-light rounded-pill fw-bold btn-sm px-3 py-2">
                    <i class="fa-solid fa-gauge me-1"></i> Dashboard
                </a>
            </div>
        </nav>

        <!-- Banner Utama -->
        <div class="hero-banner">
            <h1 class="hero-title">BOOK YOUR COURT ANYTIME, ANYWHERE.</h1>
            <p class="hero-subtitle">See real-time availability, compare courts, and pay in seconds — all in one place.</p>
            <a href="#booking-section" class="btn btn-light text-dark fw-bold rounded-pill px-4 py-2 shadow-sm text-decoration-none">
                Find a Court <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
            </a>
        </div>

        <!-- Mesej Kejayaan (PHP) -->
        <?php
        if(isset($message)){
            echo "
            <div class='alert alert-custom p-3 mb-4 text-center'>
                <i class='fa-solid fa-circle-check me-2'></i> $message
            </div>
            ";
        }
        ?>

        <!-- Bahagian Tempahan Bergaya Gaya Rujukan (Booking Section) -->
        <div id="booking-section" class="mb-5">
            <h3 class="section-heading">Book a court</h3>
            <p class="text-muted mb-4">Pick a date, a start time and a court. Available and unavailable courts are displayed below.</p>

            <form method="POST">
                <!-- Langkah 1: Pilih Tarikh -->
                <div class="mb-4">
                    <div class="step-label">
                        <span class="step-number">1</span> Choose a date
                    </div>
                    <div class="date-scroller">
                        <?php
                        // Menjana senarai 14 hari dari hari ini secara dinamik
                        for ($i = 0; $i < 14; $i++) {
                            $date_val = date('Y-m-d', strtotime("+$i days"));
                            $day_name = ($i == 0) ? 'TODAY' : strtoupper(date('D', strtotime("+$i days")));
                            $day_num = date('d', strtotime("+$i days"));
                            $month_short = date('M', strtotime("+$i days"));
                            $checked = ($i == 0) ? 'checked' : '';
                            $active_class = ($i == 0) ? 'border-warning bg-secondary shadow-sm text-white' : '';
                        ?>
                        <label class="date-card <?php echo $active_class; ?>" onclick="updateDateCard(this)">
                            <input type="radio" name="date" value="<?php echo $date_val; ?>" <?php echo $checked; ?> required>
                            <div style="font-size: 0.7rem; font-weight: 700; color: #b0a8a0;"><?php echo $day_name; ?></div>
                            <div style="font-size: 1.2rem; font-weight: 800; color: #fff; margin: 2px 0;"><?php echo $day_num; ?></div>
                            <div style="font-size: 0.75rem; font-weight: 600; color: #b0a8a0;"><?php echo $month_short; ?></div>
                        </label>
                        <?php } ?>
                    </div>
                </div>

                <!-- Langkah 2: Pilih Masa Mula -->
                <div class="mb-4">
                    <div class="step-label">
                        <span class="step-number">2</span> Choose a start time
                    </div>
                    <div class="time-slots">
                        <?php 
                        $times = ['08:00', '10:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
                        foreach($times as $index => $t) {
                            $t_checked = ($index === 4) ? 'checked' : ''; // Default pilih 18:00
                            $t_active = ($index === 4) ? 'border-warning bg-secondary text-white shadow-sm' : '';
                        ?>
                        <label class="time-slot-btn <?php echo $t_active; ?>" onclick="updateTimeCard(this)">
                            <input type="radio" name="time" value="<?php echo $t; ?>:00" <?php echo $t_checked; ?> required>
                            <?php echo $t; ?>
                        </label>
                        <?php } ?>
                    </div>
                </div>

                <!-- Langkah 3: Pilih Gelanggang (Available & Not Available dalam bentuk Jadual) -->
                <div class="mb-3">
                    <div class="step-label">
                        <span class="step-number">3</span> Choose a court & Book
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nama Gelanggang</th>
                                <th style="width: 150px;" class="text-center">Status</th>
                                <th style="width: 200px;" class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Paparkan SEMUA gelanggang (Available & Not Available)
                            $result = mysqli_query($conn, "SELECT * FROM courts");

                            while($row = mysqli_fetch_assoc($result)){
                                $is_available = ($row['status'] == 'Available');
                                $row_class = $is_available ? '' : 'unavailable';
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td class="fw-bold">#<?php echo $row['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="../images/court<?php echo $row['id']; ?>.jpg" 
                                             alt="Court Image"
                                             style="width: 50px; height: 40px; object-fit: cover; border-radius: 6px;"
                                             onerror="this.src='https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=500&auto=format&fit=crop'">
                                        <span class="fw-bold fs-6"><?php echo $row['court_name']; ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($is_available) { ?>
                                        <span class="badge bg-success px-3 py-2 fw-bold">Available</span>
                                    <?php } else { ?>
                                        <span class="badge bg-danger px-3 py-2 fw-bold">Not Available</span>
                                    <?php } ?>
                                </td>
                                <td class="text-end">
                                    <?php if($is_available) { ?>
                                        <button type="submit" name="court" value="<?php echo $row['id']; ?>" class="btn btn-book w-100">
                                            Book Court
                                        </button>
                                    <?php } else { ?>
                                        <button class="btn btn-secondary w-100" disabled style="border-radius: 10px; padding: 8px; font-weight: 700; font-size: 0.9rem;">
                                            Unavailable
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>

    <!-- Bootstrap 5 JS & Script Interaktif Pilihan Tarikh/Masa -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDateCard(element) {
            document.querySelectorAll('.date-card').forEach(card => {
                card.classList.remove('border-warning', 'bg-secondary', 'shadow-sm', 'text-white');
            });
            element.classList.add('border-warning', 'bg-secondary', 'shadow-sm', 'text-white');
        }

        function updateTimeCard(element) {
            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.classList.remove('border-warning', 'bg-secondary', 'shadow-sm', 'text-white');
            });
            element.classList.add('border-warning', 'bg-secondary', 'shadow-sm', 'text-white');
        }
    </script>
</body>

</html>