<?php 
session_start();
include 'config/db.php';

if($_POST){
    $email = $_POST['email'];
    $password = $_POST['password'];

    //check customer
    $stmt = $conn->prepare("SELECT id, password FROM customers WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hash);
    $stmt->fetch();

    if($stmt->num_rows > 0 && password_verify($password, $hash)){
        $_SESSION['customer_id'] = $id;
        header("Location: customer/dashboard.php");
        exit();
    }

    echo "Invalid Login";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #e4b6b0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            width: 400px;
            padding: 50px 40px;
            border-radius: 40px;
            background: linear-gradient(to right, #1c9c8c, #5b3f8c);
            text-align: center;
            color: white;
        }

        .login-box h2 {
            font-size: 40px;
            margin-bottom: 40px;
        }

        .input-box {
            margin-bottom: 25px;
            text-align: left;
        }

        .input-box label {
            font-size: 18px;
            display: block;
            margin-bottom: 8px;
        }

        .input-box input {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 30px;
            background: #4aa3d8;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-btn:hover {
            background: #2f8ec9;
        }

        .error {
            color: #ffdede;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if(isset($error)) { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <div class="input-box">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-box">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="login-btn">Login</button>
    </form>
</div>

</body>
</html>