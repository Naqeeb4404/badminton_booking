<?php
session_start();
include __DIR__ . '/config/db.php';
?>

<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rules & Regulations - Sports Center Badminton Labuan F.T</title>

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
            max-width: 900px;
            margin: 0 auto;
            padding: 80px 20px 100px 20px;
        }

        .rules-title {
            font-size: 3.2rem;
            font-weight: 800;
            letter-spacing: -1.5px;
            margin-bottom: 15px;
            color: var(--text-main);
        }

        .rules-desc {
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

        .delay-4 {
            transition-delay: 0.4s;
        }

        /* Custom Rule Card Styling */
        .rule-card {
            background: var(--credix-card);
            border: 1px solid var(--credix-border);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 20px;
            display: flex;
            gap: 22px;
            align-items: flex-start;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .rule-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .rule-icon {
            font-size: 1.3rem;
            color: #818cf8;
            background: rgba(99, 102, 241, 0.1);
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .rule-content h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .rule-content p {
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

            .rules-title {
                font-size: 2.5rem;
            }
        }
    
        /* TRANSPARENT / GLASS DOCK NAVIGATION */
        .custom-navbar{
            position:sticky;
            top:0;
            z-index:1000;
        }

        .nav-links{
            display:flex;
            align-items:center;
            gap:4px !important;
            padding:6px;
            border:1px solid rgba(255,255,255,.10);
            border-radius:999px;
            background:rgba(255,255,255,.055);
            -webkit-backdrop-filter:blur(18px) saturate(150%);
            backdrop-filter:blur(18px) saturate(150%);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.08),
                0 10px 30px rgba(0,0,0,.20);
        }

        .nav-links a{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:center;
            min-height:36px;
            padding:8px 15px;
            border:1px solid transparent;
            border-radius:999px;
            color:#94a3b8;
            text-decoration:none;
            font-size:.84rem;
            font-weight:700;
            line-height:1;
            transition:
                color .22s ease,
                background .22s ease,
                border-color .22s ease,
                box-shadow .22s ease,
                transform .22s ease;
        }

        .nav-links a:hover{
            color:#fff;
            background:rgba(255,255,255,.07);
            border-color:rgba(255,255,255,.08);
            box-shadow:0 0 18px rgba(99,102,241,.16);
            transform:translateY(-1px);
        }

        .nav-links a.active{
            color:#fff;
            background:linear-gradient(
                135deg,
                rgba(99,102,241,.24),
                rgba(168,85,247,.18)
            );
            border-color:rgba(129,140,248,.34);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.10),
                0 0 20px rgba(99,102,241,.22);
        }

        @media (max-width:991.98px){
            .nav-links{
                display:none !important;
            }
        }

    </style>
</head>

<body>

    <div class="top-announcement-bar d-none d-md-flex">
        <div>CALL +60 11 6351 9188 &nbsp;&nbsp;|&nbsp;&nbsp; Dewan Kampung Panji, Kuala Terengganu</div>
        <div>
            <a href="auth/login.php" class="btn-book-now">Book Now</a>
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
            <a href="facility.php">Facility</a>
            <a href="about.php">About</a>
            <a href="faq.php">FAQ</a>
            <a href="rules.php" class="active">Rules</a>
            <a href="location.php">Location</a>
        </div>

        <a href="auth/login.php" class="btn-book-now">Book Now</a>
    </nav>

    <div class="content-container">

        <div class="reveal-on-scroll">
            <h1 class="rules-title">Rules & Regulations</h1>
            <p class="rules-desc">Sila patuhi peraturan dan etika penggunaan gelanggang yang ditetapkan demi keselamatan
                dan keselesaan bersama.</p>
        </div>

        <div class="rules-list">

            <div class="rule-card reveal-on-scroll delay-1">
                <div class="rule-icon"><i class="fa-solid fa-shoe-prints"></i></div>
                <div class="rule-content">
                    <h4>Kasut Sesuai (Non-Marking Shoes)</h4>
                    <p>Semua pemain diwajibkan memakai kasut gelanggang jenis tapak getah yang tidak meninggalkan kesan
                        (non-marking shoes) bagi menjaga kualiti permukaan lantai gelanggang.</p>
                </div>
            </div>

            <div class="rule-card reveal-on-scroll delay-2">
                <div class="rule-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="rule-content">
                    <h4>Ketepatan Masa Tempahan</h4>
                    <p>Sila masuk dan keluar gelanggang mengikut slot masa yang telah ditempah. Tempoh lewat tidak akan
                        diganti sekiranya masa slot anda telah tamat.</p>
                </div>
            </div>

            <div class="rule-card reveal-on-scroll delay-3">
                <div class="rule-icon"><i class="fa-solid fa-ban"></i></div>
                <div class="rule-content">
                    <h4>Larangan Merokok & Makanan</h4>
                    <p>Merokok, membuang sampah, serta membawa makanan atau minuman manis (selain air kosong) ke dalam
                        kawasan gelanggang adalah dilarang sama sekali.</p>
                </div>
            </div>

            <div class="rule-card reveal-on-scroll delay-4">
                <div class="rule-icon"><i class="fa-solid fa-child-reaching"></i></div>
                <div class="rule-content">
                    <h4>Etika & Keselamatan Sukan</h4>
                    <p>Sentiasa mengamalkan sikap sopan santun, menghormati pemain lain, serta menjaga keselamatan diri
                        dan barangan peribadi sepanjang berada di premis.</p>
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
                    <li><a href="about.php">About</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h6>Sokongan</h6>
                <ul>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="rules.php">Rules</a></li>
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