<?php 
include 'includes/db.php';
include 'includes/header.php';

$success = '';
$error = '';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT a.*, p.name as patient_name, d.name as doctor_name
            FROM appointments a JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id WHERE a.id = '$id'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1) {
        $appointment = mysqli_fetch_assoc($result);
    } else {
        header("Location: list_appointments.php");
        exit();
    }
} else {
    header("Location: list_appointments.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE appointments SET status = '$status' WHERE id = '$id'";

    if(mysqli_query($conn, $sql)) {
        header("Location: list_appointments.php?message=Appointment status updated successfully");
        exit();
    } else{
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<html>
    <body>
        
    <div class="container">
        <h2>Update Appointment Status</h2>

        <style>
        .info-box {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }
        .info-box p {
            margin: 8px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #2196F3;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>

    <div class="info-box">
        <p><strong>Patient:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
        <p><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></p>
        <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></p>
        <p><strong>Current Status:</strong> <?php echo $appointment['status']; ?></p>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">

        <div class="form-group">
            <label>Update Status:</label>
            <select name="status" required>
                <option value="Pending" <?php if($appointment['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="Confirmed" <?php if($appointment['status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                <option value="Completed" <?php if($appointment['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                <option value="Cancelled" <?php if($appointment['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>
        </div>

        <button type="submit">Update Status</button>
    </form>

    <a href="list_appointments.php" style="display: inline-block; margin-top: 15px; color: #007bff;">← Back to Appointments</a>
    </div>
    </body>
</html>