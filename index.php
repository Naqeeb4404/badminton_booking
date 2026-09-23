<?php
session_start();
include __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Center - Badminton Kampung Panji</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-app: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --accent-gold: #f59e0b;
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
            color: #64748b;
            padding: 10px 40px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 40px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 42px;
            height: 42px;
            background: #0f172a;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f59e0b;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .brand-text span {
            display: block;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            color: #0f172a;
            line-height: 1.1;
        }

        .brand-text small {
            font-size: 0.65rem;
            color: #d97706;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #d97706;
        }

        .btn-book-now {
            border: 1.5px solid #0f172a;
            color: #0f172a;
            border-radius: 50px;
            padding: 8px 22px;
            font-weight: 700;
            font-size: 0.85rem;
            background: transparent;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-book-now:hover {
            background: #0f172a;
            color: #fff;
        }

        .hero-section {
            position: relative;
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.95)), url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?q=80&w=1400&auto=format&fit=crop') center/cover no-repeat;
            padding: 90px 40px;
            border-bottom: 1px solid #f1f5f9;
        }

        .open-badge {
            font-size: 0.72rem;
            font-weight: 800;
            background: #fef3c7;
            color: #b45309;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
            color: #0f172a;
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            max-width: 500px;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-check-availability {
            background-color: #0f172a;
            color: #fff;
            font-weight: 700;
            border: 1.5px solid #0f172a;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-check-availability:hover {
            background-color: #1e293b;
            color: #fff;
        }

        .btn-call-centre {
            background-color: transparent;
            color: #0f172a;
            font-weight: 700;
            border: 1.5px solid #cbd5e1;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-call-centre:hover {
            border-color: #0f172a;
            background: #f8fafc;
        }

        .content-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.8px;
            margin-bottom: 8px;
            color: #0f172a;
        }

        .section-subtitle-label {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: #d97706;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .step-card {
            background: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            padding: 35px 30px;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .step-card span {
            font-size: 0.75rem;
            font-weight: 800;
            color: #d97706;
            display: block;
            margin-bottom: 12px;
            letter-spacing: 1px;
        }

        .step-card h5 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: #0f172a;
        }

        .step-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.5;
        }

        .rate-card {
            background: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 20px;
            padding: 35px 30px;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .rate-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .rate-card span {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            display: block;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .rate-card h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .rate-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            background: #fafafa;
        }
    </style>
</head>

<body>

    <div class="top-announcement-bar d-none d-md-flex">
        <div><i class="fa-solid fa-phone me-1 text-warning"></i> CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div>
            <?php if(isset($_SESSION['user'])): ?>
                <a href="user/dashboard.php" class="text-decoration-none text-dark fw-bold">Dashboard</a>
            <?php else: ?>
                <a href="auth/login.php" class="text-decoration-none text-dark fw-bold">Login / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="custom-navbar">
        <a href="index.php" class="brand-container">
            <div class="brand-logo-icon">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div class="brand-text">
                <span>BADMINTON KAMPUNG PANJI</span>
                <small>Kuala Terengganu</small>
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
        <div style="max-width: 1100px; margin: 0 auto;">
            <span class="open-badge"><i class="fa-solid fa-circle-check me-1"></i> Open Daily • Dewan Kampung Panji</span>
            <h1 class="hero-title">Book a court.<br>Bring your game.</h1>
            <p class="hero-desc">Tempah gelanggang badminton anda secara online dengan mudah. Semak kekosongan masa secara langsung dan buat tempahan dalam masa seminit.</p>
            <div class="hero-buttons">
                <a href="booking.php" class="btn-check-availability">Check availability</a>
                <a href="tel:+601163519188" class="btn-call-centre"><i class="fa-solid fa-phone me-2"></i>Call Centre</a>
            </div>
        </div>
    </section>

    <div class="content-container">
        
        <!-- Live Availability Banner Section -->
        <div style="background:#ffffff; border:1.5px solid var(--border-color); border-radius:24px; padding:35px; margin-bottom:50px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="section-subtitle-label">Live Availability System</div>
                    <h3 style="font-weight: 800; font-size: 1.8rem; margin-bottom: 10px; color: #0f172a;">Pilih tarikh, masa & gelanggang pilihan anda</h3>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">Sistem kami memaparkan status ketersediaan gelanggang secara real-time bagi mengelakkan pertindihan tempahan.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="booking.php" class="btn btn-dark rounded-pill px-4 py-3 fw-bold w-100 w-lg-auto shadow-sm">Semak Gelanggang Sekarang &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Three Steps Section -->
        <div class="mb-5">
            <div class="section-subtitle-label">Prosedur Ringkas</div>
            <h3 class="section-title mb-4">Three steps to play</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <span>01</span>
                        <h5>Find your date and time</h5>
                        <p>Pilih tarikh pilihan anda dan semak slot masa kosong yang tersedia di sistem.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <span>02</span>
                        <h5>Make a booking</h5>
                        <p>Sahkan slot tempahan anda dan lakukan pembayaran secara online dengan pantas.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <span>03</span>
                        <h5>Turn up and play</h5>
                        <p>Hadir ke dewan dengan kasut bertapak getah (non-marking) dan mulakan perlawanan anda.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rates Section -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="section-subtitle-label">Harga & Fasiliti</div>
                    <h3 class="section-title m-0">Rates & Courts</h3>
                </div>
                <a href="rates.php" style="font-size: 0.88rem; font-weight: 700; color: #d97706; text-decoration: none;">Full rate card &rarr;</a>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="rate-card">
                        <span>Court Hire Rate</span>
                        <h2>RM 15</h2>
                        <p>per court, per hour — kadar standard setiap jam</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="rate-card">
                        <span>Available Courts</span>
                        <h2>Dewan Utama</h2>
                        <p>Gelanggang badminton berkualiti tinggi sedia ditempah</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <footer>
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Badminton Kampung Panji, Kuala Terengganu. Hak Cipta Terpelihara.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>