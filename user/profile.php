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
$msg_type = "success";

// 1. KEMASKINI PROFIL
if (isset($_POST['update_profile'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $phone, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            // Kemaskini data sesi
            $_SESSION['user']['name']  = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            $message  = "Profil berjaya dikemaskini!";
            $msg_type = "success";
        } else {
            $message  = "Ralat semasa kemaskini profil.";
            $msg_type = "danger";
        }
        mysqli_stmt_close($stmt);
    }
}

// 2. HANTAR FEEDBACK / RATING
if (isset($_POST['submit_feedback'])) {
    $rating  = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    $stmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, rating, comment, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $rating, $comment);
        if (mysqli_stmt_execute($stmt)) {
            $message  = "Maklum balas anda berjaya dihantar! Terima kasih.";
            $msg_type = "success";
        } else {
            $message  = "Ralat semasa hantar maklum balas.";
            $msg_type = "danger";
        }
        mysqli_stmt_close($stmt);
    }
}

// 3. NYAHAKTIFKAN AKAUN (DEACTIVATE PROFILE)
if (isset($_POST['deactivate_account'])) {
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    session_destroy();
    header("Location: ../auth/login.php?account=deactivated");
    exit();
}

// AMBIL DATA DARI PANGKALAN DATA
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user_data  = mysqli_fetch_assoc($user_query);

