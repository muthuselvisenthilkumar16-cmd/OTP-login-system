<?php

session_start();

include "db.php";

$message = "";

if (isset($_GET["registered"])) {
    $message = "Registration successful. Please login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, username, password
         FROM users
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $otp = str_pad(
                random_int(0, 999999),
                6,
                "0",
                STR_PAD_LEFT
            );

            $expiry = date(
                "Y-m-d H:i:s",
                time() + 120
            );

            $update = $conn->prepare(
                "UPDATE users
                 SET otp = ?, otp_expiry = ?, otp_attempts = 0
                 WHERE id = ?"
            );

            $update->bind_param(
                "ssi",
                $otp,
                $expiry,
                $user["id"]
            );

            $update->execute();

            $_SESSION["otp_user_id"] = $user["id"];
            $_SESSION["otp_username"] = $user["username"];

            /*
             * Local development only.
             * In a real system this OTP would be sent
             * through email/SMS.
             */
            $_SESSION["demo_otp"] = $otp;

            header("Location: verify_otp.php");
            exit();

        } else {

            $message = "Incorrect password.";

        }

    } else {

        $message = "Username not found.";

    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>🔐 Login</h1>

    <?php if ($message != "") { ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

    </form>

    <p>Don't have an account?</p>

    <a href="register.php">Create Account</a>

</div>

</body>
</html>