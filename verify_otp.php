<?php

session_start();

include "db.php";

if (!isset($_SESSION["otp_user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

$user_id = $_SESSION["otp_user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $entered_otp = trim($_POST["otp"]);

    $stmt = $conn->prepare(
        "SELECT username, otp, otp_expiry, otp_attempts
         FROM users
         WHERE id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user["otp_attempts"] >= 5) {

        $message = "Too many OTP attempts. Please login again.";

    }
    elseif (strtotime($user["otp_expiry"]) < time()) {

        $message = "OTP has expired. Please login again.";

    }
    elseif ($entered_otp !== $user["otp"]) {

        $new_attempts = $user["otp_attempts"] + 1;

        $update = $conn->prepare(
            "UPDATE users
             SET otp_attempts = ?
             WHERE id = ?"
        );

        $update->bind_param(
            "ii",
            $new_attempts,
            $user_id
        );

        $update->execute();

        $remaining = 5 - $new_attempts;

        $message = "Incorrect OTP. Attempts remaining: " . $remaining;

    }
    else {

        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $user_id;
        $_SESSION["username"] = $user["username"];

        $clear = $conn->prepare(
            "UPDATE users
             SET otp = NULL,
                 otp_expiry = NULL,
                 otp_attempts = 0
             WHERE id = ?"
        );

        $clear->bind_param("i", $user_id);
        $clear->execute();

        unset($_SESSION["otp_user_id"]);
        unset($_SESSION["otp_username"]);
        unset($_SESSION["demo_otp"]);

        header("Location: dashboard.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>🔑 OTP Verification</h1>

    <p>Enter the 6-digit OTP to continue.</p>

    <div class="demo-otp">
        <strong>Demo OTP:</strong>

        <?php
        echo htmlspecialchars($_SESSION["demo_otp"]);
        ?>

        <small>
            This is displayed only for local project testing.
        </small>
    </div>

    <?php if ($message != "") { ?>

        <div class="message error">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>

    <form method="POST">

        <label>Enter OTP</label>

        <input
            type="text"
            name="otp"
            maxlength="6"
            pattern="[0-9]{6}"
            required
        >

        <button type="submit">Verify OTP</button>

    </form>

    <p>OTP expires in 2 minutes.</p>

</div>

</body>
</html>