<?php 
include 'includes/db.php';
include 'includes/header.php';

$success = '';
$error = '';

$patients = mysqli_query($conn, "SELECT id, name FROM patients ORDER BY name");
$doctors = mysqli_query($conn, "SELECT id, name, specialization FROM doctors ORDER BY name");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $patient_id = mysqli_real_escape_string($conn, $_POST['patient_id']);
    $doctor_id = mysqli_real_escape_string($conn, $_POST['doctor_id']);
    $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
    $appointment_time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    if(empty($patient_id) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error = "All fields expect reason are required!";
    } else{
        $check_sql = "SELECT * FROM appointments
                      WHERE doctor_id = '$doctor_id'
                      AND appointment_date = '$appointment_date'
                      AND appointment_time = '$appointment_time'
                      AND appointment_date = '$appointment_date'
                      AND status = 'Cancelled'  ";
    
        $check_result = mysqli_query($conn, $check_sql);
        
        if(mysqli_num_rows($check_result) > 0){
            $error = "Doctor is not available at this time. Please choose another time slot.";
        } else{
            $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason)
                     VALUES ('$patient_id', '$doctor_id', '$appointment_date', '$appointment_time', '$reason')";

            if(mysqli_query($conn, $sql)) {
                // $success = "Appointment booked successfully!";
                // $_POST = array();
                $appointment_id = mysqli_insert_id($conn); // Get the ID of newly created appointment
                header("Location: print_appointment.php?id=" . $appointment_id);
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<html>
    <body>
        
    <div class="container">

     <style>
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .success {
            color: green;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Select Patient:</label>
            <select name="patient_id" required>
                <option value="">-- Select Patient --</option>
                <?php while ($patient = mysqli_fetch_assoc($patients)): ?>
                    <option value="<?php echo $patient['id']; ?>">
                        <?php echo htmlspecialchars($patient['name']); ?>
                    </option>
                <?php endwhile; ?>    
            </select>
        </div>

        <div class="form-group">
            <label>Select Doctor:</label>
            <select name="doctor_id" required>
                <option value="">-- Select Doctor --</option>
                <?php while ($doctor = mysqli_fetch_assoc($doctors)): ?>
                    <option value="<?php echo $doctor['id']; ?>">
                        <?php echo htmlspecialchars($doctor['name']); ?>
                    </option>
                <?php endwhile; ?>    
            </select>
        </div>

        <div class="form-group">
            <label>Appointment Date:</label>
            <input type="date" name="appointment_date" min="<?php echo date('Y-m-d')?>" required>
        </div>

        <div class="form-group">
            <label>Appointment Time:</label>
            <input type="time" name="appointment_time" required>
        </div>

        <div class="form-group">
            <label>Reason for Visit::</label>
            <textarea name="reason" rows="4" placeholder="Enter reason for appointment"></textarea>
        </div>

        <button type="submit">Book Appointment</button>
</form>

<a href="list_appointments.php" style="display: inline-block; margin-top: 15px; color: #007bff;"><- Back to Appointment</a>
</div>

</body>
</html>