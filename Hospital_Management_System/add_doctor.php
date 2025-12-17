<?php
include 'includes/db.php';
include 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);
    
    if (empty($name) || empty($specialization) || empty($phone)) {
        $error = "Name, specialization, and phone are required!";
    } else {
        $sql = "INSERT INTO doctors (name, specialization, phone, email, experience) 
                VALUES ('$name', '$specialization', '$phone', '$email', '$experience')";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Doctor added successfully!";
            $_POST = array();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<div class="container">
    <h2>Add New Doctor</h2>
    
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input, select {
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
        button:hover {
            background-color: #45a049;
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
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Doctor Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label>Specialization:</label>
            <input type="text" name="specialization" placeholder="e.g., Cardiologist, Pediatrician" required>
        </div>
        
        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="phone" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email">
        </div>
        
        <div class="form-group">
            <label>Years of Experience:</label>
            <input type="number" name="experience" min="0" max="50">
        </div>
        
        <button type="submit">Add Doctor</button>
    </form>
    
    <a href="list_doctors.php" class="back-link">← Back to Doctor List</a>
</div>

</body>
</html>
