<?php 
require_once 'config.php';

if(!isLoggedIn()){
    redirect('login.php');
}

$error = '';
$success = '';

$type_id = isset($_GET['type_id']) ? (int)$_GET['type_id']: 0;
$query = "SELECT * FROM room_types WHERE id = $type_id";
$result = $conn->query($query);

if($result->num_rows === 0){
    redirect('index.php');
}

$room_type = $result->fetch_assoc();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $check_in = sanitize($_POST['check_in']);
    $check_out = sanitize($_POST['check_out']);
    $guests = (int)$_POST['guests'];
    $special_requests = sanitize($_POST['special_requests']);

    if(strtotime($check_in) < strtotime(date('Y-m-d'))){
        $error = 'Check in date cannot be in the past';
    }
    elseif(strtotime($check_out) <= strtotime($check_in)){
        $error = 'Check out date must be after check in date';
    }
    elseif($guests > $room_type['capacity']){
        $error = 'Number of guests exceeds room capacity';
    }
    else{
        $available_query = "SELECT r.id FROM rooms r WHERE r.room_type_id = $type_id
                        AND r.status = 'available' AND r.id NOT IN (SELECT room_id FROM bookings WHERE status != 'cancelled' AND (
                        (check_in <= '$check_in' AND check_out > '$check_in') OR
                        (check_in < '$check_out' AND check_out >= '$check_out') OR
                        (check_in >= '$check_in' AND check_out <= '$check_out')
                        )) LIMIT 1";

        $available_query = $conn->query($available_query);

        if(!$available_result || $available_result->num_rows === 0){
            $error = 'No rooms available for the selected dates';
        }else{
            $available_room = $available_result->fetch_assoc();
            $room_id = $available_room['id'];

            //Calculated total price
            $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
            $total_price = $days * $room_type['price_per_night'];

            $user_id = $_SESSION['user_id'];

            $insert_query = "INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_price, special_requests, status) 
                            VALUES ($user_id, $room_id, '$check_in', '$check_out', $guests, $total_price, '$special_requests', 'confirmed')";

            if($conn->query($insert_query)){
                $booking_id = $conn->insert_id;

                $payment_query = "INSERT INTO payments (booking_id, amount, payment_method, payment_status)
                                VALUES ($booking_id, $total_price, 'credit_card', 'completed')";

                $conn->query($payment_query);

                $success = 'Booking confirmed successfully! Booking ID: ' . $booking_id;
            } else{
                $error = 'Booking failed. please tyr again.';
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
    <title>Book Room - <?php echo htmlspecialchars($room_type['type_name']); ?></title>
     <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
        }
        
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
        }
        
        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            font-size: 1.8rem;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .room-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }
        
        .room-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .booking-form {
            padding: 40px;
        }
        
        .room-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .room-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-item strong {
            color: #2c3e50;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .btn {
            padding: 15px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
        
        .price-summary {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .price-summary h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            border-top: 2px solid #667eea;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Grand Hotel</h1>
            <div>
                <a href="index.php">Home</a>
                <a href="my_bookings.php">My Bookings</a>
                <a href="logout.php"></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="booking-card">
            <div class="room-header">
                <h2><?php echo htmlspecialchars($room_type['type_name']); ?></h2>
                <p><?php echo htmlspecialchars($room_type['description']); ?></p>
            </div>

            <div class="booking-form">
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="my_bookings.php">View My Bookings</a>
                    </div>
                <?php else: ?>
                
                <div class="room-info">
                    <h3>Room Details</h3>
                    <div class="room-info-grid">
                        <div class="info-item">
                            <strong>Price:</strong> $<?php echo number_format($room_type['price_per_night'], 2); ?>/night
                        </div>

                        <div class="info-item">
                            <strong>Capacity:</strong> $<?php echo $room_type['capacity']; ?> Guests
                        </div>

                        <div class="info-item">
                            <strong>Amenities:</strong> $<?php echo htmlspecialchars($room_type['amenities']); ?>
                        </div>
                    </div>
                </div>

                <form method="POST" id="bookingForm" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check-in Date *</label>
                            <input type="date" name="check_in" id="check_in" required min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotal()">
                        </div>

                        <div class="form-group">
                            <label>Check-out Date *</label>
                            <input type="date" name="check_out" id="check_out" required min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotal()">
                        </div>
                    </div>

                        <div class="form-group">
                            <label>Number of Guests * (Max: <?php echo $room_type['capacity']; ?>)</label>
                            <input type="number" name="guests" min="1" max="<?php echo $room_type['capacity']; ?>" value="1" required>
                        </div>

                        <div class="price-summary" id="priceSummary" style="display: none">
                            <h3>Price Summary</h3>
                            <div class="price-row">
                                <span>Nights Rate:</span>
                                <span>$<?php echo number_format($room_type['price_per_night'], 2); ?></span>
                            </div>

                            <div class="price-row">
                                <span>Number of nights:</span>
                                <span id="nights">0</span>
                            </div>

                            <div class="price-row total">
                                <span>Total Amount:</span>
                                <span id="totalPrice">$0.00</span>
                            </div>
                        </div>

                        <button type="submit" class="btn">Confirm Booking</button>
                </form>

                <?php endif; ?>

                <a href="index.php" class="back-line"><- Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        function calculateTotal(){
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const pricePerNight = <?php echo $room_type['price_per_night']; ?>;

            if(checkIn && checkOut){
                const start = new Date(checkIn);
                const end = new Date(checkOut);
                const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

                if (nights > 0) {
                    const total = nights * pricePerNight;
                    document.getElementById('nights').textContent = nights;
                    document.getElementById('totalPrice').textContent = '$' + total.toFixed(2);
                    document.getElementById('priceSummary').style.display = 'block';
                } else {
                    document.getElementById('priceSummary').style.display = 'none';
                }
            }
        }
    </script>
    
</body>
</html>