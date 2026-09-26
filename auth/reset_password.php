<?php
session_start();
include __DIR__ . '/../config/db.php';

$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| Check Reset Session
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$userId = (int) $_SESSION['reset_user_id'];
$userEmail = $_SESSION['reset_email'] ?? '';

/*
|--------------------------------------------------------------------------
| Reset Password
|--------------------------------------------------------------------------
*/

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

        // Hash password before saving
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

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

                /*
                |--------------------------------------------------------------------------
                | Clear Reset Session
                |--------------------------------------------------------------------------
                */

                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_name']);

                $success = "Password changed successfully.";

            } else {

                $error = "Failed to change password. Please try again.";

            }

            $stmt->close();

        } else {

            $error = "System error. Please try again.";

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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
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

        .reset-container {
            width: 100%;
            max-width: 500px;

            background: #eef2f3;

            padding: 45px;

            border-radius: 12px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .icon {
            width: 70px;
            height: 70px;

            background: #000;
            color: #fff;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 20px;

            font-size: 30px;
        }

        h1 {
            text-align: center;

            font-size: 32px;

            font-weight: bold;

            margin-bottom: 10px;
        }

        .description {
            text-align: center;

            color: #666;

            font-size: 14px;

            margin-bottom: 25px;

            line-height: 1.6;
        }

        .email-display {
            background: #fff;

            border-radius: 8px;

            padding: 12px 15px;

            text-align: center;

            font-size: 14px;

            margin-bottom: 25px;

            color: #333;
        }

        .email-display strong {
            color: #000;
        }

        label {
            font-size: 14px;

            font-weight: bold;

            margin-bottom: 8px;

            display: block;
        }

        .form-control {
            border: none;

            border-bottom: 1px solid #aaa;

            border-radius: 0;

            background: transparent;

            padding: 12px 5px;

            margin-bottom: 20px;
        }

        .form-control:focus {
            box-shadow: none;

            border-color: #000;

            background: transparent;
        }

        .btn-reset {
            width: 100%;

            padding: 14px;

            background: #000;

            color: #fff;

            border: none;

            border-radius: 5px;

            font-weight: bold;

            transition: 0.3s;
        }

        .btn-reset:hover {
            background: #333;

            color: #fff;
        }

        .back-login {
            text-align: center;

            margin-top: 25px;

            font-size: 14px;
        }

        .back-login a {
            color: #000;

            font-weight: bold;

            text-decoration: none;
        }

        .back-login a:hover {
            text-decoration: underline;
        }

        .alert {
            font-size: 14px;

            border-radius: 8px;
        }

        .password-note {
            font-size: 12px;

            color: #777;

            margin-top: -12px;

            margin-bottom: 20px;
        }

        @media (max-width: 576px) {

            .reset-container {
                padding: 30px 25px;
            }

            h1 {
                font-size: 27px;
            }

        }

    </style>

</head>

<body>

    <div class="reset-container">

        <div class="icon">
            🔒
        </div>

        <h1>
            Reset Password
        </h1>

        <?php if (!empty($success)): ?>

            <div class="alert alert-success text-center">

                <?= htmlspecialchars($success) ?>

                <br><br>

                <a
                    href="login.php"
                    class="btn btn-dark btn-sm px-4"
                >
                    Login Now
                </a>

            </div>

        <?php else: ?>

            <p class="description">
                Create a new password for your account.
            </p>

            <?php if (!empty($userEmail)): ?>

                <div class="email-display">

                    Resetting password for:

                    <br>

                    <strong>
                        <?= htmlspecialchars($userEmail) ?>
                    </strong>

                </div>

            <?php endif; ?>

            <?php if (!empty($error)): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>

            <form method="POST">

                <label>
                    New Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter new password"
                    minlength="6"
                    required
                >

                <div class="password-note">
                    Password must be at least 6 characters.
                </div>

                <label>
                    Confirm New Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    placeholder="Confirm new password"
                    minlength="6"
                    required
                >

                <button
                    type="submit"
                    name="reset_password"
                    class="btn-reset"
                >
                    Change Password
                </button>

            </form>

            <div class="back-login">

                <a href="login.php">
                    Back to Login
                </a>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>