<?php

session_start();


if(!isset($_SESSION['user']) || $_SESSION['user']['role']!="staff"){

header("Location: ../auth/login.php");

exit();

}

?>


<h1>Staff Dashboard</h1>


<a href="../auth/logout.php">

Logout

</a>