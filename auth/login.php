<?php
session_start();
include __DIR__ . '/../config/db.php';

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");

    if ($stmt) {

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                $_SESSION['user'] = $user;


                // ==========================================
                // RETURN KE BOOKING
                // ==========================================

                if (
                    isset($_GET['return']) &&
                    $_GET['return'] === 'booking' &&
                    $user['role'] === 'user'
                ) {

                    $q = http_build_query([
                        'date' => $_GET['date'] ?? '',
                        'time' => $_GET['time'] ?? '',
                        'duration' => $_GET['duration'] ?? 1,
                        'court_id' => $_GET['court_id'] ?? '',
                        'confirm' => 1
                    ]);

                    header("Location: ../booking.php?" . $q);
                    exit();
                }


                // ==========================================
                // ADMIN
                // ==========================================

                elseif ($user['role'] === "admin") {

                    header("Location: ../admin/dashboard.php");
                    exit();
                }


                // ==========================================
                // STAFF
                // ==========================================

                elseif ($user['role'] === "staff") {

                    header("Location: ../staff/dashboard.php");
                    exit();
                }


                // ==========================================
                // USER
                // ==========================================

                else {

                    header("Location: ../user/dashboard.php");
                    exit();
                }


            } else {

                $error = "Kata Laluan Salah";
            }

        } else {

            $error = "Emel Tidak Dijumpai";
        }

        $stmt->close();

    } else {

        $error = "Ralat sistem. Sila cuba lagi.";
    }
}

?>

<!DOCTYPE html>
<html lang="ms">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login - Badminton Kampung Panji
    </title>


    <!-- Bootstrap -->
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

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

            padding: 20px;
        }


        /* ==========================================
           LOGIN CONTAINER
        ========================================== */

        .container-login {

            width: 100%;

            max-width: 950px;

            display: flex;

            overflow: hidden;

            border-radius: 12px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.4);

            background: #eef2f3;
        }


        /* ==========================================
           LEFT SIDE
        ========================================== */

        .left {

            width: 50%;

            background: #eef2f3;

            padding: 40px 50px;

            display: flex;

            flex-direction: column;

            justify-content: center;
        }


        /* ==========================================
           RIGHT SIDE
        ========================================== */

        .right {

            width: 50%;

            background: #000;
        }


        .right img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            min-height: 450px;
        }


        /* ==========================================
           TITLE
        ========================================== */

        h1 {

            font-size: 45px;

            margin-bottom: 25px;

            font-weight: bold;
        }


        /* ==========================================
           LABEL
        ========================================== */

        label {

            font-size: 14px;

            margin-bottom: 5px;

            display: block;
        }


        /* ==========================================
           INPUT
        ========================================== */

        .form-control {

            border: none;

            border-bottom:
                1px solid #bbb;

            border-radius: 0;

            background: transparent;

            margin-bottom: 20px;
        }


        .form-control:focus {

            box-shadow: none;

            border-color: #000;

            background: transparent;
        }


        /* ==========================================
           LOGIN BUTTON
        ========================================== */

        .btn-login {

            width: 100%;

            padding: 14px;

            background: #000;

            color: #fff;

            border: none;

            border-radius: 5px;

            margin-top: 10px;

            transition: 0.3s;

            font-weight: bold;
        }


        .btn-login:hover {

            background: #333;

            color: #fff;
        }


        /* ==========================================
           FORGOT PASSWORD
        ========================================== */

        .forgot-password {

            display: block;

            text-align: right;

            margin-top: -12px;

            margin-bottom: 10px;

            font-size: 13px;

            color: #111;

            font-weight: bold;

            text-decoration: none;
        }


        .forgot-password:hover {

            text-decoration: underline;

            color: #555;
        }


        /* ==========================================
           REGISTER
        ========================================== */

        .register {

            margin-top: 20px;

            text-align: center;

            font-size: 14px;
        }


        .register a {

            color: #000;

            font-weight: bold;

            text-decoration: none;
        }


        .register a:hover {

            text-decoration: underline;
        }


        /* ==========================================
           LOGO
        ========================================== */

        .logo {

            margin-top: 15px;

            text-align: center;
        }


        .logobadminton-img {

            width: 60px;

            height: 60px;

            object-fit: cover;

            border-radius: 50%;
        }


        /* ==========================================
           ERROR
        ========================================== */

        .alert-danger {

            font-size: 14px;

            border-radius: 8px;

            margin-bottom: 20px;
        }


        /* ==========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 768px) {

            .container-login {

                flex-direction: column;

                max-width: 100%;
            }


            .left {

                width: 100%;

                padding: 30px 25px;
            }


            .right {

                display: none;
            }


            h1 {

                font-size: 35px;
            }

        }

    </style>

</head>


<body>


    <div class="container-login">


        <!-- ==========================================
             LEFT - LOGIN FORM
        ========================================== -->

        <div class="left">


            <h1>
                Login ✧
            </h1>


            <!-- ERROR MESSAGE -->

            <?php if (isset($error)): ?>

                <div class="alert alert-danger py-2">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <form
                method="POST"
            >


                <!-- EMAIL -->

                <label>
                    Email
                </label>


                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Masukkan Email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >



                <!-- PASSWORD -->

                <label>
                    Password
                </label>


                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Masukkan Kata Laluan"
                    required
                >



                <!-- FORGOT PASSWORD -->

                <a
                    href="forgot_password.php"
                    class="forgot-password"
                >
                    Forgot Password?
                </a>



                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    name="login"
                    class="btn-login"
                >

                    Login

                </button>



                <!-- REGISTER -->

                <div class="register">

                    Belum ada akaun?

                    <a href="register.php">

                        Daftar

                    </a>

                </div>



                <!-- LOGO -->

                <div class="logo">

                    <img
                        src="images/logobadminton.jpg"
                        alt="Logo Badminton"
                        class="logobadminton-img"
                    >

                </div>


            </form>


        </div>



        <!-- ==========================================
             RIGHT - BADMINTON IMAGE
        ========================================== -->

        <div class="right">

            <img
                src="images/badminton.jpg"
                alt="Badminton"
                class="badminton-img"
            >

        </div>


    </div>


</body>

</html>