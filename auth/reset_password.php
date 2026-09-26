<?php
session_start();
include __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$userId = (int) $_SESSION['reset_user_id'];
$userEmail = $_SESSION['reset_email'] ?? '';

if (isset($_POST['reset_password'])) {

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirmPassword)) {

        $error = "Please fill in all fields.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    } else {

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "UPDATE users SET password = ? WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "si",
                $hashedPassword,
                $userId
            );

            if ($stmt->execute()) {

                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_name']);

                $success = "Password changed successfully.";

            } else {

                $error = "Failed to change password. Please try again.";

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
        Reset Password - Badminton Kampung Panji
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

        /* =================================
           MAIN CONTAINER
        ================================= */

        .reset-container {
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

        /* =================================
           LEFT
        ================================= */

        .left {
            width: 55%;

            background: #eef2f3;

            padding: 45px 55px;

            display: flex;
            flex-direction: column;

            justify-content: center;
        }

        /* =================================
           RIGHT
        ================================= */

        .right {
            width: 45%;

            background: #000;

            position: relative;

            overflow: hidden;
        }

        .right img {
            width: 100%;
            height: 100%;

            min-height: 500px;

            object-fit: cover;

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
            color: #fff;
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

        /* =================================
           BACK
        ================================= */

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

        /* =================================
           ICON
        ================================= */

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

        /* =================================
           TITLE
        ================================= */

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

            margin-bottom: 25px;
        }

        /* =================================
           EMAIL
        ================================= */

        .email-display {
            display: flex;

            align-items: center;

            gap: 12px;

            background: #fff;

            border-radius: 8px;

            padding: 12px 15px;

            margin-bottom: 22px;

            font-size: 13px;

            color: #555;
        }

        .email-icon {
            width: 34px;
            height: 34px;

            background: #111;

            color: #fff;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;
        }

        .email-content {
            overflow: hidden;
        }

        .email-content small {
            display: block;

            color: #888;

            margin-bottom: 2px;
        }

        .email-content strong {
            color: #111;

            word-break: break-word;
        }

        /* =================================
           FORM
        ================================= */

        .form-group {
            margin-bottom: 20px;
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

        .input-icon {
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

            padding: 12px 40px 12px 30px;

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

        /* =================================
           PASSWORD TOGGLE
        ================================= */

        .toggle-password {
            position: absolute;

            right: 5px;

            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #777;

            cursor: pointer;

            padding: 5px;
        }

        .toggle-password:hover {
            color: #000;
        }

        /* =================================
           PASSWORD NOTE
        ================================= */

        .password-note {
            font-size: 11px;

            color: #777;

            margin-top: 7px;
        }

        /* =================================
           ERROR
        ================================= */

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

        /* =================================
           BUTTON
        ================================= */

        .btn-reset {
            width: 100%;

            padding: 14px;

            background: #000;

            color: #fff;

            border: none;

            border-radius: 5px;

            font-size: 14px;

            font-weight: bold;

            transition: 0.3s;
        }

        .btn-reset:hover {
            background: #333;

            color: #fff;
        }

        .btn-reset i {
            margin-left: 7px;
        }

        /* =================================
           BACK LOGIN
        ================================= */

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

        /* =================================
           LOGO
        ================================= */

        .logo {
            text-align: center;

            margin-top: 20px;
        }

        .logo img {
            width: 48px;
            height: 48px;

            object-fit: cover;

            border-radius: 50%;
        }

        /* =================================
           SUCCESS
        ================================= */

        .success-wrapper {
            text-align: center;
        }

        .success-icon {
            width: 65px;
            height: 65px;

            background: #000;

            color: #fff;

            border-radius: 50%;

            display: flex;

            align-items: center;
            justify-content: center;

            margin: 0 auto 20px;

            font-size: 24px;
        }

        .success-wrapper h2 {
            font-size: 30px;

            font-weight: 800;

            margin-bottom: 10px;
        }

        .success-wrapper p {
            color: #666;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 25px;
        }

        .login-now {
            display: inline-block;

            background: #000;

            color: #fff;

            padding: 13px 28px;

            border-radius: 5px;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;
        }

        .login-now:hover {
            background: #333;

            color: #fff;
        }

        .login-now i {
            margin-left: 7px;
        }

        /* =================================
           MOBILE
        ================================= */

        @media (max-width: 768px) {

            body {
                padding: 15px;
            }

            .reset-container {
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

    <div class="reset-container">

        <!-- =========================
             LEFT SIDE
        ========================= -->

        <div class="left">

            <?php if (!empty($success)): ?>

                <!-- SUCCESS -->

                <div class="success-wrapper">

                    <div class="success-icon">

                        <i class="fa-solid fa-check"></i>

                    </div>

                    <h2>
                        Password Updated
                    </h2>

                    <p>
                        Your password has been changed successfully.
                        You can now login using your new password.
                    </p>

                    <a
                        href="login.php"
                        class="login-now"
                    >

                        Login Now

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                </div>

            <?php else: ?>

                <!-- BACK -->

                <div class="back-top">

                    <a href="forgot_password.php">

                        <i class="fa-solid fa-arrow-left"></i>

                        Back

                    </a>

                </div>

                <!-- ICON -->

                <div class="icon-box">

                    <i class="fa-solid fa-lock"></i>

                </div>

                <!-- TITLE -->

                <h1>
                    Reset Password
                </h1>

                <p class="description">
                    Create a new password for your
                    Badminton Kampung Panji account.
                </p>

                <!-- EMAIL -->

                <?php if (!empty($userEmail)): ?>

                    <div class="email-display">

                        <div class="email-icon">

                            <i class="fa-solid fa-envelope"></i>

                        </div>

                        <div class="email-content">

                            <small>
                                Resetting password for
                            </small>

                            <strong>
                                <?= htmlspecialchars($userEmail) ?>
                            </strong>

                        </div>

                    </div>

                <?php endif; ?>

                <!-- ERROR -->

                <?php if (!empty($error)): ?>

                    <div class="error-box">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>

                <!-- FORM -->

                <form method="POST">

                    <!-- NEW PASSWORD -->

                    <div class="form-group">

                        <label class="form-label">
                            New Password
                        </label>

                        <div class="input-wrapper">

                            <i
                                class="fa-solid fa-lock input-icon"
                            ></i>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                placeholder="Enter new password"
                                minlength="6"
                                required
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('password', this)"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                        <div class="password-note">
                            Password must be at least 6 characters.
                        </div>

                    </div>

                    <!-- CONFIRM PASSWORD -->

                    <div class="form-group">

                        <label class="form-label">
                            Confirm New Password
                        </label>

                        <div class="input-wrapper">

                            <i
                                class="fa-solid fa-shield-halved input-icon"
                            ></i>

                            <input
                                type="password"
                                name="confirm_password"
                                id="confirm_password"
                                class="form-control"
                                placeholder="Confirm your new password"
                                minlength="6"
                                required
                            >

                            <button
                                type="button"
                                class="toggle-password"
                                onclick="togglePassword('confirm_password', this)"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                    </div>

                    <!-- BUTTON -->

                    <button
                        type="submit"
                        name="reset_password"
                        class="btn-reset"
                    >

                        Change Password

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </form>

                <!-- LOGIN -->

                <div class="back-login">

                    Remember your password?

                    <a href="login.php">
                        Login here
                    </a>

                </div>

                <!-- LOGO -->

                <div class="logo">

                    <img
                        src="images/logobadminton.jpg"
                        alt="Badminton Kampung Panji Logo"
                    >

                </div>

            <?php endif; ?>

        </div>


        <!-- =========================
             RIGHT SIDE
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


    <script>

        function togglePassword(inputId, button) {

            const input =
                document.getElementById(inputId);

            const icon =
                button.querySelector('i');

            if (input.type === "password") {

                input.type = "text";

                icon.classList.remove("fa-eye");

                icon.classList.add("fa-eye-slash");

            } else {

                input.type = "password";

                icon.classList.remove("fa-eye-slash");

                icon.classList.add("fa-eye");

            }

        }

    </script>

</body>

</html>