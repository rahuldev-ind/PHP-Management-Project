<?php
// Note: This requires TCPDF library
// Install via: composer require tecnickcom/tcpdf
// For now, this is a placeholder for future PDF implementation

include 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: list_appointments.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch appointment details
$sql = "SELECT 
            a.*, 
            p.name as patient_name, 
            p.age as patient_age,
            p.gender as patient_gender,
            p.contact as patient_contact,
            d.name as doctor_name, 
            d.specialization,
            d.phone as doctor_phone
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = '$id'";

$result = mysqli_query($conn, $sql);
$appointment = mysqli_fetch_assoc($result);

// For now, redirect to print page
// Later you can implement TCPDF here
header("Location: print_appointment.php?id=" . $id);
exit();
?>
