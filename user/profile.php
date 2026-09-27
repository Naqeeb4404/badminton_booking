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

    <title>User Profile - Badminton Kampung Panji</title>

    <!-- Bootstrap 5 CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
:root{--bg:#090a0f;--panel:#141620;--panel2:#1a1c29;--border:rgba(255,255,255,.09);--text:#f8fafc;--muted:#94a3b8;--indigo:#6366f1;--purple:#a855f7;--green:#4ade80;--red:#fb7185;--yellow:#fbbf24}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;padding:0 0 65px;font-family:'Plus Jakarta Sans',sans-serif;background:#090a0f;color:var(--text);overflow-x:hidden}
body:before{content:"";position:fixed;inset:0;z-index:-2;background:radial-gradient(circle at 8% 10%,rgba(99,102,241,.22),transparent 31%),radial-gradient(circle at 92% 72%,rgba(168,85,247,.16),transparent 32%),linear-gradient(180deg,#090a0f,#0d0e16 55%,#090a0f)}
.app-container{max-width:none;margin:0;background:transparent;border-radius:0;overflow:visible;box-shadow:none;padding:0}
.custom-navbar{position:relative;height:78px;padding:10px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:rgba(9,10,15,.84);backdrop-filter:blur(18px);position:sticky;top:0;z-index:30;margin:0}
.brand-container{display:flex;align-items:center;gap:12px;text-decoration:none}.brand-logo-icon{width:53px;height:53px;display:flex;align-items:center;justify-content:center;border-radius:10px;overflow:hidden}.brand-logo-icon img{width:51px;height:51px;object-fit:contain;border-radius:9px}.brand-text{display:flex;flex-direction:column}.brand-text strong{font-size:.95rem;color:#fff;line-height:1.05}.brand-text span{font-size:.62rem;color:#a855f7;font-weight:800;letter-spacing:1.5px;margin-top:5px}
.nav-actions{display:flex;align-items:center;gap:8px}.nav-btn{height:40px;padding:0 14px;border:1px solid var(--border);border-radius:50px;background:rgba(255,255,255,.035);color:#dbe3ef;text-decoration:none;display:flex;align-items:center;gap:7px;font-size:.68rem;font-weight:800;transition:.22s}.nav-btn:hover{transform:translateY(-2px);background:rgba(255,255,255,.08);border-color:rgba(99,102,241,.25);color:#fff;box-shadow:0 7px 20px rgba(99,102,241,.1)}.nav-btn.active{background:rgba(99,102,241,.11);border-color:rgba(99,102,241,.25);color:#c4b5fd}.nav-btn.logout{border-color:rgba(251,113,133,.2);color:#fda4af;background:rgba(251,113,133,.06)}
.page-content{width:min(1120px,calc(100% - 30px));margin:0 auto;padding-top:34px}
.profile-card-header{position:relative;overflow:hidden;background:linear-gradient(135deg,rgba(25,27,41,.97),rgba(14,15,23,.95));border:1px solid var(--border);border-radius:25px;padding:28px;color:#fff;display:flex;align-items:center;gap:18px;margin-bottom:18px;box-shadow:0 22px 60px rgba(0,0,0,.28);transition:.3s}.profile-card-header:hover{transform:translateY(-2px);border-color:rgba(99,102,241,.24);box-shadow:0 28px 70px rgba(0,0,0,.34),0 0 30px rgba(99,102,241,.07)}.profile-card-header:after{content:"👤";position:absolute;right:25px;bottom:-40px;font-size:8rem;opacity:.045}.profile-avatar{width:72px;height:72px;background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;border-radius:19px;display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:800;flex-shrink:0;box-shadow:0 10px 30px rgba(99,102,241,.24)}.profile-card-header h3{color:#fff}.profile-card-header .badge{background:rgba(168,85,247,.13)!important;color:#c4b5fd!important;border:1px solid rgba(168,85,247,.2);border-radius:50px}
.alert{border-radius:14px!important;border:1px solid var(--border);font-size:.72rem}.alert-success{background:rgba(74,222,128,.08);color:#86efac;border-color:rgba(74,222,128,.17)}.alert-danger{background:rgba(251,113,133,.08);color:#fda4af;border-color:rgba(251,113,133,.17)}
.nav-pills-custom{background:rgba(20,22,32,.9);border:1px solid var(--border);border-radius:18px;padding:6px;gap:5px;margin-bottom:18px;display:flex;flex-wrap:wrap}.nav-pills-custom .nav-link{border-radius:13px;color:#94a3b8;font-weight:800;font-size:.68rem;padding:10px 17px;transition:.2s}.nav-pills-custom .nav-link:hover{color:#fff;background:rgba(255,255,255,.04)}.nav-pills-custom .nav-link.active{background:linear-gradient(135deg,rgba(99,102,241,.24),rgba(168,85,247,.19));color:#fff;box-shadow:none;border:1px solid rgba(99,102,241,.22)}
.card-with-bg{position:relative;border-radius:21px;padding:27px;box-shadow:0 18px 48px rgba(0,0,0,.22);overflow:hidden;color:#fff;margin-bottom:18px;border:1px solid var(--border);background:linear-gradient(145deg,rgba(23,25,37,.97),rgba(14,15,23,.96));transition:.28s}.card-with-bg:hover{transform:translateY(-2px);border-color:rgba(99,102,241,.22);box-shadow:0 24px 60px rgba(0,0,0,.28),0 0 26px rgba(99,102,241,.05)}.card-bg-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:1;opacity:.13}.card-bg-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(12,13,20,.82),rgba(16,13,28,.9));z-index:2}.card-content-inner{position:relative;z-index:3}.card-content-inner label,.card-content-inner .form-label,.card-content-inner .text-muted,.card-content-inner th{color:#94a3b8!important}.card-content-inner p,.card-content-inner h5,.card-content-inner td,.card-content-inner th{color:#fff!important}.card-content-inner h5{font-size:1rem}
.form-control-custom,.form-select-custom{background:rgba(255,255,255,.045);border:1px solid var(--border);border-radius:13px;padding:12px 14px;font-size:.72rem;font-weight:600;color:#fff;transition:.22s}.form-control-custom:focus,.form-select-custom:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(99,102,241,.08),0 0 22px rgba(99,102,241,.07);background:rgba(99,102,241,.06);color:#fff}.form-select-custom option{background:#171925;color:#fff}.form-control-custom::placeholder{color:#64748b}
.btn-action{background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;font-weight:800;border-radius:50px;padding:12px 24px;border:0;transition:.22s;box-shadow:0 9px 24px rgba(99,102,241,.2)}.btn-action:hover{transform:translateY(-2px);color:#fff;box-shadow:0 14px 30px rgba(99,102,241,.3)}
.history-table-container{background:rgba(12,13,20,.65);border:1px solid var(--border);border-radius:16px;overflow:hidden}.table-custom-dark{margin-bottom:0;color:#fff;background:transparent}.table-custom-dark th{background:rgba(255,255,255,.035)!important;color:#94a3b8!important;font-weight:800;text-transform:uppercase;font-size:.66rem;letter-spacing:.5px;padding:13px 16px;border-bottom:1px solid var(--border)}.table-custom-dark td{background:transparent!important;padding:13px 16px;vertical-align:middle;border-bottom:1px solid rgba(255,255,255,.05);font-size:.72rem}.table-custom-dark tbody tr:hover{background:rgba(99,102,241,.045)}.badge-status{padding:6px 10px;border-radius:50px;font-weight:800;font-size:.58rem;display:inline-flex;align-items:center;gap:5px}.badge-status.bg-success{background:rgba(74,222,128,.1)!important;color:#86efac!important;border:1px solid rgba(74,222,128,.18)}.badge-status.bg-warning{background:rgba(251,191,36,.09)!important;color:#fcd34d!important;border:1px solid rgba(251,191,36,.16)}.badge-status.bg-danger{background:rgba(251,113,133,.09)!important;color:#fda4af!important;border:1px solid rgba(251,113,133,.16)}
.notif-item{border-left:3px solid #818cf8;background:rgba(255,255,255,.035);border:1px solid var(--border);border-left:3px solid #818cf8;backdrop-filter:blur(5px);border-radius:13px;padding:14px;margin-bottom:10px;transition:.2s}.notif-item:hover{transform:translateX(3px);background:rgba(99,102,241,.05);border-color:rgba(99,102,241,.2)}
@media(max-width:800px){.custom-navbar{height:70px;padding:8px 15px}.brand-logo-icon{width:47px;height:47px}.brand-logo-icon img{width:45px;height:45px}.brand-text{display:none}.nav-btn span{display:none}.nav-btn{width:40px;padding:0;justify-content:center}.nav-actions{gap:5px}.page-content{width:min(100% - 20px,1120px);padding-top:23px}.profile-card-header{padding:21px}.profile-avatar{width:58px;height:58px;border-radius:16px}.card-with-bg{padding:20px 16px}.nav-pills-custom .nav-link{padding:9px 11px}}

/* CENTER MENU - same layout as Dashboard */
.nav-center{position:absolute;left:50%;transform:translateX(-50%);display:flex;align-items:center;justify-content:center;gap:34px;white-space:nowrap}
.nav-center a{position:relative;color:#94a3b8;text-decoration:none;font-size:.84rem;font-weight:700;transition:color .22s ease,transform .22s ease}
.nav-center a:hover{color:#fff;transform:translateY(-1px)}
.nav-center a.active{color:#f8fafc}
.nav-center a.active:after{content:"";position:absolute;left:50%;bottom:-12px;width:20px;height:2px;border-radius:20px;background:linear-gradient(90deg,#6366f1,#a855f7);transform:translateX(-50%);box-shadow:0 0 10px rgba(168,85,247,.45)}
@media(max-width:900px){.nav-center{gap:18px}.nav-center a{font-size:.72rem}}
@media(max-width:720px){.nav-center{display:none}}

</style>

</head>

<body>

    <div class="app-container">

        <!-- Header Navigation -->

        <nav class="custom-navbar">
    <a href="dashboard.php" class="brand-container">
        <div class="brand-logo-icon"><img src="../logo-badminton.png" alt="Badminton Kampung Panji"></div>
        <div class="brand-text"><strong>BADMINTON</strong><span>KAMPUNG PANJI</span></div>
    </a>
    <div class="nav-center"><a href="feedback_report.php" class="">Feedback</a><a href="message.php" class="">Message</a><a href="my_booking.php" class="">My Booking</a><a href="profile.php" class="active">Profile</a></div>
<div class="nav-actions">
<a href="dashboard.php" class="nav-btn"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
<a href="../auth/logout.php" class="nav-btn logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
</div>
</nav>
<div class="page-content">

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

                                    <h6 class="fw-bold mb-0 text-white">Selamat Datang ke Badminton Kampung Panji!</h6>

                                    <small class="text-white-50">Baru sahaja</small>

                                </div>

                                <p class="text-white-50 small mb-0">Terima kasih kerana memilih Badminton Kampung Panji untuk tempahan gelanggang anda.</p>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

    <!-- Bootstrap 5 JS Bundle -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
