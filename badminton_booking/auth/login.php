<?php
session_start();
include "../config/db.php";

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){

            $_SESSION['user'] = $user;

            if(isset($_GET['return']) && $_GET['return'] === 'booking' && $user['role'] === 'user'){
                $q = http_build_query([
                    'date' => $_GET['date'] ?? '',
                    'time' => $_GET['time'] ?? '',
                    'court_id' => $_GET['court_id'] ?? '',
                    'confirm' => 1
                ]);
                header("Location: ../booking.php?" . $q);
            }elseif($user['role'] == "admin"){
                header("Location: ../admin/dashboard.php");
            }elseif($user['role'] == "staff"){
                header("Location: ../staff/dashboard.php");
            }else{
                header("Location: ../user/dashboard.php");
            }

            exit();

        }else{
            $error = "Kata Laluan Salah";
        }

    }else{
        $error = "Emel Tidak Dijumpai";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log Masuk Gelanggang Sukan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
    height: 100vh;
}

.container-login {
    width: 1000px;
    height: 650px;
    display: flex;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

.left {
    width: 45%;
    background: #eef2f3;
    padding: 50px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.right {
    width: 55%;
    background: #000;
}

.right img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

h1 {
    font-size: 55px;
    margin-bottom: 30px;
    font-weight: bold;
}

label {
    font-size: 14px;
    margin-bottom: 5px;
    display: block;
}

.form-control {
    border: none;
    border-bottom: 1px solid #bbb;
    border-radius: 0;
    background: transparent;
    margin-bottom: 25px;
}

.form-control:focus {
    box-shadow: none;
    border-color: #000;
    background: transparent;
}

.btn-login {
    width: 100%;
    padding: 14px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 5px;
    margin-top: 15px;
    transition: 0.3s;
}

.btn-login:hover {
    background: #333;
}

.register {
    margin-top: 20px;
    text-align: center;
}

.logo {
    font-size: 60px;
    opacity: .2;
    margin-top: 20px;
    text-align: center;
}

</style>

</head>

<body>

<div class="container-login">

    <!-- Bahagian Kiri (Borang Login) -->
    <div class="left">

        <h1>Login ✧</h1>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Email</label>
            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="Masukkan Email"
                required>

            <label>Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Masukkan Kata Laluan"
                required>

            <button
                type="submit"
                name="login"
                class="btn-login">
                Login
            </button>

            <div class="register">
                Belum ada akaun? <a href="register.php">Daftar</a>
            </div>

          <div class="logo">
    <img src="images/logobadminton.jpg" 
         alt="logobadminton" 
         class="logobadminton-img"
         width="70">
</div>
        </form>

    </div>

    <!-- Bahagian Kanan (Gambar Badminton) -->
    <div class="right">
        <!-- Pastikan nama fail ini padan dengan nama sebenar (cth: badminton.jpg atau badminton.png) -->
        <img src="images/badminton.jpg" alt="Badminton" class="badminton-img">
    </div>

</div>

</body>
</html>