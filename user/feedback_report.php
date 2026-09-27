<?php

session_start();

include __DIR__ . '/../config/db.php';

// Semakan Akses Sesi

if (!isset($_SESSION['user'])) {

    header("Location: ../auth/login.php");

    exit();

}

$user_id = $_SESSION['user']['id'];

$message = "";

// SIMPAN FEEDBACK BILA DIHANTAR

if (isset($_POST['submit_feedback'])) {

    $type    = trim($_POST['type']);

    $rating  = (int)$_POST['rating'];

    $comment = trim($_POST['message']);

    // Prepared Statement untuk keselamatan

    $stmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, type, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "isis", $user_id, $type, $rating, $comment);

        if (mysqli_stmt_execute($stmt)) {

            $message = "Terima kasih! Maklum balas anda berjaya dihantar.";

        } else {

            $message = "Ralat semasa menghantar maklum balas.";

        }

        mysqli_stmt_close($stmt);

    }

}

// AMBIL SENARAI FEEDBACK DARI DATABASE (TERKINI DAHULU)

$feedbacks = mysqli_query($conn, "

    SELECT f.*, u.name, u.role

    FROM feedback f

    JOIN users u ON f.user_id = u.id

    ORDER BY f.created_at DESC

    LIMIT 5

");

?>

<!DOCTYPE html>

<html lang="ms">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Feedback & Report - Badminton Kampung Panji</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{--bg:#090a0f;--panel:#141620;--panel2:#1a1c29;--border:rgba(255,255,255,.09);--text:#f8fafc;--muted:#94a3b8;--indigo:#6366f1;--purple:#a855f7;--green:#4ade80;--yellow:#fbbf24}

*{box-sizing:border-box}

body{margin:0;min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;background:#090a0f;color:var(--text);overflow-x:hidden}

body:before{content:"";position:fixed;inset:0;z-index:-4;background:radial-gradient(circle at 10% 10%,rgba(99,102,241,.23),transparent 31%),radial-gradient(circle at 90% 70%,rgba(168,85,247,.17),transparent 32%),linear-gradient(180deg,#090a0f,#0d0e16 55%,#090a0f)}

.bg-video{position:fixed;inset:0;width:100vw;height:100vh;object-fit:cover;z-index:-5;filter:brightness(.14) saturate(.8)}

.orb{position:fixed;border-radius:50%;filter:blur(140px);opacity:.12;z-index:-3;pointer-events:none}.orb.one{width:350px;height:350px;background:#6366f1;left:-140px;top:160px}.orb.two{width:380px;height:380px;background:#a855f7;right:-170px;bottom:-60px}

.nav{position:relative;height:78px;padding:10px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:rgba(9,10,15,.82);backdrop-filter:blur(18px);position:sticky;top:0;z-index:20}

.brand{display:flex;align-items:center;gap:12px;text-decoration:none}.brand-logo{width:53px;height:53px;display:flex;align-items:center;justify-content:center;border-radius:10px;overflow:hidden}.brand-logo img{width:51px;height:51px;object-fit:contain;border-radius:9px}.brand-text{display:flex;flex-direction:column}.brand-text strong{font-size:.95rem;color:#fff;line-height:1.05}.brand-text span{font-size:.62rem;color:#a855f7;font-weight:800;letter-spacing:1.5px;margin-top:5px}

.nav-actions{display:flex;align-items:center;gap:8px}

.nav-btn{height:40px;padding:0 14px;border:1px solid var(--border);border-radius:50px;background:rgba(255,255,255,.035);color:#dbe3ef;text-decoration:none;display:flex;align-items:center;gap:7px;font-size:.68rem;font-weight:800;transition:transform .22s ease,background .22s ease,border-color .22s ease,color .22s ease,box-shadow .22s ease}

.nav-btn:hover{transform:translateY(-2px);background:rgba(255,255,255,.08);border-color:rgba(99,102,241,.25);color:#fff;box-shadow:0 7px 20px rgba(99,102,241,.10)}

.nav-btn.active{background:rgba(99,102,241,.11);border-color:rgba(99,102,241,.25);color:#c4b5fd}

.nav-btn.logout{border-color:rgba(251,113,133,.2);color:#fda4af;background:rgba(251,113,133,.06)}

.nav-btn.logout:hover{background:rgba(251,113,133,.11);border-color:rgba(251,113,133,.3)}

.wrapper{width:min(1160px,calc(100% - 30px));margin:0 auto;padding:36px 0 65px}

.hero{text-align:center;margin-bottom:25px}.hero-badge{display:inline-flex;align-items:center;gap:7px;padding:6px 11px;border:1px solid rgba(99,102,241,.25);border-radius:50px;background:rgba(99,102,241,.09);color:#a5b4fc;font-size:.6rem;font-weight:800;letter-spacing:1px;text-transform:uppercase}.hero h1{font-size:2rem;letter-spacing:-1px;margin:12px 0 7px}.hero p{color:var(--muted);font-size:.78rem;line-height:1.6;margin:0 auto;max-width:600px}

.app-container{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:17px;align-items:start}

.panel{position:relative;border:1px solid var(--border);border-radius:25px;background:linear-gradient(145deg,rgba(24,26,39,.95),rgba(14,15,23,.94));box-shadow:0 25px 65px rgba(0,0,0,.35);overflow:hidden;backdrop-filter:blur(18px)}

.form-panel{padding:27px}.form-panel:after{content:"🏸";position:absolute;right:-15px;top:-35px;font-size:8rem;opacity:.035;transform:rotate(18deg);pointer-events:none}

.panel-label{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:50px;background:rgba(168,85,247,.09);border:1px solid rgba(168,85,247,.19);color:#c4b5fd;font-size:.58rem;font-weight:800;letter-spacing:.8px;text-transform:uppercase}.form-panel h2{font-size:1.35rem;margin:12px 0 6px;letter-spacing:-.4px}.form-panel .intro{color:#94a3b8;font-size:.7rem;line-height:1.6;margin:0 0 21px}

.success-msg{display:flex;align-items:center;gap:8px;margin-bottom:15px;padding:11px 12px;border-radius:12px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.18);color:#86efac;font-size:.67rem;font-weight:700}

.field-label{display:block;color:#cbd5e1;font-size:.63rem;font-weight:800;margin:0 0 7px}.field{margin-bottom:15px}.form-control-custom,.form-select-custom{width:100%;font-family:inherit;background:rgba(255,255,255,.035);border:1px solid var(--border);border-radius:13px;padding:12px 13px;color:#fff;font-size:.72rem;font-weight:600;outline:none;transition:.2s}.form-control-custom:focus,.form-select-custom:focus{border-color:#818cf8;background:rgba(99,102,241,.06);box-shadow:0 0 0 3px rgba(99,102,241,.08)}.form-select-custom option{background:#171925;color:#fff}.form-control-custom::placeholder{color:#64748b}

.rating-group{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin-bottom:16px}.rating-group input{display:none}.rating-btn{height:44px;border-radius:12px;border:1px solid var(--border);background:rgba(255,255,255,.025);display:flex;align-items:center;justify-content:center;gap:3px;color:#fbbf24;font-size:.68rem;font-weight:800;cursor:pointer;transition:.2s}.rating-btn:hover{transform:translateY(-2px);border-color:rgba(251,191,36,.3)}.rating-group input:checked + .rating-btn{background:linear-gradient(135deg,rgba(99,102,241,.22),rgba(168,85,247,.19));border-color:#818cf8;box-shadow:0 6px 20px rgba(99,102,241,.12);color:#fde68a}

.btn-submit{width:100%;height:48px;border:0;border-radius:50px;background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;font-family:inherit;font-size:.75rem;font-weight:800;cursor:pointer;box-shadow:0 9px 25px rgba(99,102,241,.25);transition:.2s}.btn-submit:hover{transform:translateY(-2px);box-shadow:0 13px 30px rgba(99,102,241,.35)}

.mini-note{display:flex;align-items:center;justify-content:center;gap:6px;color:#64748b;font-size:.57rem;margin-top:12px}.mini-note i{color:#4ade80}

.feed-panel{padding:25px}.feed-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:17px}.feed-title{display:flex;align-items:center;gap:10px}.feed-title .icon{width:37px;height:37px;border-radius:11px;background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(168,85,247,.14));border:1px solid rgba(99,102,241,.18);color:#a5b4fc;display:flex;align-items:center;justify-content:center}.feed-title h3{font-size:.93rem;margin:0}.feed-title small{display:block;color:#64748b;font-size:.56rem;margin-top:2px}.live-badge{display:flex;align-items:center;gap:6px;padding:6px 9px;border-radius:50px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.17);color:#86efac;font-size:.56rem;font-weight:800}.live-dot{width:6px;height:6px;border-radius:50%;background:#4ade80;box-shadow:0 0 9px #4ade80}

.feedback-list{display:flex;flex-direction:column;gap:10px;max-height:600px;overflow-y:auto;padding-right:3px}.feedback-list::-webkit-scrollbar{width:5px}.feedback-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:20px}

.feedback-card{position:relative;padding:14px;border:1px solid rgba(255,255,255,.075);border-radius:16px;background:rgba(255,255,255,.025);transition:.2s}.feedback-card:hover{transform:translateY(-2px);border-color:rgba(99,102,241,.22);background:rgba(99,102,241,.035)}.feedback-top{display:flex;align-items:center;gap:10px}.avatar{width:39px;height:39px;min-width:39px;border-radius:12px;background:linear-gradient(135deg,#4f46e5,#9333ea);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:800;box-shadow:0 7px 18px rgba(99,102,241,.18)}.user-meta{flex:1;min-width:0}.user-meta strong{display:block;font-size:.72rem}.user-meta small{display:block;color:#64748b;font-size:.55rem;margin-top:2px;text-transform:capitalize}.type-badge{padding:5px 8px;border-radius:50px;background:rgba(99,102,241,.09);border:1px solid rgba(99,102,241,.17);color:#a5b4fc;font-size:.52rem;font-weight:800}.feedback-comment{margin:11px 0 10px;padding:10px 11px;border-radius:11px;background:rgba(255,255,255,.025);color:#cbd5e1;font-size:.67rem;line-height:1.55}.feedback-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px}.stars{color:#fbbf24;font-size:.65rem;white-space:nowrap}.quote-icon{color:#4f46e5;opacity:.5;font-size:.75rem}

.empty{text-align:center;padding:50px 20px;color:#64748b}.empty i{font-size:2rem;margin-bottom:10px;color:#818cf8}.empty strong{display:block;color:#cbd5e1;font-size:.78rem;margin-bottom:4px}.empty span{font-size:.62rem}

.info-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:17px}.info-item{padding:12px;border-radius:14px;border:1px solid var(--border);background:rgba(255,255,255,.025);text-align:center}.info-item i{color:#a5b4fc;font-size:.8rem;margin-bottom:6px}.info-item strong{display:block;font-size:.63rem}.info-item span{display:block;color:#64748b;font-size:.52rem;margin-top:2px}

@media(max-width:850px){.app-container{grid-template-columns:1fr}.nav{height:70px;padding:8px 16px}.brand-logo{width:47px;height:47px}.brand-logo img{width:45px;height:45px}.wrapper{width:min(100% - 20px,1160px);padding-top:25px}.hero h1{font-size:1.55rem}.feedback-list{max-height:none}}

@media(max-width:500px){.brand-text{display:none}.nav-btn span{display:none}.nav-btn{width:40px;padding:0;justify-content:center}.nav-actions{gap:5px}.form-panel,.feed-panel{padding:20px 15px}.rating-group{gap:4px}.rating-btn{font-size:.59rem}.info-strip{grid-template-columns:1fr}.feed-head{align-items:flex-start}.hero{padding:0 8px}}


/* ===== CONSISTENT GLASS CENTER NAV ===== */
.nav-center{position:absolute;left:50%;transform:translateX(-50%);display:flex;align-items:center;justify-content:center;gap:10px;margin:0;white-space:nowrap}
.nav-center a{position:relative;height:40px;padding:0 17px;display:flex;align-items:center;justify-content:center;color:#cbd5e1;text-decoration:none;font-size:.76rem;font-weight:800;letter-spacing:.1px;background:linear-gradient(135deg,rgba(255,255,255,.055),rgba(255,255,255,.018));border:1px solid rgba(255,255,255,.09);border-radius:13px;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 4px 15px rgba(0,0,0,.10);overflow:hidden;transition:transform .22s ease,background .22s ease,border-color .22s ease,color .22s ease,box-shadow .22s ease}
.nav-center a::before{content:"";position:absolute;top:-100%;left:-60%;width:45%;height:300%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.10),transparent);transform:rotate(25deg);transition:left .45s ease;pointer-events:none}
.nav-center a::after{content:"";position:absolute;left:50%;bottom:4px;width:0;height:2px;transform:translateX(-50%);border-radius:50px;background:linear-gradient(90deg,#6366f1,#a855f7);box-shadow:0 0 8px rgba(168,85,247,.7);transition:width .22s ease}
.nav-center a:hover{color:#fff;background:linear-gradient(135deg,rgba(99,102,241,.13),rgba(168,85,247,.07));border-color:rgba(129,140,248,.35);transform:translateY(-2px);box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 8px 22px rgba(0,0,0,.22),0 0 20px rgba(99,102,241,.10)}
.nav-center a:hover::before{left:130%}.nav-center a:hover::after{width:35%}
.nav-center a:active{transform:translateY(0) scale(.94);color:#fff;background:linear-gradient(135deg,rgba(99,102,241,.22),rgba(168,85,247,.14));border-color:rgba(129,140,248,.55);box-shadow:inset 0 3px 8px rgba(0,0,0,.30),0 0 15px rgba(99,102,241,.20);transition-duration:.08s}
.nav-center a.active{color:#fff;background:linear-gradient(135deg,rgba(99,102,241,.17),rgba(168,85,247,.10));border-color:rgba(129,140,248,.38);box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 0 18px rgba(99,102,241,.12)}
.nav-center a.active::after{width:35%}
@media(max-width:900px){.nav-center{gap:7px}.nav-center a{height:38px;padding:0 12px;font-size:.69rem}}
@media(max-width:720px){.nav-center{display:none}}


/* ===== LOCKED NAVBAR - EXACT SAME POSITION ON ALL USER PAGES ===== */
.custom-navbar,.nav,.navbar{
    position:sticky!important;
    top:0!important;
    z-index:1000!important;
    width:100%!important;
    height:78px!important;
    min-height:78px!important;
    padding:10px 40px!important;
    margin:0!important;
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    border-bottom:1px solid rgba(255,255,255,.09)!important;
    background:rgba(9,10,15,.84)!important;
    backdrop-filter:blur(18px)!important;
    -webkit-backdrop-filter:blur(18px)!important;
    box-sizing:border-box!important;
}
.brand-container,.brand{
    width:220px!important;
    min-width:220px!important;
    height:58px!important;
    display:flex!important;
    align-items:center!important;
    gap:12px!important;
    margin:0!important;
    padding:0!important;
    text-decoration:none!important;
}
.brand-logo-icon,.brand-logo{
    width:53px!important;
    min-width:53px!important;
    height:53px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    flex-shrink:0!important;
    border-radius:10px!important;
    overflow:hidden!important;
    margin:0!important;
    padding:0!important;
}
.brand-logo-icon img,.brand-logo img{
    width:51px!important;
    height:51px!important;
    display:block!important;
    object-fit:contain!important;
    border-radius:9px!important;
    margin:0!important;
    padding:0!important;
}
.brand-text{
    display:flex!important;
    flex-direction:column!important;
    justify-content:center!important;
    align-items:flex-start!important;
    margin:0!important;
    padding:0!important;
}
.nav-center{
    position:absolute!important;
    left:50%!important;
    top:50%!important;
    transform:translate(-50%,-50%)!important;
    height:40px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:10px!important;
    margin:0!important;
    padding:0!important;
    white-space:nowrap!important;
}
.nav-center a{
    position:relative!important;
    height:40px!important;
    min-height:40px!important;
    padding:0 17px!important;
    margin:0!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    line-height:1!important;
    color:#cbd5e1!important;
    text-decoration:none!important;
    font-family:'Plus Jakarta Sans',sans-serif!important;
    font-size:.76rem!important;
    font-weight:800!important;
    letter-spacing:.1px!important;
    background:linear-gradient(135deg,rgba(255,255,255,.055),rgba(255,255,255,.018))!important;
    border:1px solid rgba(255,255,255,.09)!important;
    border-radius:13px!important;
    box-sizing:border-box!important;
    overflow:hidden!important;
    backdrop-filter:blur(14px)!important;
    -webkit-backdrop-filter:blur(14px)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 4px 15px rgba(0,0,0,.10)!important;
    transition:transform .22s ease,background .22s ease,border-color .22s ease,color .22s ease,box-shadow .22s ease!important;
}
.nav-center a::before{
    content:""!important;
    position:absolute!important;
    top:-100%!important;
    left:-60%!important;
    width:45%!important;
    height:300%!important;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.10),transparent)!important;
    transform:rotate(25deg)!important;
    transition:left .45s ease!important;
    pointer-events:none!important;
}
.nav-center a::after{
    content:""!important;
    position:absolute!important;
    left:50%!important;
    bottom:4px!important;
    width:0!important;
    height:2px!important;
    transform:translateX(-50%)!important;
    border-radius:50px!important;
    background:linear-gradient(90deg,#6366f1,#a855f7)!important;
    box-shadow:0 0 8px rgba(168,85,247,.7)!important;
    transition:width .22s ease!important;
}
.nav-center a:hover{
    color:#fff!important;
    background:linear-gradient(135deg,rgba(99,102,241,.13),rgba(168,85,247,.07))!important;
    border-color:rgba(129,140,248,.35)!important;
    transform:translateY(-2px)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 8px 22px rgba(0,0,0,.22),0 0 20px rgba(99,102,241,.10)!important;
}
.nav-center a:hover::before{left:130%!important}
.nav-center a:hover::after{width:35%!important}
.nav-center a:active{
    transform:scale(.94)!important;
    background:linear-gradient(135deg,rgba(99,102,241,.22),rgba(168,85,247,.14))!important;
}
.nav-center a.active{
    color:#fff!important;
    background:linear-gradient(135deg,rgba(99,102,241,.17),rgba(168,85,247,.10))!important;
    border-color:rgba(129,140,248,.38)!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 0 18px rgba(99,102,241,.12)!important;
}
.nav-center a.active::after{width:35%!important}
.nav-actions{
    width:220px!important;
    min-width:220px!important;
    height:40px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:flex-end!important;
    gap:8px!important;
    margin:0!important;
    padding:0!important;
}
.nav-actions .nav-btn{
    height:40px!important;
    min-height:40px!important;
    margin:0!important;
    box-sizing:border-box!important;
    font-family:'Plus Jakarta Sans',sans-serif!important;
    line-height:1!important;
}
@media(max-width:900px){
    .custom-navbar,.nav,.navbar{padding:10px 20px!important}
    .brand-container,.brand{width:175px!important;min-width:175px!important}
    .nav-actions{width:175px!important;min-width:175px!important}
    .nav-center{gap:7px!important}
    .nav-center a{padding:0 11px!important;font-size:.68rem!important}
}
@media(max-width:720px){
    .custom-navbar,.nav,.navbar{height:70px!important;min-height:70px!important;padding:8px 15px!important}
    .nav-center{display:none!important}
    .brand-container,.brand{width:auto!important;min-width:0!important}
    .nav-actions{width:auto!important;min-width:0!important}
    .brand-logo-icon,.brand-logo{width:47px!important;min-width:47px!important;height:47px!important}
    .brand-logo-icon img,.brand-logo img{width:45px!important;height:45px!important}
}

</style>

</head>

<body>

<video autoplay loop muted playsinline class="bg-video">

    <source src="sports.mp4" type="video/mp4">

</video>

<div class="orb one"></div>

<div class="orb two"></div>

<nav class="nav">

    <a href="dashboard.php" class="brand">

        <div class="brand-logo"><img src="../logo-badminton.png" alt="Badminton Kampung Panji"></div>

        <div class="brand-text"><strong>BADMINTON</strong><span>KAMPUNG PANJI</span></div>

    </a>

    <div class="nav-center">
    <a href="feedback_report.php" class="active">Feedback</a>
    <a href="message.php">Message</a>
    <a href="my_booking.php">My Booking</a>
    <a href="profile.php">Profile</a>
</div>

<div class="nav-actions">

<a href="dashboard.php" class="nav-btn"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>

<a href="../auth/logout.php" class="nav-btn logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>

</div>

</nav>

<main class="wrapper">

    <header class="hero">

        <span class="hero-badge"><i class="fa-solid fa-comments"></i> Community Court</span>

        <h1>Your Voice Matters.</h1>

        <p>Kongsi pengalaman, cadangan atau laporkan kerosakan gelanggang. Maklum balas anda membantu Badminton Kampung Panji jadi lebih baik.</p>

    </header>

    <div class="app-container">

        <section class="panel form-panel">

            <span class="panel-label"><i class="fa-solid fa-paper-plane"></i> Share Your Experience</span>

            <h2>Submit Feedback</h2>

            <p class="intro">Ada cadangan atau masalah pada court? Beritahu kami dan bantu tingkatkan pengalaman semua pemain.</p>

            <?php if($message): ?>

                <div class="success-msg"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($message) ?></div>

            <?php endif; ?>

            <form method="POST">

                <div class="field">

                    <label class="field-label"><i class="fa-solid fa-layer-group"></i> Jenis Maklum Balas</label>

                    <select name="type" class="form-select-custom" required>

                        <option value="Feedback">💡 Cadangan / Feedback</option>

                        <option value="Report">⚠️ Aduan Kerosakan Gelanggang</option>

                    </select>

                </div>

                <label class="field-label"><i class="fa-solid fa-star"></i> Penilaian Anda</label>

                <div class="rating-group">

                    <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" class="rating-btn">5 ★</label>

                    <input type="radio" id="star4" name="rating" value="4"><label for="star4" class="rating-btn">4 ★</label>

                    <input type="radio" id="star3" name="rating" value="3"><label for="star3" class="rating-btn">3 ★</label>

                    <input type="radio" id="star2" name="rating" value="2"><label for="star2" class="rating-btn">2 ★</label>

                    <input type="radio" id="star1" name="rating" value="1"><label for="star1" class="rating-btn">1 ★</label>

                </div>

                <div class="field">

                    <label class="field-label"><i class="fa-solid fa-message"></i> Mesej Anda</label>

                    <textarea name="message" class="form-control-custom" rows="5" placeholder="Nyatakan pandangan, cadangan atau aduan anda di sini..." required></textarea>

                </div>

                <button type="submit" name="submit_feedback" class="btn-submit">

                    Submit Feedback &nbsp;<i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>

            <div class="mini-note"><i class="fa-solid fa-shield-halved"></i> Maklum balas anda akan direkod dengan selamat.</div>

            <div class="info-strip">

                <div class="info-item"><i class="fa-solid fa-bolt"></i><strong>Quick Report</strong><span>Mudah dihantar</span></div>

                <div class="info-item"><i class="fa-solid fa-star"></i><strong>Rate Us</strong><span>1 hingga 5 bintang</span></div>

                <div class="info-item"><i class="fa-solid fa-users"></i><strong>Community</strong><span>Bantu pemain lain</span></div>

            </div>

        </section>

        <section class="panel feed-panel">

            <div class="feed-head">

                <div class="feed-title">

                    <div class="icon"><i class="fa-solid fa-comments"></i></div>

                    <div><h3>Maklum Balas Terkini</h3><small>5 feedback terbaru daripada komuniti</small></div>

                </div>

                <span class="live-badge"><span class="live-dot"></span> LIVE</span>

            </div>

            <div class="feedback-list">

                <?php if($feedbacks && mysqli_num_rows($feedbacks)>0): ?>

                    <?php while($row=mysqli_fetch_assoc($feedbacks)): ?>

                        <article class="feedback-card">

                            <div class="feedback-top">

                                <div class="avatar"><?= strtoupper(substr($row['name'],0,1)) ?></div>

                                <div class="user-meta">

                                    <strong><?= htmlspecialchars($row['name']) ?></strong>

                                    <small><?= htmlspecialchars($row['role']) ?></small>

                                </div>

                                <span class="type-badge"><?= htmlspecialchars($row['type'] ?? 'Feedback') ?></span>

                            </div>

                            <div class="feedback-comment">

                                <i class="fa-solid fa-quote-left quote-icon"></i>

                                <?= htmlspecialchars($row['comment']) ?>

                            </div>

                            <div class="feedback-bottom">

                                <div class="stars">

                                    <?php

                                    $stars=(int)($row['rating'] ?? 5);

                                    for($i=1;$i<=5;$i++){

                                        echo $i<=$stars

                                            ? '<i class="fa-solid fa-star"></i> '

                                            : '<i class="fa-regular fa-star" style="color:#475569"></i> ';

                                    }

                                    ?>

                                </div>

                                <span style="font-size:.52rem;color:#64748b"><i class="fa-solid fa-circle-check" style="color:#4ade80"></i> Community Feedback</span>

                            </div>

                        </article>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="empty">

                        <i class="fa-regular fa-comments"></i>

                        <strong>Belum ada maklum balas</strong>

                        <span>Jadilah orang pertama berkongsi pengalaman anda.</span>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </div>

</main>

</body>

</html>
