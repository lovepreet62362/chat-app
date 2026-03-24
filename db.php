<?php
$conn = mysqli_connect("localhost", "root", "", "chat_app");

if (!$conn) {
    die("DB failed");
}

session_start();
?>