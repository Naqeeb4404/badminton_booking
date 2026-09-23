<?php
session_start();
include "../config/db.php";

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
    <title>Feedback & Report - AceTime</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-left: #181818;        /* Tema Gelap AceTime untuk Sebelah Kiri */
            --bg-right: #f4f4f4;       /* Latar Belakang Cerah Sebelah Kanan */
            --accent-orange: #d9622b;  /* Warna Aksen Utama */
            --card-white: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #382319, #120e0c);
            min-height: 100vh;
            padding: 30px 15px;
            color: #111;
        }

        /* Container Utama 2 Bahagian (Split Layout) */
        .app-container {
            max-width: 1100px;
            margin: 0 auto;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            background-color: var(--bg-right);
        }

        /* Bahagian Kiri (Borang Feedback) */
        .left-panel {
            background-color: var(--bg-left);
            color: #ffffff;
            padding: 40px;
            height: 100%;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .title-heading {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent-orange);
            line-height: 1.1;
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        /* Form Inputs */
        .form-control-custom, .form-select-custom {
            background-color: #242424;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 12px 16px;
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: #2c2c2c;
            border-color: var(--accent-orange);
            color: #fff;
            box-shadow: none;
        }

        /* Rating Stars Radio */
        .rating-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .rating-btn {
            flex: 1;
            text-align: center;
            background: #242424;
            border: 1px solid #333;
            padding: 10px;
            border-radius: 12px;
            cursor: pointer;
            color: #ffc107;
            font-weight: 700;
            transition: all 0.2s;
        }

        .rating-group input[type="radio"] {
            display: none;
        }

        .rating-group input[type="radio"]:checked + label {
            background: var(--accent-orange);
            border-color: var(--accent-orange);
            color: #ffffff;
        }

        .btn-submit {
            background-color: var(--accent-orange);
            color: #ffffff;
            font-weight: 800;
            border-radius: 14px;
            padding: 14px;
            border: none;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #be4f1d;
            color: #ffffff;
        }

        /* Bahagian Kanan (Senarai Kad Speech Bubble) */
        .right-panel {
            padding: 40px;
            max-height: 700px;
            overflow-y: auto;
        }

        /* Cad Speech Bubble Style (Seperti dalam gambar) */
        .speech-bubble {
            position: relative;
            background: var(--card-white);
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        /* Ekor Speech Bubble */
        .speech-bubble::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 40px;
            border-width: 10px 10px 0;
            border-style: solid;
            border-color: var(--card-white) transparent;
            display: block;
            width: 0;
        }

        .user-avatar {
            width: 55px;
            height: 55px;
            background-color: #221a16;
            color: var(--accent-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .bubble-content h6 {
            font-weight: 800;
            margin-bottom: 2px;
            color: #111;
        }

        .bubble-content .user-role {
            font-size: 0.75rem;
            color: #888;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        .bubble-content p {
            font-size: 0.88rem;
            color: #444;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .stars-display {
            color: #ffc107;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>

    <div class="app-container">
        <div class="row g-0">

            <!-- SEBELAH KIRI: BORANG FEEDBACK -->
            <div class="col-lg-5 left-panel">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="#" class="brand-logo">AceTime</a>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <h1 class="title-heading">Submit Your Feedback</h1>
                <p class="subtitle">Kongsi pengalaman atau aduan kerosakan gelanggang anda untuk membantu kami membina perkhidmatan terbaik.</p>

                <?php if ($message): ?>
                    <div class="alert alert-success rounded-4 text-center small fw-bold mb-3">
                        <i class="fa-solid fa-circle-check me-1"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Jenis Maklum Balas -->
                    <div class="mb-3">
                        <select name="type" class="form-select form-select-custom" required>
                            <option value="Feedback">💡 Cadangan / Feedback</option>
                            <option value="Report">⚠️ Aduan Kerosakan Gelanggang</option>
                        </select>
                    </div>

                    <!-- Penilaian Rating Bintang -->
                    <label class="form-label small fw-bold text-light">Penilaian Anda</label>
                    <div class="rating-group">
                        <input type="radio" id="star5" name="rating" value="5" checked>
                        <label for="star5" class="rating-btn">5 ★</label>

                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" class="rating-btn">4 ★</label>

                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" class="rating-btn">3 ★</label>

                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" class="rating-btn">2 ★</label>

                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" class="rating-btn">1 ★</label>
                    </div>

                    <!-- Mesej -->
                    <div class="mb-4">
                        <textarea name="message" class="form-control form-control-custom" rows="4" placeholder="Nyatakan pandangan atau aduan anda di sini..." required></textarea>
                    </div>

                    <button type="submit" name="submit_feedback" class="btn-submit">
                        Submit Feedback
                    </button>
                </form>
            </div>

            <!-- SEBELAH KANAN: PAPARAN FEEDBACK REAL-TIME (SPEECH BUBBLES) -->
            <div class="col-lg-7 right-panel">
                <h5 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-comments me-2"></i>Maklum Balas Terkini</h5>

                <?php if ($feedbacks && mysqli_num_rows($feedbacks) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($feedbacks)): ?>
                        <div class="speech-bubble">
                            <!-- Avatar Pengguna -->
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                            </div>

                            <!-- Kandungan Feedback -->
                            <div class="bubble-content w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($row['name']); ?></h6>
                                    <span class="badge bg-secondary text-capitalize" style="font-size:0.7rem;">
                                        <?php echo htmlspecialchars($row['type'] ?? 'Feedback'); ?>
                                    </span>
                                </div>
                                <span class="user-role"><?php echo htmlspecialchars($row['role']); ?></span>

                                <p><?php echo htmlspecialchars($row['comment']); ?></p>

                                <!-- Paparan Bintang -->
                                <div class="stars-display">
                                    <?php 
                                    $stars = (int)($row['rating'] ?? 5);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $stars) {
                                            echo '<i class="fa-solid fa-star text-warning"></i> ';
                                        } else {
                                            echo '<i class="fa-regular fa-star text-muted"></i> ';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Belum ada maklum balas. Jadilah yang pertama menghantar feedback!</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>