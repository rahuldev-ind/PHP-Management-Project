<?php 
include 'config/db.php';

if($_POST){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $address, $password);
    $stmt->execute();

    echo "Registration Successful";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
    <h2>Register</h2>

    <form method="POST">
        <div class="input-box">
            <label>Name</label>
            <input type="text" name="name" placeholder="Enter your name" required>
        </div>

        <div class="input-box">
            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-box">
            <label>Phone</label>
            <input type="text" name="phone" placeholder="Enter your phone number">
        </div>

        <div class="input-box">
            <label>Address</label>
            <input type="text" name="address" placeholder="Enter your address">
        </div>

        <div class="input-box">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="login-btn">Register</button>
    </form>
</div>

</body>
</html>