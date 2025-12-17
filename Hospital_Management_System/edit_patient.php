<?php 
include 'includes/db.php';

$success = '';
$error = '';

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $sql = "SELECT * FROM patients WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1) {
        $patient = mysqli_fetch_assoc($result);
    } else{
        header("Location: list_patients.php");
        exit();
    }
} else{
    header("Location: list_patients.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
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
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0b7dda;
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
        .link {
            margin-top: 20px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h2>Edit Patient</h2>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $patient['id']; ?>">
        
        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Age:</label>
            <input type="number" name="age" value="<?php echo $patient['age']; ?>" min="1" max="150" required>
        </div>
        
        <div class="form-group">
            <label>Gender:</label>
            <select name="gender" required>
                <option value="Male" <?php if($patient['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if($patient['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                <option value="Other" <?php if($patient['gender'] == 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Contact:</label>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($patient['contact']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Address:</label>
            <textarea name="address" rows="4"><?php echo htmlspecialchars($patient['address']); ?></textarea>
        </div>
        
        <button type="submit">Update Patient</button>
    </form>
    
    <div class="link">
        <a href="list_patients.php">← Back to Patient List</a>
    </div>
</body>
</html>