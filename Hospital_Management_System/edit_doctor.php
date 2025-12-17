<?php 
include 'includes/db.php';
include 'includes/header.php';

$success = '';
$error = '';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM doctors WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){
        $doctor = mysqli_fetch_assoc($result);
    } else {
        header("Location: list_doctors.php");
        exit();
    }
} else{
    header("Location: list_doctors.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $experience = mysqli_real_escape_string($conn, $_POST['experience']);

    if(empty($name) || empty($specialization) || empty($phone)) {
        $error = "Name, specialization, and phone are required!";
    } else {
        $sql = "UPDATE doctors SET
                name = '$name',
                specialization = '$specialization',
                phone = '$phone',
                email = '$email', 
                experience = '$experience'
                WHERE id = '$id'";

        if(mysqli_query($conn, $sql)) {
            $success = "Doctor updated successfully!";
            $result = mysqli_query($conn, "SELECT * FROM doctors WHERE id = '$id'");
            $doctor = mysqli_fetch_assoc($result);
        } else{
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<html>
    <body>
        
    <div class="container">
     <h2>Edit Doctor</h2>
    
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
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

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo ($doctor['id']); ?>">

        <div class="form-group">
            <label>Doctor Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Specialization:</label>
            <input type="text" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
        </div>

        <div class="form-group">
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>">
        </div>

        <div class="form-group">
            <label>Years of Experience:</label>
            <input type="number" name="experience" value="<?php echo $doctor['experience']; ?>" min="0" max="50">
        </div>

        <button type="submit">Update Doctor</button>
    </form>

    <a href="list_doctors.php" style="display: inline-block; margin-top: 15px; color: #007bff;"><- Back to Doctor List</a>
</div>
</body>
</html>