<?php

$host = getenv("MYSQLHOST");        // mysql.railway.internal
$user = getenv("MYSQLUSER");        // root
$password = getenv("MYSQLPASSWORD");
$database = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT");        // usually 3306

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Optional (good practice)
mysqli_set_charset($conn, "utf8mb4");

?>
