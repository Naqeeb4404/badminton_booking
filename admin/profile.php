```php
<?php
session_start();

include __DIR__ . '/../config/db.php';

/* =========================================================
   CHECK ADMIN LOGIN
   ========================================================= */
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = (int) $_SESSION['user']['id'];

$message = "";
$error = "";

/* =========================================================
   HELPER
   ========================================================= */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/* =========================================================
   UPLOAD DIRECTORY
   ========================================================= */
$uploadDir = __DIR__ . '/../uploads/';

/*
 * Create uploads folder if it does not exist.
 */
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

/* =========================================================
   UPDATE PROFILE
   ========================================================= */
if (isset($_POST['update_profile'])) {

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notif = isset($_POST['notifications']) ? 1 : 0;

    /* Basic validation */
    if ($name === '') {

        $error = "Nama tidak boleh kosong.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format e-mel tidak sah.";

    } else {

        /* -------------------------------------------------
           CHECK EMAIL DUPLICATE
           ------------------------------------------------- */
        $checkEmail = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $checkEmail,
            "si",
            $email,
            $admin_id
        );

        mysqli_stmt_execute($checkEmail);

        $emailResult = mysqli_stmt_get_result($checkEmail);

        if (mysqli_num_rows($emailResult) > 0) {

            $error = "E-mel tersebut sudah digunakan oleh pengguna lain.";

        } else {

            /* -------------------------------------------------
               UPDATE BASIC PROFILE
               ------------------------------------------------- */
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users 
                 SET name = ?, email = ?, phone = ?, sms_alerts = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssii",
                $name,
                $email,
                $phone,
                $notif,
                $admin_id
            );

            if (mysqli_stmt_execute($stmt)) {

                /* Update session */
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;

                /* -------------------------------------------------
                   PROFILE IMAGE
                   ------------------------------------------------- */
                if (
                    isset($_FILES['profile_pic']) &&
                    $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE
                ) {

                    $file = $_FILES['profile_pic'];

                    if ($file['error'] !== UPLOAD_ERR_OK) {

                        $error = "Gambar gagal dimuat naik. Kod error: " . $file['error'];

                    } else {

                        /* Maximum 5MB */
                        if ($file['size'] > 5 * 1024 * 1024) {

                            $error = "Saiz gambar terlalu besar. Maksimum 5MB.";

                        } else {

                            /* -------------------------------------------------
                               CHECK MIME TYPE
                               ------------------------------------------------- */
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mime = finfo_file($finfo, $file['tmp_name']);
                            finfo_close($finfo);

                            $allowed = [
                                'image/jpeg' => 'jpg',
                                'image/png'  => 'png',
                                'image/gif'  => 'gif',
                                'image/webp' => 'webp'
                            ];

                            if (!isset($allowed[$mime])) {

                                $error = "Format gambar tidak disokong. Gunakan JPG, PNG, GIF atau WEBP.";

                            } else {

                                $extension = $allowed[$mime];

                                /*
                                 * Unique filename
                                 */
                                $filename = 'admin_' . $admin_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

                                $target = $uploadDir . $filename;

                                /* -------------------------------------------------
                                   MOVE FILE
                                   ------------------------------------------------- */
                                if (move_uploaded_file($file['tmp_name'], $target)) {

                                    /*
                                     * Save filename into database
                                     */
                                    $imageStmt = mysqli_prepare(
                                        $conn,
                                        "UPDATE users SET profile_pic = ? WHERE id = ?"
                                    );

                                    mysqli_stmt_bind_param(
                                        $imageStmt,
                                        "si",
                                        $filename,
                                        $admin_id
                                    );

                                    if (mysqli_stmt_execute($imageStmt)) {

                                        /*
                                         * Update session if used by sidebar
                                         */
                                        $_SESSION['user']['profile_pic'] = $filename;

                                    } else {

                                        $error = "Gambar berjaya dimuat naik tetapi gagal disimpan ke database.";

                                    }

                                } else {

                                    $error = "Gagal menyimpan gambar ke folder uploads. Pastikan folder uploads boleh ditulis oleh server.";

                                }
                            }
                        }
                    }
                }

                if ($error === "") {
                    $message = "Profil berjaya dikemaskini!";
                }
            } else {

                $error = "Gagal mengemaskini profil: " . mysqli_error($conn);
            }
        }
    }
}

/* =========================================================
   CHANGE PASSWORD
   ========================================================= */
if (isset($_POST['change_password'])) {

    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $con_pass = $_POST['confirm_password'] ?? '';

    if ($old_pass === '' || $new_pass === '' || $con_pass === '') {

        $error = "Sila isi semua ruangan kata laluan.";

    } elseif ($new_pass !== $con_pass) {

        $error = "Kata laluan baharu tidak sepadan.";

    } elseif (strlen($new_pass) < 6) {

        $error = "Kata laluan baharu mestilah sekurang-kurangnya 6 aksara.";

    } else {

        $passStmt = mysqli_prepare(
            $conn,
            "SELECT password FROM users WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $passStmt,
            "i",
            $admin_id
        );

        mysqli_stmt_execute($passStmt);

        $passResult = mysqli_stmt_get_result($passStmt);
        $row = mysqli_fetch_assoc($passResult);

        if ($row && password_verify($old_pass, $row['password'])) {

            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

            $updatePass = mysqli_prepare(
                $conn,
                "UPDATE users SET password = ? WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $updatePass,
                "si",
                $hashed,
                $admin_id
            );

            if (mysqli_stmt_execute($updatePass)) {

                $message = "Kata laluan berjaya ditukar!";

            } else {

                $error = "Gagal menukar kata laluan.";
            }

        } else {

            $error = "Kata laluan semasa salah.";
        }
    }
}

/* =========================================================
   GET ADMIN DATA
   ========================================================= */
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $admin_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    die("Admin tidak dijumpai.");
}

/* =========================================================
   ACTIVITY HISTORY
   ========================================================= */
$activities = mysqli_query(
    $conn,
    "SELECT * FROM bookings ORDER BY id DESC LIMIT 5"
);

/* =========================================================
   PROFILE IMAGE
   ========================================================= */
$profileImage = "";

if (!empty($admin['profile_pic'])) {
    $profileImage = "../uploads/" . rawurlencode($admin['profile_pic']);
}

/* First letter */
$initial = strtoupper(
    substr(
        trim($admin['name'] ?? 'A'),
        0,
        1
    )
);
?>
<!DOCTYPE html>
<html lang="ms">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Profile</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<link rel="stylesheet" href="sidebar.css">

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
    --danger: #dc2626;
    --radius: 16px;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: var(--bg-main);
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--text-main);
}

/* =========================================================
   TOPBAR
   ========================================================= */

.topbar {
    min-height: 72px;
    padding: 0 30px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    background: #ffffff;
    border-bottom: 1px solid var(--border);

    position: sticky;
    top: 0;
    z-index: 100;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-form i {
    color: var(--text-muted);
}

.search-input {
    width: 250px !important;
    border: none !important;
    background: transparent !important;
    padding: 8px !important;
    outline: none;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* =========================================================
   USER PILL
   ========================================================= */

.user-pill {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 5px 10px 5px 5px;

    border: 1px solid var(--border);
    border-radius: 999px;

    background: #ffffff;
}

.user-avatar {
    width: 38px;
    height: 38px;

    border-radius: 50%;

    background: var(--primary);
    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-weight: 700;
}

/* =========================================================
   LOGOUT
   ========================================================= */

.logout-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 10px 17px;

    border-radius: 999px;

    background: var(--danger);
    color: #ffffff;

    text-decoration: none;

    font-size: 13px;
    font-weight: 700;

    transition: 0.2s;
}

.logout-btn:hover {
    background: #b91c1c;
    color: #ffffff;
    transform: translateY(-1px);
}

/* =========================================================
   CONTENT
   ========================================================= */

.content-body {
    padding: 30px;
}

.content-area {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;

    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* =========================================================
   ALERT
   ========================================================= */

.alert {
    padding: 13px 17px;

    border-radius: 10px;

    font-size: 14px;
    font-weight: 500;
}

.alert-success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* =========================================================
   PROFILE HEADER
   ========================================================= */

.profile-header-card {
    background: var(--surface);

    border: 1px solid var(--border);

    border-radius: var(--radius);

    overflow: hidden;

    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.banner {
    height: 150px;

    background:
        linear-gradient(
            135deg,
            #1e293b,
            #0f172a
        );
}

.profile-info-section {
    min-height: 105px;

    padding: 0 30px 25px;

    position: relative;

    display: flex;
    align-items: flex-end;
}

.profile-avatar-large {
    width: 105px;
    height: 105px;

    border-radius: 50%;

    border: 5px solid #ffffff;

    background: var(--primary);
    color: #ffffff;

    position: absolute;

    top: -52px;
    left: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 38px;
    font-weight: 700;

    background-size: cover;
    background-position: center;

    overflow: hidden;
}

.profile-details {
    margin-left: 125px;
    padding-top: 20px;
}

.profile-details h2 {
    margin: 0 0 5px;

    font-size: 21px;
}

.profile-details p {
    margin: 0;

    color: var(--text-muted);

    font-size: 13px;
}

/* =========================================================
   GRID
   ========================================================= */

.grid-2 {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 25px;
}

/* =========================================================
   CARD
   ========================================================= */

.card {
    background: var(--surface);

    border: 1px solid var(--border);

    border-radius: var(--radius);

    padding: 25px;

    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.card h3 {
    margin: 0 0 20px;

    display: flex;
    align-items: center;

    gap: 9px;

    font-size: 16px;
}

.card h3 i {
    color: var(--accent);
}

/* =========================================================
   FORM
   ========================================================= */

.field {
    margin-bottom: 16px;
}

.field label {
    display: block;

    margin-bottom: 7px;

    color: var(--text-muted);

    font-size: 12px;

    font-weight: 700;
}

.form-control {
    width: 100%;

    padding: 11px 14px;

    border: 1px solid var(--border);

    border-radius: 10px;

    font-family: inherit;

    font-size: 13px;

    background: #ffffff;

    color: var(--text-main);
}

.form-control:focus {
    outline: none;

    border-color: var(--accent);

    box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
}

/* =========================================================
   BUTTON
   ========================================================= */

.btn-main {
    width: 100%;

    padding: 12px 20px;

    border: none;

    border-radius: 10px;

    background: var(--primary);

    color: #ffffff;

    font-family: inherit;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s;
}

.btn-main:hover {
    background: var(--accent);
}

/* =========================================================
   NOTIFICATION
   ========================================================= */

.notification-title {
    margin: 20px 0 10px;

    font-size: 13px;

    font-weight: 700;
}

.notification-option {
    display: flex;

    align-items: center;

    gap: 9px;

    font-size: 13px;

    color: var(--text-muted);
}

.notification-option input {
    width: 16px;
    height: 16px;
}

/* =========================================================
   ACTIVITY
   ========================================================= */

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;

    padding-bottom: 10px;

    border-bottom: 1px solid var(--border);

    font-size: 13px;
}

.activity-date {
    color: var(--text-muted);

    font-size: 11px;

    white-space: nowrap;
}

.activity-status {
    font-weight: 700;
}

/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-btn {
    display: flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    width: 100%;

    padding: 12px;

    border-radius: 10px;

    background: #e5e7eb;

    color: var(--text-main);

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    transition: 0.2s;
}

.back-btn:hover {
    background: #d1d5db;

    color: var(--text-main);
}

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 900px) {

    .grid-2 {
        grid-template-columns: 1fr;
    }

    .topbar {
        padding: 0 15px;
    }

    .content-body {
        padding: 20px 15px;
    }

    .search-form {
        display: none;
    }

}

@media (max-width: 600px) {

    .topbar {
        min-height: 65px;
    }

    .user-pill {
        display: none;
    }

    .logout-btn {
        padding: 9px 13px;
    }

    .profile-info-section {
        padding-left: 20px;
        padding-right: 20px;
    }

    .profile-avatar-large {
        left: 20px;
    }

    .profile-details {
        margin-left: 110px;
    }

    .activity-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

}

</style>

</head>

<body class="admin-page">

<?php
/*
 * SIDEBAR
 */
include __DIR__ . '/sidebar.php';
?>

<!-- =====================================================
     MAIN CONTENT
     ===================================================== -->

<div class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">

        <div class="search-form">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                class="form-control search-input"
                placeholder="Type to search..."
                autocomplete="off"
            >

        </div>

        <div class="topbar-right">

            <div class="user-pill">

                <div
                    class="user-avatar"
                    <?php if ($profileImage): ?>
                        style="
                            background-image:url('<?php echo e($profileImage); ?>');
                            background-size:cover;
                            background-position:center;
                        "
                    <?php endif; ?>
                >

                    <?php if (!$profileImage): ?>
                        <?php echo e($initial); ?>
                    <?php endif; ?>

                </div>

                <div>
                    <strong>
                        <?php echo e($admin['name']); ?>
                    </strong>
                </div>

            </div>

            <!-- LOGOUT -->
            <a
                href="../auth/logout.php"
                class="logout-btn"
                onclick="return confirm('Adakah anda pasti mahu logout?');"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </header>


    <!-- CONTENT -->
    <div class="content-body">

        <div class="content-area">

            <!-- ALERT -->
            <?php if ($message): ?>

                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?php echo e($message); ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo e($error); ?>
                </div>

            <?php endif; ?>


            <!-- =================================================
                 PROFILE HEADER
                 ================================================= -->

            <div class="profile-header-card">

                <div class="banner"></div>

                <div class="profile-info-section">

                    <div
                        class="profile-avatar-large"
                        <?php if ($profileImage): ?>
                            style="
                                background-image:url('<?php echo e($profileImage); ?>');
                                background-size:cover;
                                background-position:center;
                            "
                        <?php endif; ?>
                    >

                        <?php if (!$profileImage): ?>

                            <?php echo e($initial); ?>

                        <?php endif; ?>

                    </div>


                    <div class="profile-details">

                        <h2>
                            <?php echo e($admin['name']); ?>
                        </h2>

                        <p>

                            <?php echo e($admin['email']); ?>

                            &nbsp;•&nbsp;

                            <span
                                style="
                                    text-transform:uppercase;
                                    font-weight:700;
                                    color:var(--accent);
                                "
                            >
                                <?php echo e($admin['role']); ?>
                            </span>

                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 TWO COLUMN
                 ================================================= -->

            <div class="grid-2">

                <!-- =================================================
                     EDIT PROFILE
                     ================================================= -->

                <div class="card">

                    <h3>
                        <i class="fa-solid fa-user-pen"></i>
                        Edit Profile
                    </h3>


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <div class="field">

                            <label>Nama Admin</label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo e($admin['name']); ?>"
                                required
                            >

                        </div>


                        <div class="field">

                            <label>E-mel</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo e($admin['email']); ?>"
                                required
                            >

                        </div>


                        <div class="field">

                            <label>No. Telefon</label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?php echo e($admin['phone'] ?? ''); ?>"
                            >

                        </div>


                        <div class="field">

                            <label>Gambar Profil Baharu</label>

                            <input
                                type="file"
                                name="profile_pic"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.gif,.webp,image/*"
                            >

                            <small
                                style="
                                    display:block;
                                    margin-top:6px;
                                    color:var(--text-muted);
                                    font-size:11px;
                                "
                            >
                                JPG, PNG, GIF atau WEBP. Maksimum 5MB.
                            </small>

                        </div>


                        <!-- NOTIFICATION -->

                        <div class="notification-title">

                            <i class="fa-solid fa-bell"></i>

                            Notification Settings

                        </div>


                        <label class="notification-option">

                            <input
                                type="checkbox"
                                name="notifications"
                                value="1"

                                <?php
                                echo (
                                    !empty($admin['sms_alerts']) &&
                                    $admin['sms_alerts'] == 1
                                )
                                ? 'checked'
                                : '';
                                ?>
                            >

                            Terima Notifikasi Tempahan & Bayaran

                        </label>


                        <button
                            type="submit"
                            name="update_profile"
                            class="btn-main"
                            style="margin-top:20px;"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            &nbsp; Simpan Perubahan

                        </button>

                    </form>

                </div>


                <!-- =================================================
                     RIGHT COLUMN
                     ================================================= -->

                <div
                    style="
                        display:flex;
                        flex-direction:column;
                        gap:25px;
                    "
                >

                    <!-- CHANGE PASSWORD -->

                    <div class="card">

                        <h3>

                            <i class="fa-solid fa-key"></i>

                            Change Password

                        </h3>


                        <form method="POST">

                            <div class="field">

                                <label>
                                    Kata Laluan Semasa
                                </label>

                                <input
                                    type="password"
                                    name="old_password"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="field">

                                <label>
                                    Kata Laluan Baharu
                                </label>

                                <input
                                    type="password"
                                    name="new_password"
                                    class="form-control"
                                    minlength="6"
                                    required
                                >

                            </div>


                            <div class="field">

                                <label>
                                    Sahkan Kata Laluan Baharu
                                </label>

                                <input
                                    type="password"
                                    name="confirm_password"
                                    class="form-control"
                                    minlength="6"
                                    required
                                >

                            </div>


                            <button
                                type="submit"
                                name="change_password"
                                class="btn-main"
                            >

                                <i class="fa-solid fa-lock"></i>

                                &nbsp; Tukar Kata Laluan

                            </button>

                        </form>

                    </div>


                    <!-- ACTIVITY HISTORY -->

                    <div class="card">

                        <h3>

                            <i class="fa-solid fa-clock-rotate-left"></i>

                            Activity History

                        </h3>


                        <div class="activity-list">

                            <?php if (
                                $activities &&
                                mysqli_num_rows($activities) > 0
                            ): ?>

                                <?php while (
                                    $act = mysqli_fetch_assoc($activities)
                                ): ?>

                                    <div class="activity-item">

                                        <span>

                                            Tempahan ID
                                            <strong>
                                                #<?php echo e($act['id']); ?>
                                            </strong>

                                            <br>

                                            Status:

                                            <span class="activity-status">

                                                <?php
                                                echo e(
                                                    $act['status'] ?? 'Unknown'
                                                );
                                                ?>

                                            </span>

                                        </span>


                                        <span class="activity-date">

                                            <?php
                                            echo e(
                                                $act['booking_date'] ?? '-'
                                            );
                                            ?>

                                        </span>

                                    </div>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <span
                                    style="
                                        color:var(--text-muted);
                                        font-size:13px;
                                    "
                                >
                                    Tiada aktiviti terkini.
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 BACK
                 ================================================= -->

            <a
                href="dashboard.php"
                class="back-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Kembali ke Dashboard

            </a>

        </div>

    </div>

</div>

</body>
</html>
```