$bookings_query = mysqli_query($conn, "
    SELECT b.*, c.court_name 
    FROM bookings b 
    JOIN courts c ON b.court_id = c.id 
    WHERE b.user_id = '$user_id' 
    ORDER BY b.booking_date DESC, b.booking_time DESC
");

$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - AceTime</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-outer: #221a16;
            --bg-app: #f4f4f4;
            --card-dark: #181818;
            --accent-orange: #d9622b;
            --text-dark: #111111;
            --text-muted: #777777;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #382319, #120e0c);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 30px 15px;
        }

        .app-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: var(--bg-app);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            padding: 30px 40px 40px 40px;
        }

        .custom-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #000;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .btn-black {
            background-color: var(--card-dark);
            color: #fff;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-black:hover {
            background-color: #333;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Profile Header Banner Biasa */
        .profile-card-header {
            background: var(--card-dark);
            border-radius: 24px;
            padding: 30px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: var(--accent-orange);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        /* Nav Tabs Custom Styling */
        .nav-pills-custom {
            background: #e9ecef;
            border-radius: 50px;
            padding: 6px;
            gap: 5px;
            margin-bottom: 30px;
        }

        .nav-pills-custom .nav-link {
            border-radius: 50px;
            color: var(--text-dark);
            font-weight: 700;
            font-size: 0.88rem;
            padding: 10px 20px;
            transition: all 0.2s ease;
        }

        .nav-pills-custom .nav-link.active {
            background-color: var(--card-dark);
            color: #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        /* Form Controls */
        .form-control-custom, .form-select-custom {
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--accent-orange);
            box-shadow: none;
            background-color: #fff;
        }

        .btn-action {
            background-color: var(--card-dark);
            color: #ffffff;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px 24px;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background-color: var(--accent-orange);
            color: #ffffff;
        }

        /* KAD DENGAN LATAR BELAKANG IMEJ UNTUK SEMUA BAHAGIAN */
        .card-with-bg {
            position: relative;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            color: #fff;
            margin-bottom: 20px;
        }

        .card-bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .card-bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.70); /* Gelap sedikit supaya teks jelas */
            z-index: 2;
        }

        .card-content-inner {
            position: relative;
            z-index: 3;
        }

        .card-content-inner label, 
        .card-content-inner .form-label, 
        .card-content-inner .text-muted, 
        .card-content-inner th {
            color: #d1d5db !important;
        }

        .card-content-inner p, 
        .card-content-inner h5, 
        .card-content-inner td, 
        .card-content-inner th {
            color: #ffffff !important;
        }

        /* STYLING KHAS UNTUK SEJARAH TEMPAHAN (BOOKING HISTORY) */
        .history-table-container {
            background: rgba(18, 18, 18, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .table-custom-dark {
            margin-bottom: 0;
            color: #fff;
            background-color: transparent;
        }

        .table-custom-dark th {
            background-color: rgba(30, 30, 30, 0.95) !important;
            color: #adb5bd !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-custom-dark td {
            padding: 14px 18px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.9rem;
        }

        .table-custom-dark tbody tr:last-child td {
            border-bottom: none;
        }

        .table-custom-dark tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04);
        }

        /* Badge Status Moden & Cantik */
        .badge-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-status.bg-success {
            background-color: rgba(25, 135, 84, 0.2) !important;
            color: #4ade80 !important;
            border: 1px solid rgba(74, 222, 128, 0.3);
        }

        .badge-status.bg-warning {
            background-color: rgba(255, 193, 7, 0.15) !important;
            color: #facc15 !important;
            border: 1px solid rgba(250, 204, 21, 0.3);
        }

        .badge-status.bg-danger {
            background-color: rgba(220, 53, 69, 0.2) !important;
            color: #f87171 !important;
            border: 1px solid rgba(248, 113, 113, 0.3);
        }

        /* Notifications Items */
        .notif-item {
            border-left: 4px solid var(--accent-orange);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    <div class="app-container">

        <!-- Header Navigation -->
        <nav class="custom-navbar">
            <a href="#" class="brand-logo">AceTime</a>
            <a href="dashboard.php" class="btn-black">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </nav>

        <!-- Profile Header -->
        <div class="profile-card-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user_data['name'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
                <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($user_data['name'] ?? 'Pengguna'); ?></h3>
                <span class="badge bg-warning text-dark text-uppercase fw-bold">
                    <i class="fa-solid fa-shield me-1"></i> <?php echo htmlspecialchars($user_data['role'] ?? 'User'); ?>
                </span>
            </div>
        </div>

        <!-- Mesej Status Notifikasi (Jika ada) -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show rounded-4 mb-4" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills nav-pills-custom justify-content-center" id="profileTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="info-tab" data-bs-toggle="pill" data-bs-target="#info-panel">
                    <i class="fa-solid fa-user me-1"></i> Profile
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="edit-tab" data-bs-toggle="pill" data-bs-target="#edit-panel">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
                </button>
            </li>
        
            <li class="nav-item">
                <button class="nav-link" id="feedback-tab" data-bs-toggle="pill" data-bs-target="#feedback-panel">
                    <i class="fa-solid fa-star me-1"></i> Feedback
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="notif-tab" data-bs-toggle="pill" data-bs-target="#notif-panel">
                    <i class="fa-solid fa-bell me-1"></i> Notification
                </button>
            </li>
        </ul>

        <!-- Tab Content Panels -->
        <div class="tab-content" id="profileTabsContent">

            <!-- 1. PROFILE INFO -->
            <div class="tab-pane fade show active" id="info-panel">
                <div class="card-with-bg">
                    <img src="qr/userbackground.jpg" alt="User Background" class="card-bg-img">
                    <div class="card-bg-overlay"></div>
                    
                    <div class="card-content-inner">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-id-card me-2"></i>Maklumat Akaun</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold">Nama Penuh</label>
                                <p class="fw-bold fs-6"><?php echo htmlspecialchars($user_data['name'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Alamat E-mel</label>
                                <p class="fw-bold fs-6"><?php echo htmlspecialchars($user_data['email'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Nombor Telefon</label>
                                <p class="fw-bold fs-6"><?php echo htmlspecialchars($user_data['phone'] ?? 'Belum ditetapkan'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Peranan (Role)</label>
                                <p class="fw-bold fs-6 text-uppercase"><?php echo htmlspecialchars($user_data['role'] ?? 'User'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. EDIT PROFILE & DEACTIVATE -->
            <div class="tab-pane fade" id="edit-panel">
                <div class="card-with-bg mb-4">
                    <img src="qr/userbackground.jpg" alt="User Background" class="card-bg-img">
                    <div class="card-bg-overlay"></div>

                    <div class="card-content-inner">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-user-gear me-2"></i>Kemaskini Profil</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Penuh</label>
                                <input type="text" name="name" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Alamat E-mel</label>
                                <input type="email" name="email" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Nombor Telefon</label>
                                <input type="text" name="phone" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="cth: 0123456789">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-action w-100">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Kad Deactivate Account -->
                <div class="card-with-bg border border-danger">
                    <img src="qr/userbackground.jpg" alt="User Background" class="card-bg-img">
                    <div class="card-bg-overlay"></div>

                    <div class="card-content-inner">
                        <h5 class="fw-bold text-danger mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Nyahaktifkan Akaun (Deactivate)</h5>
                        <p class="text-white-50 small mb-3">Tindakan ini akan memadamkan atau menyahaktifkan akaun anda serta merta. Anda tidak akan dapat log masuk lagi selepas ini.</p>
                        <form method="POST" onsubmit="return confirm('Adakah anda pasti mahu menyahaktifkan / memadam akaun anda?');">
                            <button type="submit" name="deactivate_account" class="btn btn-danger w-100 fw-bold py-2">
                                Nyahaktifkan Akaun Saya
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. BOOKING HISTORY -->
            <div class="tab-pane fade" id="history-panel">
                <div class="card-with-bg">
                    <img src="qr/userbackground.jpg" alt="User Background" class="card-bg-img">
                    <div class="card-bg-overlay"></div>

                    <div class="card-content-inner">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-calendar-check me-2"></i>Sejarah Tempahan</h5>
                        <div class="history-table-container">
                            <div class="table-responsive">
                                <table class="table table-custom-dark align-middle">
                                    <thead>
                                        <tr>
                                            <th>Gelanggang</th>
                                            <th>Tarikh</th>
                                            <th>Masa</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($bookings_query) > 0): ?>
                                            <?php while ($b = mysqli_fetch_assoc($bookings_query)): ?>
                                                <tr>
                                                    <td class="fw-bold text-white"><i class="fa-solid fa-circle-dot text-warning me-2 fs-6"></i><?php echo htmlspecialchars($b['court_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['booking_time']); ?></td>
                                                    <td>
                                                        <?php 
                                                            $st = strtolower($b['status']);
                                                            $badge_color = ($st == 'approved') ? 'bg-success' : (($st == 'pending') ? 'bg-warning' : 'bg-danger');
                                                            $icon_status = ($st == 'approved') ? 'fa-check' : (($st == 'pending') ? 'fa-clock' : 'fa-xmark');
                                                        ?>
                                                        <span class="badge badge-status <?php echo $badge_color; ?>">
                                                            <i class="fa-solid <?php echo $icon_status; ?>"></i> <?php echo ucfirst($b['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-white-50 py-4">Tiada rekod tempahan dijumpai.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. FEEDBACK / RATING -->
            <div class="tab-pane fade" id="feedback-panel">
                <div class="card-with-bg">
                    <img src="qr/userbackground.jpg" alt="User Background" class="card-bg-img">
                    <div class="card-bg-overlay"></div>

                    <div class="card-content-inner">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-comment-dots me-2"></i>Hantar Feedback & Penilaian</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Penilaian (Rating)</label>
                                <select name="rating" class="form-select form-select-custom" required>
                                    <option value="5">⭐⭐⭐⭐⭐ (5/5 - Sangat Memuaskan)</option>
                                    <option value="4">⭐⭐⭐⭐ (4/5 - Memuaskan)</option>
                                    <option value="3">⭐⭐⭐ (3/5 - Sederhana)</option>
                                    <option value="2">⭐⭐ (2/5 - Kurang Memuaskan)</option>
                                    <option value="1">⭐ (1/5 - Sangat Teruk)</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Komen / Cadangan</label>
                                <textarea name="comment" rows="4" class="form-control form-control-custom" placeholder="Tulis pengalaman atau cadangan anda di sini..." required></textarea>
                            </div>
                            <button type="submit" name="submit_feedback" class="btn btn-action w-100">
                                Hantar Maklum Balas
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 5. NOTIFICATION -->
            <div class="tab-pane fade" id="notif-panel">
                <div class="card-with-bg">
                    <img src="qr/userbackground.jpg" alt="User Background" class="card-bg-img">
                    <div class="card-bg-overlay"></div>

                    <div class="card-content-inner">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-bell me-2"></i>Notifikasi Terkini</h5>
                        <?php if ($notif_query && mysqli_num_rows($notif_query) > 0): ?>
                            <?php while ($n = mysqli_fetch_assoc($notif_query)): ?>
                                <div class="notif-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 text-white"><?php echo htmlspecialchars($n['title'] ?? 'Pemberitahuan'); ?></h6>
                                        <small class="text-white-50"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></small>
                                    </div>
                                    <p class="text-white-50 small mb-0"><?php echo htmlspecialchars($n['message'] ?? ''); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="notif-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-white">Selamat Datang ke AceTime!</h6>
                                    <small class="text-white-50">Baru sahaja</small>
                                </div>
                                <p class="text-white-50 small mb-0">Terima kasih kerana memilih AceTime untuk tempahan gelanggang anda.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>