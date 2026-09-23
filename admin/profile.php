<?php
session_start();
include __DIR__ . '/../config/db.php';

// CHECK ADMIN LOGIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user']['id'];
$message = "";
$error = "";

/* ================= HANDLE ACTIONS ================= */

// Edit Profile & Upload Picture
if (isset($_POST['update_profile'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $notif = isset($_POST['notifications']) ? 1 : 0;

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $filename = "admin_" . $admin_id . "_" . time() . "." . $ext;
        $target = "../uploads/" . $filename;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            mysqli_query($conn, "UPDATE users SET profile_pic = '$filename' WHERE id = '$admin_id'");
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ?, sms_alerts = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssii", $name, $email, $phone, $notif, $admin_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $message = "Profil berjaya dikemaskini!";
    } else {
        $error = "Gagal mengemaskini profil.";
    }
}

// Change Password
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $con_pass = $_POST['confirm_password'];

    $res = mysqli_query($conn, "SELECT password FROM users WHERE id = '$admin_id'");
    $row = mysqli_fetch_assoc($res);

    if ($row && password_verify($old_pass, $row['password'])) {
        if ($new_pass === $con_pass) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE id = '$admin_id'");
            $message = "Kata laluan berjaya ditukar!";
        } else {
            $error = "Kata laluan baharu tidak sepadan.";
        }
    } else {
        $error = "Kata laluan semasa salah.";
    }
}

