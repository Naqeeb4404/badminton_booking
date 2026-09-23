<?php
session_start();

include "../config/db.php";

if(isset($_POST['login'])){

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE email='$email'";

$result = mysqli_query($conn,$query);

$user = mysqli_fetch_assoc($result);


if($user){

    if(password_verify($password, $user['password'])){

        $_SESSION['user'] = $user;

        echo "Login Successful";

        // redirect contoh
        // header("Location: ../index.php");

    }else{

        echo "Wrong Password";

    }

}else{

    echo "Email not found";

}

}

?>

$check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");


if(mysqli_num_rows($check)>0){

    echo "
    <div class='alert alert-danger'>
    Email already registered
    </div>";

}else{

    mysqli_query($conn,$query);

    echo "
    <div class='alert alert-success'>
    Register Successful
    </div>";

}