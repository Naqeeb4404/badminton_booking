<?php
session_start();
include __DIR__ . '/../config/db.php';
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user = $_SESSION['user'];
$name = trim($user['name'] ?? '');
$email = trim($user['email'] ?? '');
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? $name);
    $email = trim($_POST['email'] ?? $email);
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('INSERT INTO messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())');
        if ($stmt) {
            $stmt->bind_param('sss', $name, $email, $message);
            if ($stmt->execute()) {
                $success = 'Your message has been sent to the admin.';
                $_POST['message'] = '';
            } else {
                $error = 'Message could not be sent. Please try again.';
            }
            $stmt->close();
        } else {
            $error = 'Message could not be sent. Please check the messages table.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Message - Badminton Kampung Panji</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#080b10;--panel:#10151d;--border:#252b35;--text:#f5f7fb;--muted:#9ba8bb;--purple:#8b5cf6;--red:#ff6268}
*{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--text);font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh}
.topbar{height:82px;border-bottom:1px solid #1d222b;display:flex;align-items:center;justify-content:space-between;padding:0 4.5%;position:sticky;top:0;background:#080b10;z-index:10}
.brand{display:flex;align-items:center;gap:14px;text-decoration:none;color:#fff}.logo{width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#6658ff,#9c46f2);display:grid;place-items:center;font-size:23px;box-shadow:0 8px 22px rgba(112,82,255,.25)}
.brand b{display:block;font-size:18px}.brand small{display:block;color:#b65cff;text-transform:uppercase;letter-spacing:2px;font-weight:800;margin-top:4px}
.nav{display:flex;gap:30px;align-items:center}.nav a{color:#a9b4c7;text-decoration:none;font-weight:700}.nav a:hover,.nav a.active{color:#fff}
.logout{border:1px solid #68252b;color:#ff7077;padding:11px 22px;border-radius:999px;text-decoration:none;font-weight:800}.logout:hover{background:#321317;color:#fff}
.wrap{max-width:900px;margin:65px auto;padding:0 22px}.title{font-size:42px;font-weight:800;margin-bottom:10px}.subtitle{color:var(--muted);font-size:17px;margin-bottom:30px}
.card{background:var(--panel);border:1px solid var(--border);border-radius:24px;padding:30px;color:var(--text);box-shadow:0 18px 50px rgba(0,0,0,.2)}
label{font-weight:700;margin-bottom:9px}.form-control{background:#0b0f15!important;border:1px solid #2b3340!important;color:#fff!important;border-radius:13px!important;padding:13px 15px!important}.form-control:focus{box-shadow:0 0 0 3px rgba(139,92,246,.16)!important;border-color:#7557e8!important}.form-control::placeholder{color:#667085}.readonly{color:#c5cede!important}
textarea.form-control{min-height:170px;resize:vertical}.send{border:0;background:linear-gradient(135deg,#6558ff,#9448ef);color:#fff;border-radius:13px;padding:13px 22px;font-weight:800;transition:.18s}.send:hover{transform:translateY(-1px);filter:brightness(1.08)}
.alert{border-radius:13px}.back{color:#a9b4c7;text-decoration:none;font-weight:700}.back:hover{color:#fff}
@media(max-width:800px){.nav{display:none}.topbar{padding:0 20px}.wrap{margin-top:40px}.title{font-size:32px}.card{padding:22px}}
</style>
</head>
<body>
<header class="topbar">
<a class="brand" href="dashboard.php"><div class="logo"><i class="fa-solid fa-feather"></i></div><div><b>BADMINTON</b><small>Kampung Panji</small></div></a>
<nav class="nav">
<a href="feedback_report.php">Feedback</a>
<a href="message.php" class="active">Message</a>
<a href="my_booking.php">My Booking</a>
<a href="profile.php">Profile</a>
</nav>
<a class="logout" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Log Out</a>
</header>
<main class="wrap">
<a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
<h1 class="title mt-4">Message & Questions</h1>
<p class="subtitle">Send your question to the admin. The message will appear in the admin Message page.</p>
<div class="card">
<?php if ($success !== ''): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST" action="">
<div class="mb-4"><label for="name">Name</label><input class="form-control readonly" id="name" name="name" value="<?= htmlspecialchars($name) ?>" readonly></div>
<div class="mb-4"><label for="email">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email" required></div>
<div class="mb-4"><label for="message">Your Message</label><textarea class="form-control" id="message" name="message" placeholder="Type your question here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea></div>
<button class="send" type="submit"><i class="fa-solid fa-paper-plane me-2"></i>Send Message</button>
</form>
</div>
</main>
</body>
</html>
