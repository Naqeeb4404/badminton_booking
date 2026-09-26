<?php
session_start();
include __DIR__ . '/../config/db.php';

$message = '';
$error = '';

if (isset($_POST['check_email'])) {

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } else {

        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ? LIMIT 1");

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {

                $user = $result->fetch_assoc();

                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_email'] = $user['email'];
                $_SESSION['reset_name'] = $user['name'];

                header("Location: reset_password.php");
                exit();

            } else {

                $error = "Email not found. Please check your email.";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Badminton Kampung Panji</title>

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

        .forgot-container {
            width: 100%;
            max-width: 500px;
            background: #eef2f3;
            padding: 45px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
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
            margin-bottom: 30px;
            line-height: 1.6;
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

        @media (max-width: 576px) {

            .forgot-container {
                padding: 30px 25px;
            }

            h1 {
                font-size: 27px;
            }

        }

    </style>

</head>

<body>

    <div class="forgot-container">

        <div class="icon">
            🔑
        </div>

        <h1>Forgot Password?</h1>

        <p class="description">
            Enter your registered email address to reset your password.
        </p>

        <?php if (!empty($error)): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Enter your registered email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                required
            >

            <button
                type="submit"
                name="check_email"
                class="btn-reset"
            >
                Continue
            </button>

        </form>

        <div class="back-login">

            Remember your password?
            <a href="login.php">Back to Login</a>

        </div>

    </div>

</body>

</html>