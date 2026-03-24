<?php
include "db.php";

if(!isset($_SESSION['user_id'])){
    die("unauthorized");
}
?>