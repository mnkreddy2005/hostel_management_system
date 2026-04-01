<?php
// db_connect.php

/* Alwaysdata Production Settings */
$host = 'mysql-nithinmeruva.alwaysdata.net'; 
$user = 'nithinmeruva';               
$pass = 'meruva2005';               
$dbname = 'nithinmeruva_hms';               

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


