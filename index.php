<?php
session_start();
include __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Center Badminton - Next-Gen Court Booking</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --credix-bg: #090a0f;
            --credix-hero-card: rgba(14, 16, 23, 0.85);
            --credix-border: rgba(255, 255, 255, 0.12);
            --credix-accent: #6366f1;
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

        /* Latar Belakang Animasi Berterusan ( Bergerak Bebas ) */
        .animated-bg-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background: radial-gradient(circle at 20% 30%, rgba(99, 102, 241, 0.18) 0%, transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(168, 85, 247, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 1) 0%, #090a0f 100%);
            animation: bgPulse 12s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes bgPulse {
            0% { transform: scale(1) translate(0px, 0px); }
            50% { transform: scale(1.05) translate(-15px, -10px); }
            100% { transform: scale(1.1) translate(15px, 10px); }
        }

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

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 40px;
            border-bottom: 1px solid var(--credix-border);
            background: rgba(9, 10, 15, 0.85);
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
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-book-now:hover {
            background: var(--credix-accent);
            border-color: var(--credix-accent);
            color: #fff;
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.5);
            transform: translateY(-2px);
        }

        /* Hero Section dengan Kotak Besar Merangkumi Teks & Video Penuh */
        .hero-section {
            padding: 50px 0;
            position: relative;
        }

        .master-hero-card {
            background: var(--credix-hero-card);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--credix-border);
            border-radius: 32px;
            padding: 60px 50px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.8), 
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 2;
            transition: transform 0.2s ease-out;
        }

        .badge-pill {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #818cf8;
            letter-spacing: 0.5px;
        }

        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            color: var(--text-main);
            line-height: 1.1;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-primary-custom {
            background: var(--credix-accent);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary-custom:hover {
            background: #4f46e5;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.6);
        }

        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-main);
            border: 1px solid var(--credix-border);
            border-radius: 50px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Bingkai Video Penuh di Dalam Kotak Sebelah Kanan */
        .hero-video-wrapper {
            width: 100%;
            height: 100%;
            min-height: 340px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .hero-video-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            pointer-events: none;
        }

        .site-footer {
            background-color: rgba(19, 21, 31, 0.9);
            backdrop-filter: blur(15px);
            padding: 70px 40px 35px 40px;
            border-top: 1px solid var(--credix-border);
            margin-top: 80px;
            color: var(--text-muted);
            position: relative;
            z-index: 5;
        }

        .footer-container {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .footer-col h6 {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 15px;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-col ul li a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .footer-col ul li a:hover {
            color: var(--text-main);
        }

        .footer-bottom {
            max-width: 1100px;
            margin: 0 auto;
            border-top: 1px solid var(--credix-border);
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        @media (max-width: 991px) {
            .master-hero-card {
                padding: 40px 25px;
            }
            .hero-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.1rem;
            }
            .hero-video-wrapper {
                min-height: 240px;
                margin-top: 25px;
            }
            .footer-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            .footer-bottom {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Latar Belakang Animasi Berterusan -->
    <div class="animated-bg-layer"></div>

    <div class="top-announcement-bar d-none d-md-flex">
        <div>CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div>
            <a href="auth/login.php" class="text-decoration-none text-light fw-bold">Login / Register</a>
        </div>
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

        <?php if(isset($_SESSION['user'])): ?>
            <a href="booking.php" class="btn-book-now">Book Now</a>
        <?php else: ?>
            <a href="auth/login.php" class="btn-book-now">Book Now</a>
        <?php endif; ?>
    </nav>

    <!-- Hero Section dengan Kotak Besar Utama -->
    <section class="hero-section">
        <div class="container">
            <div class="master-hero-card" id="heroCard">
                <div class="row align-items-center g-4">
                    
                    <!-- Bahagian Kiri: Teks & Butang -->
                    <div class="col-lg-6">
                        <div class="badge-pill">
                            <span>NEXT-GEN COURT BOOKING PLATFORM</span>
                        </div>
                        <h1 class="hero-title">
                            Book a court.<br>Bring your game.
                        </h1>
                        <p class="hero-desc">
                            Sistem tempahan digital berprestasi tinggi. Semak ketersediaan gelanggang secara real-time dengan reka bentuk antara muka yang pantas dan lancar.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <?php if(isset($_SESSION['user'])): ?>
                                <a href="booking.php" class="btn btn-primary-custom">
                                    Check availability <i class="fa-solid fa-arrow-right ms-2"></i>
                                </a>
                            <?php else: ?>
                                <a href="auth/login.php" class="btn btn-primary-custom">
                                    Check availability <i class="fa-solid fa-arrow-right ms-2"></i>
                                </a>
                            <?php endif; ?>
                            <a href="tel:+601163519188" class="btn btn-secondary-custom">
                                <i class="fa-solid fa-phone me-2"></i> Call Centre
                            </a>
                        </div>
                    </div>

                    <!-- Sebelah Kanan: Video di dalam Kad yang sama -->
                    <div class="col-lg-5">
                        <div class="hero-video-wrapper">
                            <video autoplay muted loop playsinline>
                                <source src="videoiklan.mp4" type="video/mp4">
                                Pelayar anda tidak menyokong paparan video.
                            </video>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-col">
                <a href="index.php" class="brand-container mb-3 d-inline-flex">
                    <div class="brand-logo-icon">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="brand-text">
                        <span>SPORTS CENTER</span>
                        <small>Badminton • Kuala Terengganu</small>
                    </div>
                </a>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 10px;">
                    Dewan Kampung Panji, Kampung Panji<br>20050 Kuala Terengganu, Terengganu
                </p>
            </div>
            <div class="footer-col">
                <h6>Pautan Pantas</h6>
                <ul>
                    <li><a href="rates.php">Rates</a></li>
                    <li><a href="facility.php">Facility</a></li>
                    <li><a href="about.php">About</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h6>Sokongan</h6>
                <ul>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="rules.php">Rules</a></li>
                    <li><a href="location.php">Location</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; Sports Center • Badminton court booking</div>
            <div>Powered by aestivo.ai</div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS & Skrip Parallax Halus -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("scroll", function () {
            const scrollPosition = window.pageYOffset;
            const heroCard = document.getElementById("heroCard");

            if (window.innerWidth > 991) {
                // Kesan parallax halus pada keseluruhan kotak utama berbanding latar belakang
                heroCard.style.transform = `translateY(${scrollPosition * 0.04}px)`;
            }
        });
    </script>
</body>

</html>