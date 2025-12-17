<?php 
session_start();

$host_name = 'localhost';
$user_name = 'root';
$password = '';
$db_name = 'hotel_booking';

try {
    $conn = new mysqli($host_name, $user_name, $password, $db_name);

    if($conn->connect_error){
        die("Connection Failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

function sanitize($data){
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function isLoggedIn(){
    return isset($_SESSION['user_id']);
}

function isAdmin(){
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url){
    header("Location: $url");
    exit();
}
?>