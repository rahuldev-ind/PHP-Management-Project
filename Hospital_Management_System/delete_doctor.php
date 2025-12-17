<?php 
include 'includes/db.php';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments WHERE doctor_id = '$id'");
    $count = mysqli_fetch_assoc($check)['count'];

    if($count > 0){
        header("Location: list_doctors.php?error= Cannot delete doctor with existing appointments");
        exit();
    }

    $sql = "DELETE FROM doctors WHERE id = '$id'";

    if(mysqli_query($conn, $sql)) {
        header("Location: list_doctors.php?message=Doctor deleted successfully");
    } else{
        header("Location: list_doctors.php?error=Error deleting doctor");
    }
} else{
    header("Location: list_doctors.php");
}
exit();
?>