<?php
include "auth.php";

$sender = $_SESSION['user_id'];
$receiver = (int)$_POST['receiver_id'];
$message = trim($_POST['message']);

if(empty($message) || !$receiver){
    http_response_code(400);
    echo "invalid";
    exit;
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iis", $sender, $receiver, $message);
mysqli_stmt_execute($stmt);

echo "sent";
?>
