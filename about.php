<?php
session_start();
include __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Sports Center Badminton Labuan F.T</title>

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

        .content-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 80px 20px 100px 20px;
        }

        .about-title {
            font-size: 3.2rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            margin-bottom: 15px;
            color: var(--text-main);
        }

        .about-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            max-width: 700px;
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

        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }

        .info-card {
            background: var(--credix-card);
            border: 1px solid var(--credix-border);
            border-radius: 24px;
            padding: 35px 30px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
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

        .info-card:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(99, 102, 241, 0.15);
        }

        .info-card:hover::before {
            opacity: 1;
        }

        .info-card span {
            font-size: 0.7rem;
            font-weight: 800;
            color: #818cf8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-bottom: 12px;
        }

        .info-card h4 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--text-main);
        }

        .info-card p {
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
            .footer-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            .footer-bottom {
                flex-direction: column;
                gap: 10px;
            }
            .about-title {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="top-announcement-bar d-none d-md-flex">
        <div>CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
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
            <a href="about.php" class="active">About</a>
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

    <div class="content-container">
        
        <div class="reveal-on-scroll">
            <h1 class="about-title">About Us</h1>
            <p class="about-desc">Pusat Sukan Badminton Labuan F.T di Sungai Bangat membawakan kemudahan gelanggang badminton bertaraf moden untuk komuniti tempatan beriadah, berlatih, dan mengadakan pertandingan.</p>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-md-4 reveal-on-scroll delay-1">
                <div class="info-card">
                    <span>Misi Kami</span>
                    <h4>Komuniti Sihat & Aktif</h4>
                    <p>Menyediakan ruang sukan yang selesa, mudah diakses, dan sistematik untuk semua pencinta sukan badminton di Wilayah Persekutuan Labuan.</p>
                </div>
            </div>
            <div class="col-md-4 reveal-on-scroll delay-2">
                <div class="info-card">
                    <span>Lokasi Strategik</span>
                    <h4>Sungai Bangat</h4>
                    <p>Terletak di kawasan gudang Sungai Bangat, berdekatan dengan Savemore Superstore. Mempunyai ruang letak kereta yang luas dan selesa untuk pengunjung.</p>
                </div>
            </div>
            <div class="col-md-4 reveal-on-scroll delay-3">
                <div class="info-card">
                    <span>Kemudahan</span>
                    <h4>6 Gelanggang Berkualiti</h4>
                    <p>Dilengkapi dengan sejumlah 6 gelanggang badminton profesional lengkap sistem tempahan dalam talian yang pantas dan mudah digunakan.</p>
                </div>
            </div>
        </div>

    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-col">
                <a href="index.php" class="brand-container mb-3 d-inline-flex">
                    <div class="brand-logo-icon">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="brand-text">
                        <span>SPORTS CENTER</span>
                        <small>Badminton • Labuan F.T</small>
                    </div>
                </a>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 10px;">
                    Sungai Bangat Warehouse<br>near Savemore Superstore
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
                    <li><a href="location.php">Location</a></li>
                    <li><a href="booking.php">Book Now</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; Sports Center • Badminton court booking</div>
            <div>Powered by aestivo.ai</div>
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