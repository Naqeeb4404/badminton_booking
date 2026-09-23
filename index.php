<?php
session_start();
include __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Center - Badminton Labuan F.T</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-app: #ffffff;
            --text-dark: #111111;
            --text-muted: #666666;
            --accent-gold: #c59b27;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-app);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
        }

        .top-announcement-bar {
            font-size: 0.75rem;
            color: #777;
            padding: 8px 40px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 40px;
            border-bottom: 1px solid #f1f5f9;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 40px;
            height: 40px;
            background: #f59e0b;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
        }

        .brand-text span {
            display: block;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 1px;
            color: #111;
            line-height: 1.1;
        }

        .brand-text small {
            font-size: 0.65rem;
            color: #b45309;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 22px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #d97706;
            font-weight: 700;
        }

        .btn-book-now {
            border: 1.5px solid #d1d5db;
            color: #111;
            border-radius: 50px;
            padding: 6px 20px;
            font-weight: 700;
            font-size: 0.85rem;
            background: transparent;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-book-now:hover {
            border-color: #111;
            background: #111;
            color: #fff;
        }

        .hero-section {
            position: relative;
            background: linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.92)), url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=1400&auto=format&fit=crop') center/cover no-repeat;
            padding: 80px 40px;
            border-bottom: 1px solid #f1f5f9;
        }

        .open-badge {
            font-size: 0.72rem;
            font-weight: 700;
            background: #fef3c7;
            color: #b45309;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1px;
            margin-bottom: 15px;
            color: #111;
        }

        .hero-desc {
            color: #4b5563;
            font-size: 1rem;
            max-width: 480px;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
        }

        .btn-check-availability {
            background-color: #fef3c7;
            color: #b45309;
            font-weight: 700;
            border: 1.5px solid #fcd34d;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-check-availability:hover {
            background-color: #fde68a;
        }

        .btn-call-centre {
            background-color: transparent;
            color: #374151;
            font-weight: 700;
            border: 1.5px solid #d1d5db;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-call-centre:hover {
            border-color: #374151;
        }

        .content-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }

        .section-subtitle-label {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #b45309;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .legend-container {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: #64748b;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .legend-box {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            border: 1px solid #cbd5e1;
        }

        .date-slider-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .date-card {
            min-width: 65px;
            height: 80px;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 5px;
        }

        .date-card span {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
        }

        .date-card h4 {
            font-size: 1.2rem;
            font-weight: 800;
            margin: 2px 0;
            color: #1e293b;
        }

        .date-card.active, .date-card:hover {
            border-color: #d97706;
            background-color: #fffbeb;
        }

        .date-card.active h4, .date-card.active span {
            color: #b45309;
        }

        .schedule-grid-wrapper {
            overflow-x: auto;
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .time-header-row {
            display: grid;
            grid-template-columns: 100px repeat(15, 1fr);
            gap: 6px;
            margin-bottom: 12px;
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 700;
            text-align: center;
        }

        .court-row {
            display: grid;
            grid-template-columns: 100px repeat(15, 1fr);
            gap: 6px;
            margin-bottom: 8px;
            align-items: center;
        }

        .court-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
        }

        .slot-cell {
            height: 42px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 6px;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s;
        }

        .slot-cell:hover {
            border-color: #d97706;
            background: #fffbeb;
        }

        .slot-cell.booked {
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            cursor: not-allowed;
        }

        .slot-cell.selected {
            background: #fef3c7 !important;
            border: 1.5px solid #d97706 !important;
        }

        .selection-box {
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            padding: 20px 25px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .btn-continue {
            border: none;
            background: #d97706;
            color: #fff;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-continue:hover {
            background: #b45309;
        }

        .step-card {
            background: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            height: 100%;
        }

        .step-card span {
            font-size: 0.75rem;
            font-weight: 800;
            color: #b45309;
            display: block;
            margin-bottom: 8px;
        }

        .step-card h5 {
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .step-card p {
            font-size: 0.88rem;
            color: #64748b;
            margin: 0;
        }

        .rate-card {
            background: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            height: 100%;
        }

        .rate-card span {
            font-size: 0.72rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            display: block;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .rate-card h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .rate-card p {
            font-size: 0.88rem;
            color: #64748b;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="top-announcement-bar d-none d-md-flex">
        <div>CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div><a href="auth/login.php" class="text-decoration-none text-dark fw-bold">Login / Register</a></div>
    </div>

    <nav class="custom-navbar">
        <a href="index.php" class="brand-container">
            <div class="brand-logo-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div class="brand-text">
                <span>SPORTS CENTER</span>
                <small>Badminton • Kuala Terengganu</small>
            </div>
        </a>

        <div class="nav-links d-none d-lg-flex">
            <a href="index.php">Home</a>
            <a href="rates.php">Rates</a>
            <a href="facility.php">Facility</a>
            <a href="about.php">About</a>
            <a href="faq.php">FAQ</a>
            <a href="rules.php">Rules</a>
            <a href="location.php">Location</a>
        </div>
        <a href="booking.php" class="btn-book-now">Book Now</a>
    </nav>

    <section class="hero-section">
        <div style="max-width: 1100px; margin: 0 auto;">
            <span class="open-badge">Open daily • Sungai Bangat</span>
            <h1 class="hero-title">Book a court.<br>Bring your game.</h1>
            <p class="hero-desc">Courts by the hour, seven days a week. See what's free tonight and book it online in under a minute.</p>
            <div class="hero-buttons">
                <a href="booking.php" class="btn-check-availability">Check availability</a>
                <a href="tel:+60168355533" class="btn-call-centre">Call the centre</a>
            </div>
        </div>
    </section>

    <div class="content-container" id="availability-section">
        
        <div class="mb-4">
            <div class="section-subtitle-label">Live Availability</div>
            <h2 class="section-title">Pick a court and an hour</h2>
        </div>

        <div class="legend-container">
            <div class="legend-item"><div class="legend-box" style="background: #f8fafc;"></div> Available</div>
            <div class="legend-item"><div class="legend-box" style="background: #fef3c7; border-color: #d97706;"></div> Selected</div>
            <div class="legend-item"><div class="legend-box" style="background: #fee2e2;"></div> Booked</div>
        </div>

        <!-- Tarikh Slider -->
        <div class="date-slider-container" id="dateSlider">
            <?php
            for ($i = 0; $i < 10; $i++) {
                $currentDate = date('Y-m-d', strtotime("+$i days"));
                $dayName = ($i == 0) ? 'TODAY' : strtoupper(date('D', strtotime($currentDate)));
                $dayNum = date('d', strtotime($currentDate));
                $monthName = date('M', strtotime($currentDate));
                $activeClass = ($i == 0) ? 'active' : '';
                
                echo '
                <div class="date-card ' . $activeClass . '" data-date="' . $currentDate . '">
                    <span>' . $dayName . '</span>
                    <h4>' . $dayNum . '</h4>
                    <span>' . $monthName . '</span>
                </div>';
            }
            ?>
        </div>

        <!-- Pautan ke sistem booking sebenar -->
        <div class="live-booking-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:28px;margin-bottom:45px;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#d97706;margin-bottom:6px;">Live Availability</div>
                    <h3 style="font-weight:800;margin-bottom:7px;">Choose your date, time and court</h3>
                    <p style="color:#64748b;margin:0;">Sistem akan semak status gelanggang dan booking sebenar sebelum anda teruskan.</p>
                </div>
                <a href="booking.php" class="btn btn-dark rounded-pill px-4 py-3 fw-bold">Check availability →</a>
            </div>
        </div>

        <!-- Tiga Langkah -->
        <div class="mb-5">
            <h3 class="section-title mb-4">Three steps</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <span>01</span>
                        <h5>Find your date and time</h5>
                        <p>The grid above shows every court, hour by hour, seven days out.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <span>02</span>
                        <h5>Make a booking</h5>
                        <p>Confirm your slots and pay online. You get the confirmation immediately.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <span>03</span>
                        <h5>Turn up and play</h5>
                        <p>Bring non-marking court shoes. Change at the shoe rack outside the court.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bahagian Kadar Harga -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title m-0">Rates</h3>
                <a href="rates.php" style="font-size: 0.85rem; font-weight: 700; color: #d97706; text-decoration: none;">Full rate card &rarr;</a>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="rate-card">
                        <span>Court Hire</span>
                        <h2>RM 15</h2>
                        <p>per court, per hour — every hour, every season</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rate-card">
                        <span>Courts</span>
                        <h2>6</h2>
                        <p>bookable individually, by the hour</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS & Skrip Interaktif -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Availability and booking selection are handled by booking.php.
    </script>
</body>

</html>