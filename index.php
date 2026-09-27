<?php
session_start();
include __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badminton Kampung Panji - Court Booking</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
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

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--credix-bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* =========================
           BACKGROUND
        ========================= */

        .animated-bg-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background:
                radial-gradient(
                    circle at 20% 30%,
                    rgba(99, 102, 241, 0.18) 0%,
                    transparent 40%
                ),
                radial-gradient(
                    circle at 80% 70%,
                    rgba(168, 85, 247, 0.15) 0%,
                    transparent 40%
                ),
                radial-gradient(
                    circle at 50% 50%,
                    rgba(15, 23, 42, 1) 0%,
                    #090a0f 100%
                );
            animation: bgPulse 12s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes bgPulse {
            0% {
                transform: scale(1) translate(0, 0);
            }

            50% {
                transform: scale(1.05) translate(-15px, -10px);
            }

            100% {
                transform: scale(1.1) translate(15px, 10px);
            }
        }

        /* =========================
           TOP BAR
        ========================= */

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
            -webkit-backdrop-filter: blur(10px);
        }

        /* =========================
           NAVBAR
        ========================= */

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 40px;
            min-height: 70px;
            border-bottom: 1px solid var(--credix-border);
            background: rgba(9, 10, 15, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* =========================
           BRAND
        ========================= */

        .brand-container {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }

        /* =========================
           LOGO - SMALLER
        ========================= */

        .brand-logo-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: transparent;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .brand-logo-icon img {
            width: 46px;
            height: 46px;
            display: block;
            object-fit: contain;
            object-position: center;
            background: transparent;
            border-radius: 9px;
            border: none;
            margin: 0;
            padding: 0;
            filter: none;
        }

        /* =========================
           BRAND TEXT
        ========================= */

        .brand-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            min-width: 0;
        }

        .brand-text span {
            display: block;
            color: #f8fafc;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .brand-text small {
            display: block;
            color: #a855f7;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            line-height: 1;
            margin-top: 6px;
            padding: 0;
            text-transform: uppercase;
        }

        /* =========================
           NAVIGATION
        ========================= */

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
            transition: color 0.2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--text-main);
        }

        /* =========================
           BOOK NOW
        ========================= */

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
            white-space: nowrap;
        }

        .btn-book-now:hover {
            background: var(--credix-accent);
            border-color: var(--credix-accent);
            color: #fff;
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.5);
            transform: translateY(-2px);
        }

        /* =========================
           HERO
        ========================= */

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
            box-shadow:
                0 40px 80px rgba(0, 0, 0, 0.8),
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

        /* =========================
           BUTTONS
        ========================= */

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

        /* =========================
           VIDEO
        ========================= */

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

        /* =========================
           FOOTER
        ========================= */

        .site-footer {
            background-color: rgba(19, 21, 31, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 70px 40px 35px;
            border-top: 1px solid var(--credix-border);
            margin-top: 80px;
            color: var(--text-muted);
            position: relative;
            z-index: 5;
        }

        .footer-container {
            max-width: 1100px;
            margin: 0 auto 50px;
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr;
            gap: 40px;
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

        /* =========================
           TABLET
        ========================= */

        @media (max-width: 991px) {
            .master-hero-card {
                padding: 40px 25px;
            }

            .hero-title {
                font-size: 2.5rem;
            }
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 768px) {
            .custom-navbar {
                padding: 8px 18px;
                min-height: 62px;
            }

            .top-announcement-bar {
                padding: 10px 20px;
            }

            .brand-container {
                gap: 9px;
            }

            .brand-logo-icon {
                width: 42px;
                height: 42px;
                border-radius: 9px;
            }

            .brand-logo-icon img {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                object-fit: contain;
                filter: none;
            }

            .brand-text span {
                font-size: 0.88rem;
            }

            .brand-text small {
                font-size: 0.58rem;
                letter-spacing: 1.2px;
                margin-top: 5px;
            }

            .btn-book-now {
                padding: 7px 15px;
                font-size: 0.75rem;
            }

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
    
        /* ===== LOCKED PUBLIC NAVBAR / GLASS DOCK ===== */
        html{
            overflow-y:scroll;
            scrollbar-gutter:stable;
        }
        .top-announcement-bar{
            box-sizing:border-box !important;
            min-height:39px !important;
            height:39px !important;
            padding:0 40px !important;
            display:flex;
            align-items:center;
        }
        .custom-navbar{
            box-sizing:border-box !important;
            position:sticky !important;
            top:0 !important;
            z-index:1000 !important;
            width:100% !important;
            height:70px !important;
            min-height:70px !important;
            padding:0 40px !important;
            display:flex !important;
            align-items:center !important;
            justify-content:space-between !important;
            background:rgba(9,10,15,.88) !important;
            border-bottom:1px solid rgba(255,255,255,.08) !important;
            -webkit-backdrop-filter:blur(18px) !important;
            backdrop-filter:blur(18px) !important;
        }
        .custom-navbar .brand-container{
            width:230px !important;
            min-width:230px !important;
            height:70px !important;
            display:flex !important;
            align-items:center !important;
            gap:12px !important;
            margin:0 !important;
            padding:0 !important;
            flex-shrink:0 !important;
        }
        .custom-navbar .brand-logo-icon{
            width:48px !important;
            height:48px !important;
            min-width:48px !important;
            margin:0 !important;
            padding:0 !important;
        }
        .custom-navbar .brand-logo-icon img{
            width:46px !important;
            height:46px !important;
            max-width:46px !important;
            max-height:46px !important;
            object-fit:contain !important;
        }
        .custom-navbar .brand-text{
            margin:0 !important;
            padding:0 !important;
        }
        .custom-navbar .brand-text span{
            font-size:.95rem !important;
            line-height:1.1 !important;
            font-weight:800 !important;
            margin:0 !important;
        }
        .custom-navbar .brand-text small{
            font-size:.65rem !important;
            line-height:1.1 !important;
            font-weight:700 !important;
            margin:4px 0 0 !important;
        }

        /* Dock sentiasa tepat di tengah skrin, bukan ikut lebar kiri/kanan */
        .custom-navbar .nav-links{
            box-sizing:border-box !important;
            position:absolute !important;
            left:50% !important;
            top:50% !important;
            transform:translate(-50%,-50%) !important;
            width:590px !important;
            height:48px !important;
            padding:5px !important;
            margin:0 !important;
            display:grid !important;
            grid-template-columns:repeat(7,1fr) !important;
            gap:3px !important;
            align-items:center !important;
            border:1px solid rgba(255,255,255,.10) !important;
            border-radius:999px !important;
            background:rgba(255,255,255,.055) !important;
            -webkit-backdrop-filter:blur(18px) saturate(150%) !important;
            backdrop-filter:blur(18px) saturate(150%) !important;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 10px 30px rgba(0,0,0,.20) !important;
        }
        .custom-navbar .nav-links a{
            box-sizing:border-box !important;
            width:100% !important;
            height:36px !important;
            min-width:0 !important;
            padding:0 6px !important;
            margin:0 !important;
            display:flex !important;
            align-items:center !important;
            justify-content:center !important;
            border:1px solid transparent !important;
            border-radius:999px !important;
            color:#94a3b8 !important;
            text-decoration:none !important;
            font-size:.82rem !important;
            line-height:1 !important;
            font-weight:700 !important;
            white-space:nowrap !important;
            transform:none !important;
            transition:background .2s ease,color .2s ease,border-color .2s ease,box-shadow .2s ease !important;
        }
        .custom-navbar .nav-links a:hover{
            color:#fff !important;
            background:rgba(255,255,255,.07) !important;
            border-color:rgba(255,255,255,.08) !important;
            box-shadow:0 0 16px rgba(99,102,241,.16) !important;
            transform:none !important;
        }
        .custom-navbar .nav-links a.active{
            color:#fff !important;
            background:linear-gradient(135deg,rgba(99,102,241,.24),rgba(168,85,247,.18)) !important;
            border-color:rgba(129,140,248,.34) !important;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.10),0 0 18px rgba(99,102,241,.20) !important;
            transform:none !important;
        }
        .custom-navbar .btn-book-now{
            box-sizing:border-box !important;
            width:116px !important;
            min-width:116px !important;
            height:38px !important;
            padding:0 !important;
            margin:0 !important;
            display:flex !important;
            align-items:center !important;
            justify-content:center !important;
            flex-shrink:0 !important;
            border:1px solid rgba(255,255,255,.12) !important;
            border-radius:999px !important;
            background:rgba(255,255,255,.04) !important;
            color:#f8fafc !important;
            font-size:.85rem !important;
            font-weight:700 !important;
            line-height:1 !important;
            text-decoration:none !important;
            white-space:nowrap !important;
            transform:none !important;
        }
        .custom-navbar .btn-book-now:hover{
            background:#6366f1 !important;
            border-color:#6366f1 !important;
            color:#fff !important;
            box-shadow:0 0 22px rgba(99,102,241,.42) !important;
            transform:none !important;
        }
        @media(max-width:991.98px){
            .custom-navbar{padding:0 20px !important;}
            .custom-navbar .nav-links{display:none !important;}
            .custom-navbar .brand-container{width:auto !important;min-width:0 !important;}
        }

    </style>
</head>

<body>

<div class="animated-bg-layer"></div>

<!-- TOP BAR -->
<div class="top-announcement-bar d-none d-md-flex">

    <div>
        CALL +60 11 6351 9188
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Dewan Kampung Panji, Kuala Terengganu
    </div>

    <div>
        <?php if (isset($_SESSION['user'])): ?>

            <a href="user/dashboard.php"
               class="text-decoration-none text-light fw-bold">
                Dashboard
            </a>

        <?php else: ?>

            <a href="auth/login.php"
               class="text-decoration-none text-light fw-bold">
                Login / Register
            </a>

        <?php endif; ?>
    </div>

</div>

<!-- NAVBAR -->
<nav class="custom-navbar">

    <!-- BRAND -->
    <a href="index.php" class="brand-container">

        <div class="brand-logo-icon">
            <img
                src="logo-badminton.png"
                alt="Badminton Kampung Panji"
            >
        </div>

        <div class="brand-text">
            <span>BADMINTON</span>
            <small>KAMPUNG PANJI</small>
        </div>

    </a>

    <!-- NAVIGATION -->
    <div class="nav-links d-none d-lg-flex">

        <a href="index.php" class="active">Home</a>
        <a href="rates.php">Rates</a>
        <a href="facility.php">Facility</a>
        <a href="about.php">About</a>
        <a href="faq.php">FAQ</a>
        <a href="rules.php">Rules</a>
        <a href="location.php">Location</a>

    </div>

    <!-- BOOK NOW -->
    <a href="auth/login.php" class="btn-book-now">Book Now</a>

</nav>

<!-- HERO -->
<section class="hero-section">

    <div class="container">

        <div class="master-hero-card" id="heroCard">

            <div class="row align-items-center g-4 justify-content-between">

                <!-- LEFT -->
                <div class="col-lg-6">

                    <div class="badge-pill">
                        <span>
                            NEXT-GEN COURT BOOKING PLATFORM
                        </span>
                    </div>

                    <h1 class="hero-title">
                        Book a court.<br>
                        Bring your game.
                    </h1>

                    <p class="hero-desc">
                        Sistem tempahan digital berprestasi tinggi.
                        Semak ketersediaan gelanggang secara real-time
                        dengan reka bentuk antara muka yang pantas dan lancar.
                    </p>

                    <div class="d-flex flex-wrap gap-3">

                        <?php if (isset($_SESSION['user'])): ?>

                            <a href="booking.php"
                               class="btn btn-primary-custom">

                                Check availability

                                <i class="fa-solid fa-arrow-right ms-2"></i>

                            </a>

                        <?php else: ?>

                            <a href="auth/login.php"
                               class="btn btn-primary-custom">

                                Check availability

                                <i class="fa-solid fa-arrow-right ms-2"></i>

                            </a>

                        <?php endif; ?>

                        <a href="tel:+601163519188"
                           class="btn btn-secondary-custom">

                            <i class="fa-solid fa-phone me-2"></i>

                            Call Centre

                        </a>

                    </div>

                </div>

                <!-- VIDEO -->
                <div class="col-lg-5">

                    <div class="hero-video-wrapper">

                        <video autoplay muted loop playsinline>

                            <source
                                src="videoiklan.mp4"
                                type="video/mp4"
                            >

                            Pelayar anda tidak menyokong paparan video.

                        </video>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- FOOTER -->
<footer class="site-footer">

    <div class="footer-container">

        <!-- FOOTER BRAND -->
        <div class="footer-col">

            <a href="index.php"
               class="brand-container mb-3 d-inline-flex">

                <div class="brand-logo-icon">

                    <img
                        src="logo-badminton.png"
                        alt="Badminton Kampung Panji"
                    >

                </div>

                <div class="brand-text">

                    <span>BADMINTON</span>
                    <small>KAMPUNG PANJI</small>

                </div>

            </a>

            <p style="
                font-size: 0.85rem;
                color: var(--text-muted);
                margin-top: 10px;
                line-height: 1.7;
            ">
                Dewan Kampung Panji, Kampung Panji
                <br>
                20050 Kuala Terengganu, Terengganu
            </p>

        </div>

        <!-- QUICK LINKS -->
        <div class="footer-col">

            <h6>Pautan Pantas</h6>

            <ul>
                <li>
                    <a href="rates.php">Rates</a>
                </li>

                <li>
                    <a href="facility.php">Facility</a>
                </li>

                <li>
                    <a href="about.php">About</a>
                </li>
            </ul>

        </div>

        <!-- SUPPORT -->
        <div class="footer-col">

            <h6>Sokongan</h6>

            <ul>
                <li>
                    <a href="faq.php">FAQ</a>
                </li>

                <li>
                    <a href="rules.php">Rules</a>
                </li>

                <li>
                    <a href="location.php">Location</a>
                </li>
            </ul>

        </div>

    </div>

    <div class="footer-bottom">

        <div>
            &copy; <?php echo date('Y'); ?>
            Badminton Kampung Panji
        </div>

        <div>
            Badminton Court Booking System
        </div>

    </div>

</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("scroll", function () {

    const scrollPosition = window.pageYOffset;
    const heroCard = document.getElementById("heroCard");

    if (window.innerWidth > 991 && heroCard) {
        heroCard.style.transform =
            `translateY(${scrollPosition * 0.04}px)`;
    }

});
</script>

</body>
</html>