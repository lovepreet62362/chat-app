<?php
include "auth.php";

$user1 = $_SESSION['user_id'];
$user2 = $_GET['user2'];

$result = mysqli_query($conn,
"SELECT * FROM messages 
WHERE (sender_id='$user1' AND receiver_id='$user2')
   OR (sender_id='$user2' AND receiver_id='$user1')
ORDER BY id ASC");

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>