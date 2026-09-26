<?php
session_start();
include __DIR__ . '/../config/db.php';

$error = '';

if (isset($_POST['check_email'])) {

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {

        $error = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email FROM users WHERE email = ? LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {

                $user = $result->fetch_assoc();

                $_SESSION['reset_user_id'] = (int)$user['id'];
                $_SESSION['reset_email'] = $user['email'];
                $_SESSION['reset_name'] = $user['name'];

                $stmt->close();

                header("Location: reset_password.php");
                exit();

            } else {

                $error = "We couldn't find an account with that email.";

            }

            $stmt->close();

        } else {

            $error = "Something went wrong. Please try again.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Forgot Password - Badminton Kampung Panji
    </title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #111;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 20px;
        }

        /* =========================
           MAIN CONTAINER
        ========================= */

        .forgot-container {
            width: 100%;
            max-width: 950px;

            min-height: 500px;

            display: flex;

            overflow: hidden;

            border-radius: 12px;

            background: #eef2f3;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.45);
        }

        /* =========================
           LEFT SIDE
        ========================= */

        .left {
            width: 55%;

            background: #eef2f3;

            padding: 45px 55px;

            display: flex;
            flex-direction: column;

            justify-content: center;
        }

        /* =========================
           RIGHT SIDE
        ========================= */

        .right {
            width: 45%;

            background: #000;

            position: relative;

            overflow: hidden;
        }

        .right img {
            width: 100%;
            height: 100%;

            object-fit: cover;

            min-height: 500px;

            display: block;
        }

        .right-overlay {
            position: absolute;

            inset: 0;

            background: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.15),
                rgba(0, 0, 0, 0.65)
            );

            display: flex;

            align-items: flex-end;

            padding: 35px;
        }

        .right-text {
            color: white;
        }

        .right-text h2 {
            font-size: 28px;

            font-weight: bold;

            margin-bottom: 8px;
        }

        .right-text p {
            font-size: 13px;

            opacity: 0.85;

            line-height: 1.6;

            margin: 0;
        }

        /* =========================
           BACK BUTTON
        ========================= */

        .back-top {
            margin-bottom: 25px;
        }

        .back-top a {
            color: #555;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

            transition: 0.2s;
        }

        .back-top a:hover {
            color: #000;
        }

        .back-top i {
            margin-right: 6px;
        }

        /* =========================
           ICON
        ========================= */

        .icon-box {
            width: 58px;
            height: 58px;

            background: #000;

            color: #fff;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 22px;

            margin-bottom: 20px;
        }

        /* =========================
           TITLE
        ========================= */

        h1 {
            font-size: 42px;

            font-weight: 800;

            color: #111;

            margin-bottom: 10px;
        }

        .description {
            color: #666;

            font-size: 14px;

            line-height: 1.7;

            max-width: 430px;

            margin-bottom: 30px;
        }

        /* =========================
           FORM
        ========================= */

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;

            font-size: 13px;

            font-weight: bold;

            color: #222;

            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;

            left: 5px;

            top: 50%;

            transform: translateY(-50%);

            color: #777;

            font-size: 14px;
        }

        .form-control {
            width: 100%;

            border: none;

            border-bottom: 1px solid #aaa;

            border-radius: 0;

            background: transparent;

            padding: 12px 5px 12px 30px;

            font-size: 14px;

            color: #111;
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-control:focus {
            box-shadow: none;

            background: transparent;

            border-bottom-color: #000;
        }

        /* =========================
           ERROR
        ========================= */

        .error-box {
            display: flex;

            align-items: center;

            gap: 10px;

            background: #fff0f0;

            border-left: 4px solid #dc3545;

            color: #b02a37;

            padding: 11px 14px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 20px;
        }

        .error-box i {
            font-size: 14px;
        }

        /* =========================
           BUTTON
        ========================= */

        .btn-reset {
            width: 100%;

            padding: 14px;

            background: #000;

            color: #fff;

            border: none;

            border-radius: 5px;

            font-size: 14px;

            font-weight: bold;

            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background: #333;

            transform: translateY(-1px);
        }

        .btn-reset i {
            margin-left: 7px;
        }

        /* =========================
           FOOTER
        ========================= */

        .back-login {
            text-align: center;

            margin-top: 22px;

            font-size: 13px;

            color: #666;
        }

        .back-login a {
            color: #000;

            font-weight: bold;

            text-decoration: none;
        }

        .back-login a:hover {
            text-decoration: underline;
        }

        /* =========================
           LOGO
        ========================= */

        .logo {
            text-align: center;

            margin-top: 25px;
        }

        .logo img {
            width: 48px;
            height: 48px;

            object-fit: cover;

            border-radius: 50%;
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 768px) {

            body {
                padding: 15px;
            }

            .forgot-container {
                max-width: 500px;

                min-height: auto;

                display: block;
            }

            .left {
                width: 100%;

                padding: 35px 30px;
            }

            .right {
                display: none;
            }

            h1 {
                font-size: 34px;
            }

            .description {
                margin-bottom: 25px;
            }

        }

        @media (max-width: 400px) {

            .left {
                padding: 30px 22px;
            }

            h1 {
                font-size: 29px;
            }

        }

    </style>

</head>

<body>

    <div class="forgot-container">

        <!-- =========================
             LEFT CONTENT
        ========================= -->

        <div class="left">

            <!-- Back -->
            <div class="back-top">

                <a href="login.php">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back to Login

                </a>

            </div>

            <!-- Icon -->
            <div class="icon-box">

                <i class="fa-solid fa-key"></i>

            </div>

            <!-- Title -->
            <h1>
                Forgot Password?
            </h1>

            <p class="description">
                No worries. Enter the email address associated
                with your account and we'll help you reset
                your password.
            </p>

            <!-- Error -->
            <?php if (!empty($error)): ?>

                <div class="error-box">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <span>
                        <?= htmlspecialchars($error) ?>
                    </span>

                </div>

            <?php endif; ?>

            <!-- Form -->
            <form method="POST" autocomplete="off">

                <div class="form-group">

                    <label class="form-label">
                        Email Address
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your registered email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    name="check_email"
                    class="btn-reset"
                >

                    Continue

                    <i class="fa-solid fa-arrow-right"></i>

                </button>

            </form>

            <!-- Back Login -->
            <div class="back-login">

                Remember your password?

                <a href="login.php">
                    Login here
                </a>

            </div>

            <!-- Logo -->
            <div class="logo">

                <img
                    src="images/logobadminton.jpg"
                    alt="Badminton Kampung Panji Logo"
                >

            </div>

        </div>

        <!-- =========================
             RIGHT IMAGE
        ========================= -->

        <div class="right">

            <img
                src="images/badminton.jpg"
                alt="Badminton Kampung Panji"
            >

            <div class="right-overlay">

                <div class="right-text">

                    <h2>
                        Badminton Kampung Panji
                    </h2>

                    <p>
                        Your game. Your court. Your time.
                    </p>

                </div>

            </div>

        </div>

    </div>

</body>

</html>