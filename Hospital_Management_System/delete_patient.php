<?php 
include 'includes/db.php';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "DELETE FROM patients WHERE id = '$id'";

    if(mysqli_query($conn, $sql)){
        header("Location: list_patients.php?message=Patient deleted successfully");
        exit();
    } else{
        header("Location: list_patients.php?error=Error deleting patient");
        exit();
    }
} else{
    header("Location: list_patients.php");
    exit();
}
?>