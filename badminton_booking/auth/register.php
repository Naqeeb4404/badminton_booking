<?php
include "../config/db.php";

if(isset($_POST['register'])){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_raw = $_POST['password'] ?? '';

    if($name === '' || $email === '' || $password_raw === ''){
        $error = "Please fill in all fields.";
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Please enter a valid email address.";
    }elseif(strlen($password_raw) < 6){
        $error = "Password must be at least 6 characters.";
    }else{
        // Check whether the email is already registered (prepared statement).
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $error = "Email already registered";
        }else{
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $name, $email, $password);

            if($stmt->execute()){
                $success = "Register Successful! You can now login.";
            }else{
                $error = "Register Failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sport Court Booking</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --accent-color: #ccff00; /* Lime Green */
            --accent-hover: #b3e600;
            --bg-dark: #0f1115;
            --card-bg: #181a20;
            --input-bg: #22252d;
            --text-light: #f8f9fa;
            --text-muted: #9a9ea9;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Top Announcement Bar */
        .top-bar {
            background-color: var(--accent-color);
            color: #000;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 6px 0;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 10;
        }

        /* Split Screen Container */
        .split-wrapper {
            min-height: calc(100vh - 33px);
            display: flex;
            flex-wrap: wrap;
        }

        /* Bahagian Kiri: Video */
        .media-section {
            background: #14161d;
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow: hidden;
            min-height: 350px;
        }

        .media-section video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        /* Gradient Overlay di atas video */
        .media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 17, 21, 0.75) 0%, rgba(15, 17, 21, 0.35) 100%);
            z-index: 2;
        }

        /* Teks Kandungan di Atas Video */
        .media-content {
            position: relative;
            z-index: 3;
            padding: 50px 30px;
            color: #fff;
            width: 100%;
        }

        .media-badge {
            background-color: var(--accent-color);
            color: #000;
            font-weight: 800;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 15px;
        }

        /* Bahagian Kanan: Borang Register */
        .form-section {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-color: var(--bg-dark);
        }

        .register-card {
            width: 100%;
            max-width: 440px;
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        /* Elements dalam Form */
        .form-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: var(--input-bg);
            border-color: var(--accent-color);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(204, 255, 0, 0.15);
        }

        .form-control::placeholder {
            color: #5a5e6b;
        }

        /* Button Accent */
        .btn-accent {
            background-color: var(--accent-color);
            color: #000;
            font-weight: 800;
            padding: 14px;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
        }

        .btn-accent:hover {
            background-color: var(--accent-hover);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(204, 255, 0, 0.25);
        }

        /* Alerts */
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            border-radius: 10px;
        }

        .alert-success {
            background-color: rgba(25, 135, 84, 0.15);
            border: 1px solid rgba(25, 135, 84, 0.3);
            color: #51cf66;
            border-radius: 10px;
        }

        a.login-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 700;
        }

        a.login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- Bar Pengumuman Atas -->
    <div class="top-bar text-center">
        <i class="fa-solid fa-bolt me-1"></i> EMPOWERING YOUR FITNESS JOURNEY — JOIN OUR CLUB TODAY
    </div>

    <div class="container-fluid p-0">
        <div class="row g-0 split-wrapper">
            
            <!-- BAHAGIAN KIRI: VIDEO -->
            <div class="col-lg-7 col-12 media-section">
                <div class="media-overlay"></div>

                <video autoplay loop muted playsinline>
                    <source src="video/videosport.mp4" type="video/mp4">
                    Browser anda tidak menyokong tag video.
                </video>

                <!-- Teks Hiasan di atas Video -->
                <div class="media-content max-w-lg">
                    <span class="media-badge">
                        <i class="fa-solid fa-trophy me-1"></i> Sport Court Booking
                    </span>
                    <h1 class="display-4 fw-bold mb-3">WITNESS THE POWER</h1>
                    <p class="lead text-white-50">
                        The platform that turns aspirations into accomplishments. Join now and unleash your potential in the world of badminton.
                    </p>
                </div>
            </div>

            <!-- BAHAGIAN KANAN: BORANG REGISTER -->
            <div class="col-lg-5 col-12 form-section">
                <div class="register-card">
                    
                    <div class="mb-4 text-center text-lg-start">
                        <h2 class="fw-bold mb-1">Create Account</h2>
                        <!-- Diubah ke warna putih (text-white) -->
                        <p class="text-white small">Enter your details to register</p>
                    </div>

                    <!-- Paparan Mesej PHP -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger text-center p-2 mb-4 small">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($success)): ?>
                        <div class="alert alert-success text-center p-2 mb-4 small">
                            <i class="fa-solid fa-circle-check me-1"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>

                        <button type="submit" name="register" class="btn btn-accent w-100">
                            Create Account <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>

                    </form>

                    <!-- Diubah ke warna putih (text-white) -->
                    <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25 text-white small">
                        Already have account? 
                        <a href="login.php" class="login-link">Login</a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>