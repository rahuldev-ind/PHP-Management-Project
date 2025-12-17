<?php 
require_once 'config.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);

    //validation
    if(empty($name) || empty($email) || empty($phone) || empty($password)){
        $error = 'All fields are important or required';
    } elseif($password != $confirm_password){
        $error = 'Password do not match';
    } elseif(strlen($password) < 6){
        $error = 'Password must be at least 6 characters';
    } else{
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_query);

        if($check_result->num_rows > 0){
            $error = 'Email already registered';
        } else{
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            //INSERT USER
            $insert_query = "INSERT INTO users (name, email, phone, password) VALUES ('$user', '$email', '$phone', '$hashed_password')";

            if($conn->query($insert_query)){
                $success = 'Registration successful! You can now login';
            }else{
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hotel Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }
        
        .register-container h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .back-home {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-home a {
            color: #666;
            text-decoration: none;
        }
        
        .back-home a:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>🏨 Create Account</h2>

        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Register</button>
        </form>

        <div class="login-link">
            Already have an Account? <a href="login.php">Login here</a>
        </div>

        <div class="back-home">
            <a href="index.php"><- Back to Home</a>
        </div>
    </div>
</body>
</html>