/* ================= FETCH ADMIN DATA ================= */
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Activity History
$activities = mysqli_query($conn, "SELECT * FROM bookings ORDER BY id DESC LIMIT 5");

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Profile - UI Style</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --bg-main: #f4f6f9;
        --surface: #ffffff;
        --border: #e5e7eb;
        --text-main: #111827;
        --text-muted: #6b7280;
        --primary: #0f172a;
        --accent: #2563eb;
        --accent-light: #eff6ff;
        --radius: 16px;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        background-color: var(--bg-main);
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-main);
        padding: 30px;
    }

    /* Top Navigation Bar */
    .top-nav {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .brand {
        font-weight: 700;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .nav-links {
        display: flex;
        gap: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-muted);
        align-items: center;
    }
    .nav-links a { text-decoration: none; color: inherit; }
    .nav-links a.active { color: var(--text-main); font-weight: 600; }

    /* Main App Layout */
    .app-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 25px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Sidebar Menu */
    .sidebar {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        height: fit-content;
    }
    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 15px;
    }
    .sidebar-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        background-size: cover;
        background-position: center;
    }
    .menu-list {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .menu-item {
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-muted);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: 0.2s;
    }
    .menu-item:hover, .menu-item.active {
        background: var(--accent-light);
        color: var(--accent);
    }

    /* Content Area */
    .content-area {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    /* Banner & Profile Header Card */
    .profile-header-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .banner {
        height: 140px;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        position: relative;
    }
    .profile-info-section {
        padding: 0 30px 25px 30px;
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .profile-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid var(--surface);
        background: var(--primary);
        color: #fff;
        position: absolute;
        top: -50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 700;
        background-size: cover;
        background-position: center;
    }
    .profile-details {
        margin-left: 115px;
        padding-top: 10px;
    }
    .profile-details h2 {
        margin: 0 0 4px 0;
        font-size: 1.3rem;
    }
    .profile-details p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    /* Grid Sections */
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 25px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .card h3 {
        font-size: 1rem;
        margin-top: 0;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Form Elements */
    .field {
        margin-bottom: 15px;
    }
    .field label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-family: inherit;
        font-size: 0.9rem;
        background: #fdfdfd;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--accent);
        background: #fff;
    }

    .btn {
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        width: 100%;
        transition: 0.2s;
    }
    .btn:hover { background: var(--accent); }
    .btn-danger { background: #dc2626; }
    .btn-danger:hover { background: #b91c1c; }

    .alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 0.85rem;
        margin-bottom: 20px;
    }
    .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

    @media (max-width: 900px) {
        .app-layout { grid-template-columns: 1fr; }
        .grid-2 { grid-template-columns: 1fr; }
    }
</style>
    <link rel="stylesheet" href="sidebar.css">
</head>
<body class="admin-page">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <header class="topbar">
            <div class="search-form">
                <i class="fa-solid fa-search"></i>
                <input type="text" class="form-control search-input" placeholder="Type to search..." autocomplete="off">
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="user-pill">
                    <div class="user-avatar"><?php echo e(strtoupper(substr($admin['name'] ?? 'A', 0, 1))); ?></div>
                    <div class="fw-bold fs-7 pe-2"><?php echo e($admin['name']); ?></div>
                </div>
                <a href="../auth/logout.php" class="btn btn-danger btn-sm rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </header>

        <div class="content-body">
            <div class="content-area">


            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <!-- Header Profile Card (Banner Style) -->
            <div class="profile-header-card">
                <div class="banner"></div>
                <div class="profile-info-section">
                    <div class="profile-avatar-large" style="<?php echo !empty($admin['profile_pic']) ? 'background-image: url(\'../uploads/'.e($admin['profile_pic']).'\');' : ''; ?>">
                        <?php echo empty($admin['profile_pic']) ? e(strtoupper(substr($admin['name'] ?? 'A', 0, 1))) : ''; ?>
                    </div>
                    <div class="profile-details">
                        <h2><?php echo e($admin['name']); ?></h2>
                        <p><?php echo e($admin['email']); ?> &bull; <span style="text-transform: uppercase; font-weight: 600; color: var(--accent);"><?php echo e($admin['role']); ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Edit Profile & Notification Settings -->
            <div class="grid-2">
                <div class="card">
                    <h3><i class="fa-solid fa-user-pen"></i> Edit Profile</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="field">
                            <label>Nama Admin</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($admin['name']); ?>" required>
                        </div>
                        <div class="field">
                            <label>E-mel</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e($admin['email']); ?>" required>
                        </div>
                        <div class="field">
                            <label>No. Telefon</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($admin['phone'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label>Gambar Profil Baharu</label>
                            <input type="file" name="profile_pic" accept="image/*" class="form-control" style="padding: 7px;">
                        </div>
                        
                        <!-- Notification Settings -->
                        <div style="margin: 20px 0 10px 0; font-size: 0.85rem; font-weight: 700;"><i class="fa-solid fa-bell"></i> Notification Settings</div>
                        <div class="field" style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
                            <input type="checkbox" name="notifications" value="1" <?php echo (!empty($admin['sms_alerts']) && $admin['sms_alerts'] == 1) ? 'checked' : ''; ?>>
                            <span>Terima Notifikasi Tempahan & Bayaran</span>
                        </div>

                        <button type="submit" name="update_profile" class="btn" style="margin-top: 15px;">Simpan Perubahan</button>
                    </form>
                </div>

                <!-- Change Password & Activity History -->
                <div style="display: flex; flex-direction: column; gap: 25px;">
                    <div class="card">
                        <h3><i class="fa-solid fa-key"></i> Change Password</h3>
                        <form method="POST">
                            <div class="field">
                                <label>Kata Laluan Semasa</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="field">
                                <label>Kata Laluan Baharu</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="field">
                                <label>Sahkan Kata Laluan Baharu</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn">Tukar Kata Laluan</button>
                        </form>
                    </div>

                    <div class="card">
                        <h3><i class="fa-solid fa-clock-rotate-left"></i> Activity History</h3>
                        <div style="font-size: 0.85rem; display: flex; flex-direction: column; gap: 10px;">
                            <?php if(mysqli_num_rows($activities) > 0): ?>
                                <?php while($act = mysqli_fetch_assoc($activities)): ?>
                                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                                        <span>Tempahan ID #<?php echo $act['id']; ?> (<b><?php echo $act['status']; ?></b>)</span>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;"><?php echo $act['booking_date']; ?></span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Tiada aktiviti terkini.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Back Button -->
            <a href="dashboard.php" class="btn" style="text-align: center; text-decoration: none; background: #e5e7eb; color: var(--text-main); display: block;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>

            </div>
        </div>
    </div>

</body>
</html>