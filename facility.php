<?php

session_start();

include __DIR__ . '/config/db.php';

?>



<!DOCTYPE html>

<html lang="ms">



<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Facility - Sports Center Badminton Labuan F.T</title>



    <!-- Bootstrap 5 CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"

        rel="stylesheet">



    <style>

        :root {

            --credix-bg: #090a0f;

            --credix-card: #13151f;

            --credix-border: rgba(255, 255, 255, 0.08);

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

            padding: 9px 40px;

            min-height: 70px;

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



        .nav-links a:hover,

        .nav-links a.active {

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



        .content-container {

            max-width: 1100px;

            margin: 0 auto;

            padding: 80px 20px 100px 20px;

        }



        .facility-title {

            font-size: 3.2rem;

            font-weight: 800;

            letter-spacing: -1.5px;

            margin-bottom: 15px;

            color: var(--text-main);

        }



        .facility-desc {

            color: var(--text-muted);

            font-size: 1.05rem;

            max-width: 650px;

            margin-bottom: 50px;

            line-height: 1.6;

        }



        /* Interactive Scroll Animation Classes */

        .reveal-on-scroll {

            opacity: 0;

            transform: translateY(35px);

            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);

            will-change: opacity, transform;

        }



        .reveal-on-scroll.is-visible {

            opacity: 1;

            transform: translateY(0);

        }



        .delay-1 {

            transition-delay: 0.1s;

        }



        .delay-2 {

            transition-delay: 0.2s;

        }



        .delay-3 {

            transition-delay: 0.3s;

        }



        .facility-card {

            background: var(--credix-card);

            border: 1px solid var(--credix-border);

            border-radius: 24px;

            padding: 35px 30px;

            height: 100%;

            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);

            position: relative;

            overflow: hidden;

        }



        .facility-card::before {

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



        .facility-card:hover {

            transform: translateY(-8px);

            border-color: rgba(99, 102, 241, 0.4);

            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(99, 102, 241, 0.15);

        }



        .facility-card:hover::before {

            opacity: 1;

        }



        .facility-icon {

            width: 54px;

            height: 54px;

            background: rgba(99, 102, 241, 0.1);

            color: #818cf8;

            border-radius: 16px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 1.3rem;

            margin-bottom: 24px;

            border: 1px solid rgba(99, 102, 241, 0.2);

        }



        .facility-card h4 {

            font-size: 1.2rem;

            font-weight: 800;

            margin-bottom: 12px;

            color: var(--text-main);

        }



        .facility-card p {

            font-size: 0.92rem;

            color: var(--text-muted);

            margin: 0;

            line-height: 1.6;

        }



        .site-footer {

            background-color: var(--credix-card);

            padding: 70px 40px 35px 40px;

            border-top: 1px solid var(--credix-border);

            margin-top: 100px;

            color: var(--text-muted);

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



        @media (max-width: 768px) {

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



            .footer-container {

                grid-template-columns: 1fr;

                gap: 25px;

            }



            .footer-bottom {

                flex-direction: column;

                gap: 10px;

            }



            .facility-title {

                font-size: 2.5rem;

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



    <div class="top-announcement-bar d-none d-md-flex">

        <div>CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>

        <div>

            <?php if (isset($_SESSION['user'])): ?>

                <a href="user/dashboard.php" class="text-decoration-none text-light fw-bold">Dashboard</a>

            <?php else: ?>

                <a href="auth/login.php" class="text-decoration-none text-light fw-bold">Login / Register</a>

            <?php endif; ?>

        </div>

    </div>



    <nav class="custom-navbar">

        <a href="index.php" class="brand-container">

            <div class="brand-logo-icon">

                <img src="logo-badminton.png" alt="Badminton Kampung Panji">

            </div>

            <div class="brand-text">

                <span>BADMINTON</span>

                <small>Kampung Panji</small>

            </div>

        </a>



        <div class="nav-links d-none d-lg-flex">

            <a href="index.php">Home</a>

            <a href="rates.php">Rates</a>

            <a href="facility.php" class="active">Facility</a>

            <a href="about.php">About</a>

            <a href="faq.php">FAQ</a>

            <a href="rules.php">Rules</a>

            <a href="location.php">Location</a>

        </div>



        <a href="auth/login.php" class="btn-book-now">Book Now</a>

    </nav>



    <div class="content-container">



        <div class="reveal-on-scroll">

            <h1 class="facility-title">Facility & Amenities</h1>

            <p class="facility-desc">Our indoor sports center at Sungai Bangat is designed for comfort, optimal airflow,

                and quality gameplay. Explore what we provide for players.</p>

        </div>



        <div class="row g-4">

            <div class="col-md-4 reveal-on-scroll delay-1">

                <div class="facility-card">

                    <div class="facility-icon">

                        <i class="fa-solid fa-table-tennis-paddle-ball"></i>

                    </div>

                    <h4>6 Professional Courts</h4>

                    <p>Equipped with high-grade synthetic surfaces designed to absorb impact and reduce joint stress

                        during intense rallies.</p>

                </div>

            </div>

            <div class="col-md-4 reveal-on-scroll delay-2">

                <div class="facility-card">

                    <div class="facility-icon">

                        <i class="fa-solid fa-wind"></i>

                    </div>

                    <h4>Optimal Ventilation</h4>

                    <p>Designed with high warehouse ceilings and side airflow management to maintain a cool indoor

                        playing environment.</p>

                </div>

            </div>

            <div class="col-md-4 reveal-on-scroll delay-3">

                <div class="facility-card">

                    <div class="facility-icon">

                        <i class="fa-solid fa-shoe-prints"></i>

                    </div>

                    <h4>Shoe Change Area</h4>

                    <p>Dedicated shoe rack zone outside the courts to ensure non-marking court floors remain clean and

                        slip-free.</p>

                </div>

            </div>

            <div class="col-md-4 reveal-on-scroll delay-1">

                <div class="facility-card">

                    <div class="facility-icon">

                        <i class="fa-solid fa-square-parking"></i>

                    </div>

                    <h4>Ample Parking Space</h4>

                    <p>Spacious parking grounds located right outside the warehouse for easy and secure vehicle

                        placement.</p>

                </div>

            </div>

            <div class="col-md-4 reveal-on-scroll delay-2">

                <div class="facility-card">

                    <div class="facility-icon">

                        <i class="fa-solid fa-restroom"></i>

                    </div>

                    <h4>Restroom Facilities</h4>

                    <p>Clean and well-maintained washrooms available on-site for players before and after matches.</p>

                </div>

            </div>

            <div class="col-md-4 reveal-on-scroll delay-3">

                <div class="facility-card">

                    <div class="facility-icon">

                        <i class="fa-solid fa-store"></i>

                    </div>

                    <h4>Convenient Location</h4>

                    <p>Strategically situated at Sungai Bangat, right near Savemore Superstore for easy accessibility.

                    </p>

                </div>

            </div>

        </div>



    </div>



    <footer class="site-footer">

        <div class="footer-container">

            <div class="footer-col">

                <a href="index.php" class="brand-container mb-3 d-inline-flex">

                    <div class="brand-logo-icon">

                <img src="logo-badminton.png" alt="Badminton Kampung Panji">

            </div>

                    <div class="brand-text">

                        <span>BADMINTON</span>

                        <small>Kampung Panji</small>

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

                    <li><a href="rules.php">Rules</a></li>

                </ul>

            </div>

            <div class="footer-col">

                <h6>Sokongan</h6>

                <ul>

                    <li><a href="faq.php">FAQ</a></li>

                    <li><a href="location.php">Location</a></li>

                    <li><a href="auth/login.php">Book Now</a></li>

                </ul>

            </div>

        </div>

        <div>
            &copy; <?php echo date('Y'); ?>
            Badminton Kampung Panji
        </div>

        <div>
            Badminton Court Booking System
        </div>

    </footer>



    <!-- Bootstrap 5 JS & Scroll Observer Script -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            const observerOptions = {

                root: null,

                rootMargin: '0px 0px -50px 0px',

                threshold: 0.15

            };



            const observer = new IntersectionObserver((entries, observer) => {

                entries.forEach(entry => {

                    if (entry.isIntersecting) {

                        entry.target.classList.add('is-visible');

                    } else {

                        entry.target.classList.remove('is-visible');

                    }

                });

            }, observerOptions);



            document.querySelectorAll('.reveal-on-scroll').forEach(element => {

                observer.observe(element);

            });

        });

    </script>

</body>



</html>