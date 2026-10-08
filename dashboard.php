<?php

session_start();

if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>🎉 Login Successful</h1>

    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

    <div class="success-box">

        <h3>🔐 Authentication Successful</h3>

        <p>
            Your username and password were verified successfully.
        </p>

        <p>
            Your OTP was also verified successfully.
        </p>

    </div>

    <a href="logout.php" class="btn logout">
        Logout
    </a>

</div>

</body>
</html>