<?php 
$server_name = "localhost";
$user_name = "root";
$password = "";
$database_name = "hospital_db";

//create a connection
$conn = mysqli_connect($server_name, $user_name, $password, $database_name);

//check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>