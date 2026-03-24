<?php
session_start();

// If user already logged in → go to chat
if(isset($_SESSION['user_id'])){
    header("Location: chat.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nexus Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body{
            margin:0;
            font-family: Arial;
            background: #0b1016;
            color:white;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .box{
            text-align:center;
            background:#111827;
            padding:40px;
            border-radius:12px;
        }

        h1{
            margin-bottom:20px;
        }

        a{
            display:block;
            margin:10px;
            padding:12px;
            text-decoration:none;
            color:white;
            border-radius:8px;
            background:#2563eb;
        }

        a:hover{
            background:#1d4ed8;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Nexus Chat</h1>

    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</div>

</body>
</html>