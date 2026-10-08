<?php

$conn = mysqli_connect("localhost", "root", "", "otp_login");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>