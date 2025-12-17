<?php 
require_once 'config.php';

if(!isLoggedIn()){
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

if(isset($_GET['cancel']) && is_numeric($_GET['cancel'])){
    $booking_id = (int)$_GET['cancel'];
    $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id AND user_id = $user_id";
    $conn->query($update_query);
    redirect('my_bookings.php');
}

$query = "SELECT b.*, r.room_number, rt.type_name, rt.price_per_night
            FROM bookings b JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            WHERE b.user_id = $user_id ORDER BY b.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Hotel Booking System</title>
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
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #2c3e50;
            font-size: 2rem;
        }
        
        .bookings-grid {
            display: grid;
            gap: 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
        }
        
        .booking-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-item strong {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .detail-item span {
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .booking-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-align: center;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-cancel {
            background: #e74c3c;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #c0392b;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        
        .empty-state h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .price-tag {
            font-size: 1.5rem;
            color: #667eea;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .booking-card {
                grid-template-columns: 1fr;
            }
            
            .booking-actions {
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>🏨 Grand Hotel</h1>
            <div>
                <a href="index.php">Home</a>
                <a href="my_bookings.php">My Bookings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>My Bookings</h2>
        </div>

        <?php if($result->num_rows > 0): ?>
            <div class="bookings-grid">
                <?php while ($booking = $result->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="booking-info">
                            <h3>Booking #<?php echo $booking['id']; ?> - <?php echo htmlspecialchars($booking['type_name']); ?></h3>

                            <div class="booking-details">
                                <div class="detail-item">
                                    <strong>Room Number</strong>
                                    <span><?php echo htmlspecialchars($booking['room_number']); ?></span>
                                </div>

                                <div class="detail-item">
                                    <strong>Check-in</strong>
                                    <span><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></span>
                                </div>

                                <div class="detail-item">
                                    <strong>Check-out</strong>
                                    <span><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></span>
                                </div>

                                <div class="detail-item">
                                    <strong>Guests</strong>
                                    <span><?php echo $booking['guests']; ?> Person(s)</span>
                                </div>

                                <div class="detail-item">
                                    <strong>Total Price</strong>
                                    <span class='price-tag'>$<?php echo number_format($booking['total_price'], 2); ?></span>
                                </div>

                                <div class="detail-item">
                                    <strong>Booked On</strong>
                                    <span><?php echo date('M d, Y', strtotime($booking['check_at'])); ?></span>
                                </div>
                            </div>

                            <?php if(!empty($booking['special_requests'])): ?>
                                <div style="margin-top: 15px;">
                                    <strong>Special Requests</strong>
                                    <p style="color: #666; margin-top: 5px;"><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="booking-actions">
                            <span class="status-badge status-<?php echo $booking['status'];?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>

                            <?php if($booking['status'] === 'confirmed' && strtotime($booking['check_in']) > time()): ?>
                                <a href="my_bookings.php?cancel=<?php echo $booking['id']; ?>"
                                class="btn btn-cancel"
                                onclick="return confirm('Are you sure you want to cancel this bookings?')">Cancel Booking</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Booking yet</h3>
                <p>You haven't made any bookings. Start exploring our rooms!</p>
                <a href="index.php" class="btn btn-primary">Browse Rooms</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>