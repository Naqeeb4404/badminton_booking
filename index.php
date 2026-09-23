<?php
session_start();
include __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badminton Kampung Panji</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --credix-bg: #090a0f;
            --credix-card: #13151f;
            --credix-border: rgba(255, 255, 255, 0.08);
            --credix-glow: rgba(99, 102, 241, 0.15);
            --credix-accent: #6366f1;
            --credix-accent-hover: #4f46e5;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--credix-bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Top Announcement Bar */
        .top-announcement-bar {
            font-size: 0.75rem;
            color: var(--text-muted);
            padding: 10px 40px;
            border-bottom: 1px solid var(--credix-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(19, 21, 31, 0.5);
            backdrop-filter: blur(10px);
        }

        /* Credix Glass Navbar */
        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 40px;
            border-bottom: 1px solid var(--credix-border);
            background: rgba(9, 10, 15, 0.8);
            backdrop-filter: blur(16px);
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
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .brand-text span {
            display: block;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            color: var(--text-main);
            line-height: 1.1;
        }

        .brand-text small {
            font-size: 0.65rem;
            color: #a855f7;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--text-main);
        }

        .btn-book-now {
            border: 1px solid var(--credix-border);
            color: var(--text-main);
            border-radius: 50px;
            padding: 8px 24px;
            font-weight: 700;
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.03);
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-book-now:hover {
            background: var(--credix-accent);
            border-color: var(--credix-accent);
            color: #fff;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        /* Hero Section ala Credix Landing */
        .hero-section {
            position: relative;
            padding: 100px 40px 80px;
            border-bottom: 1px solid var(--credix-border);
            background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.12) 0%, transparent 60%);
        }

        .open-badge {
            font-size: 0.72rem;
            font-weight: 800;
            background: rgba(99, 102, 241, 0.1);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 6px 14px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -2px;
            margin-bottom: 20px;
            color: var(--text-main);
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 540px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-check-availability {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: #fff;
            font-weight: 700;
            border: none;
            padding: 13px 30px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-check-availability:hover {
            opacity: 0.9;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-call-centre {
            background-color: var(--credix-card);
            color: var(--text-main);
            font-weight: 700;
            border: 1px solid var(--credix-border);
            padding: 13px 30px;
            border-radius: 50px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-call-centre:hover {
            border-color: var(--text-muted);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .content-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 70px 20px;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .section-subtitle-label {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #818cf8;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        /* Credix Feature & Step Cards */
        .step-card {
            background: var(--credix-card);
            border: 1px solid var(--credix-border);
            border-radius: 24px;
            padding: 40px 30px;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #6366f1, transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .step-card:hover {
            transform: translateY(-6px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5), 0 0 20px rgba(99, 102, 241, 0.1);
        }

        .step-card:hover::before {
            opacity: 1;
        }

        .step-card span {
            font-size: 0.75rem;
            font-weight: 800;
            color: #818cf8;
            display: block;
            margin-bottom: 15px;
            letter-spacing: 1.2px;
        }

        .step-card h5 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-main);
        }

        .step-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.6;
        }

        /* Credix Rate Cards */
        .rate-card {
            background: var(--credix-card);
            border: 1px solid var(--credix-border);
            border-radius: 24px;
            padding: 40px 30px;
            height: 100%;
            transition: all 0.3s ease;
        }

        .rate-card:hover {
            transform: translateY(-6px);
            border-color: rgba(168, 85, 247, 0.4);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .rate-card span {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            display: block;
            margin-bottom: 12px;
            letter-spacing: 1.2px;
        }

        .rate-card h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-main);
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .rate-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--credix-border);
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            background: var(--credix-card);
        }
    </style>
</head>

<body>

    <div class="top-announcement-bar d-none d-md-flex">
        <div><i class="fa-solid fa-bolt me-1 text-indigo"></i> CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div>
            <?php if(isset($_SESSION['user'])): ?>
                <a href="user/dashboard.php" class="text-decoration-none text-light fw-bold">Dashboard</a>
            <?php else: ?>
                <a href="auth/login.php" class="text-decoration-none text-light fw-bold">Login / Register</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="custom-navbar">
        <a href="index.php" class="brand-container">
            <div class="brand-logo-icon">
                <i class="fa-solid fa-feather"></i>
            </div>
            <div class="brand-text">
                <span>BADMINTON</span>
                <small>Kampung Panji</small>
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
            <span class="open-badge"><i class="fa-solid fa-sparkles me-1"></i> Next-Gen Court Booking Platform</span>
            <h1 class="hero-title">Book a court.<br>Bring your game.</h1>
            <p class="hero-desc">Sistem tempahan digital berprestasi tinggi. Semak ketersediaan gelanggang secara real-time dengan reka bentuk antara muka yang pantas dan lancar.</p>
            <div class="hero-buttons">
                <a href="booking.php" class="btn-check-availability">Check availability &rarr;</a>
                <a href="tel:+601163519188" class="btn-call-centre"><i class="fa-solid fa-phone me-2"></i>Call Centre</a>
            </div>
        </div>
    </section>

    <div class="content-container">
        
        <!-- Dashboard Live Banner -->
        <div style="background: var(--credix-card); border: 1px solid var(--credix-border); border-radius: 28px; padding: 40px; margin-bottom: 60px; position: relative; overflow: hidden;">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="section-subtitle-label">Instant Sync Engine</div>
                    <h3 style="font-weight: 800; font-size: 1.85rem; margin-bottom: 12px; color: var(--text-main);">Pilih tarikh, masa & gelanggang pilihan anda</h3>
                    <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">Sistem pintar mengawal status ketersediaan secara langsung tanpa konflik jadual.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="booking.php" class="btn btn-light rounded-pill px-4 py-3 fw-bold w-100 w-lg-auto shadow-sm" style="background:#fff; color:#000;">Check availability →</a>
                </div>
            </div>
        </div>

        <!-- Three Steps Section -->
        <div class="mb-5">
            <div class="section-subtitle-label">Workflow</div>
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
                    <div class="section-subtitle-label">Pricing Matrix</div>
                    <h3 class="section-title m-0">Rates & Courts</h3>
                </div>
                <a href="rates.php" style="font-size: 0.88rem; font-weight: 700; color: #818cf8; text-decoration: none;">Full rate card &rarr;</a>
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
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Badminton Kampung Panji, Kuala Terengganu. Credix UI Edition. Hak Cipta Terpelihara.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>