<?php 
include 'includes/db.php';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "DELETE FROM appointments WHERE id = '$id'";

    if(mysqli_query($conn, $sql)) {
        header("Location: list_appointments.php?message=Appointment cancelled successfully");
    } else{
        header("Location: list_appointments.php?message=Error cancelling appointment");
    }
} else{
    header("Location: list_appointments.php");
}
exit();
?>