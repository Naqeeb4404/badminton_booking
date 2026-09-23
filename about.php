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

        .nav-links a.active, .nav-links a:hover {
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

        .content-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 20px 80px 20px;
        }

        .about-title {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 15px;
        }

        .about-desc {
            color: #4b5563;
            font-size: 1.05rem;
            max-width: 700px;
            margin-bottom: 45px;
            line-height: 1.6;
        }

        .info-card {
            background: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            height: 100%;
        }

        .info-card span {
            font-size: 0.7rem;
            font-weight: 800;
            color: #b45309;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 10px;
        }

        .info-card h4 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .info-card p {
            font-size: 0.88rem;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        .site-footer {
            background-color: #f1f5f9;
            padding: 60px 40px 30px 40px;
            border-top: 1px solid #e2e8f0;
            margin-top: 80px;
            color: #475569;
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
            color: #1e293b;
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
            card-color: #64748b;
            color: #64748b;
            text-decoration: none;
            font-size: 0.88rem;
            transition: color 0.2s;
        }

        .footer-col ul li a:hover {
            color: #d97706;
        }

        .footer-bottom {
            max-width: 1100px;
            margin: 0 auto;
            border-top: 1px solid #cbd5e1;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            color: #64748b;
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
            <a href="about.php" class="active">About</a>
            <a href="faq.php">FAQ</a>
            <a href="rules.php">Rules</a>
            <a href="location.php">Location</a>
        </div>

        <a href="auth/login.php" class="btn-book-now">Book Now</a>
    </nav>

    <div class="content-container">
        
        <h1 class="about-title">About Us</h1>
        <p class="about-desc">Pusat Sukan Badminton Labuan F.T di Sungai Bangat membawakan kemudahan gelanggang badminton bertaraf moden untuk komuniti tempatan beriadah, berlatih, dan mengadakan pertandingan.</p>

        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <div class="info-card">
                    <span>Misi Kami</span>
                    <h4>Komuniti Sihat & Aktif</h4>
                    <p>Menyediakan ruang sukan yang selesa, mudah diakses, dan sistematik untuk semua pencinta sukan badminton di Wilayah Persekutuan Labuan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <span>Lokasi Strategik</span>
                    <h4>Sungai Bangat</h4>
                    <p>Terletak di kawasan gudang Sungai Bangat, berdekatan dengan Savemore Superstore. Mempunyai ruang letak kereta yang luas dan selesa untuk pengunjung.</p>
                </div>
            </div>
            <div class="col-md-4">
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
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 10px;">
                    Sungai Bangat Warehouse<br>near Savemore Superstore
                </p>
            </div>
            <div class="footer-col">
                <ul>
                    <li><a href="rates.php">Rates</a></li>
                    <li><a href="facility.php">Facility</a></li>
                    <li><a href="about.php">About</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <ul>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="location.php">Location</a></li>
                    <li><a href="auth/login.php">Book Now</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; Sports Center • Badminton court booking</div>
            <div>Powered by aestivo.ai</div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>