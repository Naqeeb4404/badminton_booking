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
            --bg-app: #f8fafc;
            --surface-white: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --accent-gold: #f59e0b;
            --accent-amber-dark: #b45309;
            --border-color: #e2e8f0;
            --shadow-subtle: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            --shadow-hover: 0 10px 25px -5px rgba(245, 158, 11, 0.15);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-app);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .top-announcement-bar {
            font-size: 0.78rem;
            color: #475569;
            background: #ffffff;
            padding: 10px 48px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 48px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .brand-text span {
            display: block;
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -0.3px;
            color: var(--text-dark);
            line-height: 1.1;
        }

        .brand-text small {
            font-size: 0.68rem;
            color: var(--accent-amber-dark);
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 28px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--accent-amber-dark);
        }

        .btn-book-now {
            border: 2px solid var(--text-dark);
            color: var(--text-dark);
            border-radius: 50px;
            padding: 8px 24px;
            font-weight: 700;
            font-size: 0.88rem;
            background: transparent;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn-book-now:hover {
            border-color: var(--text-dark);
            background: var(--text-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        .hero-section {
            position: relative;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9)), url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=1400&auto=format&fit=crop') center/cover no-repeat;
            padding: 100px 48px;
            border-bottom: 1px solid var(--border-color);
        }

        .open-badge {
            font-size: 0.75rem;
            font-weight: 700;
            background: #fef3c7;
            color: var(--accent-amber-dark);
            padding: 6px 14px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: inset 0 0 0 1px #fcd34d;
        }

        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 520px;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
        }

        .btn-check-availability {
            background-color: var(--accent-gold);
            color: #fff;
            font-weight: 700;
            border: 1.5px solid var(--accent-gold);
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
        }

        .btn-check-availability:hover {
            background-color: #d97706;
            border-color: #d97706;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-call-centre {
            background-color: var(--surface-white);
            color: var(--text-dark);
            font-weight: 700;
            border: 1.5px solid var(--border-color);
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: var(--shadow-subtle);
        }

        .btn-call-centre:hover {
            border-color: var(--text-dark);
            background-color: #f1f5f9;
            color: var(--text-dark);
        }

        .content-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 60px 24px;
        }

        .section-title {
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: -0.8px;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .section-subtitle-label {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: var(--accent-amber-dark);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .legend-container {
            display: flex;
            gap: 20px;
            font-size: 0.85rem;
            color: var(--text-muted);
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-box {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .date-slider-container {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 15px;
            margin-bottom: 35px;
            scrollbar-width: thin;
        }

        .date-card {
            min-width: 75px;
            height: 90px;
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            background: var(--surface-white);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 8px;
            box-shadow: var(--shadow-subtle);
        }

        .date-card span {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .date-card h4 {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 3px 0;
            color: var(--text-dark);
        }

        .date-card.active, .date-card:hover {
            border-color: var(--accent-gold);
            background-color: #fffbeb;
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .date-card.active h4, .date-card.active span {
            color: var(--accent-amber-dark);
        }

        .schedule-grid-wrapper {
            overflow-x: auto;
            background: var(--surface-white);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 35px;
            box-shadow: var(--shadow-subtle);
        }

        .time-header-row {
            display: grid;
            grid-template-columns: 100px repeat(15, 1fr);
            gap: 8px;
            margin-bottom: 15px;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 700;
            text-align: center;
        }

        .court-row {
            display: grid;
            grid-template-columns: 100px repeat(15, 1fr);
            gap: 8px;
            margin-bottom: 10px;
            align-items: center;
        }

        .court-label {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .slot-cell {
            height: 46px;
            border: 1.5px dashed var(--border-color);
            border-radius: 8px;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .slot-cell:hover {
            border-color: var(--accent-gold);
            background: #fffbeb;
        }

        .slot-cell.booked {
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            cursor: not-allowed;
        }

        .slot-cell.selected {
            background: #fef3c7 !important;
            border: 1.5px solid var(--accent-gold) !important;
        }

        .selection-box {
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            padding: 24px 30px;
            background: var(--surface-white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 45px;
            box-shadow: var(--shadow-subtle);
        }

        .btn-continue {
            border: none;
            background: var(--accent-gold);
            color: #fff;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .btn-continue:hover {
            background: var(--accent-amber-dark);
            transform: translateY(-1px);
        }

        .step-card {
            background: var(--surface-white);
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            padding: 35px 30px;
            height: 100%;
            box-shadow: var(--shadow-subtle);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: #fde68a;
        }

        .step-card span {
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--accent-amber-dark);
            display: block;
            margin-bottom: 12px;
            background: #fef3c7;
            width: fit-content;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .step-card h5 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-dark);
        }

        .step-card p {
            font-size: 0.92rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.6;
        }

        .rate-card {
            background: var(--surface-white);
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            padding: 35px 30px;
            height: 100%;
            box-shadow: var(--shadow-subtle);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .rate-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: #fde68a;
        }

        .rate-card span {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            display: block;
            margin-bottom: 10px;
            letter-spacing: 0.8px;
        }

        .rate-card h2 {
            font-size: 2.6rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: var(--text-dark);
            letter-spacing: -1px;
        }

        .rate-card p {
            font-size: 0.92rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.6rem;
            }
            .top-announcement-bar, .custom-navbar {
                padding-left: 20px;
                padding-right: 20px;
            }
            .hero-section {
                padding: 60px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="top-announcement-bar d-none d-md-flex">
        <div><i class="fa-solid fa-phone me-2 text-warning"></i> CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div><a href="auth/login.php" class="text-decoration-none text-dark fw-bold">Login / Register <i class="fa-solid fa-arrow-right-to-bracket ms-1 text-warning"></i></a></div>
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
            <a href="index.php" class="active">Home</a>
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
        <div style="max-width: 1140px; margin: 0 auto;">
            <span class="open-badge"><i class="fa-solid fa-circle-check me-1"></i> Open daily • Sungai Bangat</span>
            <h1 class="hero-title">Book a court.<br>Bring your game.</h1>
            <p class="hero-desc">Courts by the hour, seven days a week. See what's free tonight and book it online in under a minute.</p>
            <div class="hero-buttons">
                <a href="booking.php" class="btn-check-availability">Check availability <i class="fa-solid fa-arrow-right ms-2"></i></a>
                <a href="tel:+60168355533" class="btn-call-centre"><i class="fa-solid fa-phone-volume me-2 text-secondary"></i> Call the centre</a>
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
            <div class="legend-item"><div class="legend-box" style="background: #fee2e2; border-color: #fca5a5;"></div> Booked</div>
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
        <div class="live-booking-card" style="background:var(--surface-white);border:1px solid var(--border-color);border-radius:20px;padding:32px;margin-bottom:45px;box-shadow:var(--shadow-subtle);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#d97706;margin-bottom:6px;"><i class="fa-solid fa-bolt me-1"></i> Live Availability</div>
                    <h3 style="font-weight:800;margin-bottom:7px;color:var(--text-dark);">Choose your date, time and court</h3>
                    <p style="color:var(--text-muted);margin:0;">Sistem akan semak status gelanggang dan booking sebenar sebelum anda teruskan.</p>
                </div>
                <a href="booking.php" class="btn btn-dark rounded-pill px-4 py-3 fw-bold shadow-sm">Check availability &rarr;</a>
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
                <a href="rates.php" style="font-size: 0.88rem; font-weight: 700; color: #d97706; text-decoration: none;">Full rate card &rarr;</a>
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