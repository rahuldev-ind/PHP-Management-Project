<?php
require_once 'config.php';

$query = "SELECT * FROM room_types ORDER BY price_per_night";
$result = $conn->query($query);

if (!$result) {
    die("Database Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f4f4f4;
        }
        
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        
        .navbar nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 5px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .navbar nav a:hover {
            background: #34495e;
        }
        
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200') center/cover;
            color: white;
            text-align: center;
            padding: 100px 20px;
        }
        
        .hero h2 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            background: #c0392b;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: #2c3e50;
        }
        
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .room-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
        }
        
        .room-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .room-content {
            padding: 20px;
        }
        
        .room-content h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .room-content p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .room-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .price {
            font-size: 1.5rem;
            color: #e74c3c;
            font-weight: bold;
        }
        
        .amenities {
            font-size: 0.9rem;
            color: #777;
            margin: 10px 0;
        }
        
        footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }
        
        .search-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: -50px auto 40px;
            max-width: 800px;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>🏨 Grand Hotel</h1>
            <nav>
                <a href="index.php">Home</a>
                <?php if(isLoggedIn()): ?>
                    <a href="my_bookings.php">My Booking</a>
                    <?php if(isAdmin()): ?>
                        <a href="admin.php">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout (<?php echo $_SESSION['name']; ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </nav>

    <section class="hero">
        <h2>Welcome to Grand Hotel</h2>
        <p>Experience luxury and comfort in the heart of the city</p>
        <a href="#rooms" class="btn">Explore Rooms</a>
    </section>

    <div class="container">
        <div class="search-box">
            <form method="GET" action="search_rooms.php" class="search-form">
                <div class="form-group">
                    <label>Check-in Date</label>
                    <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Check-out Date</label>
                    <input type="date" name="check_out" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Guests</label>
                    <input type="number" name="guests" min="1" max="10" value="2" required>
                </div>

                <div class="form-group">
                    <label>&nbsp</label>
                    <button type="submit" class="btn" style="width: 100%">Search Rooms</button>
                </div>
            </form>
        </div>

        <h2 class="section-title" id="rooms">Our Rooms</h2>

        <div class="rooms-grid">
            <?php while ($room = $result->fetch_assoc()): ?>
                <div class="room-card">
                    <img src="<?php echo $room['image_url'] ?: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400'; ?>" alt="<?php echo htmlspecialchars($room['type_name']); ?>">
                    <div class="room-content">
                        <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                        <p><?php echo htmlspecialchars($room['description']); ?></p>
                        <div class="amenities">
                            <strong>Amenities:</strong><?php echo htmlspecialchars($room['amenities']); ?>
                        </div>

                        <div class="amenities">
                            <strong>Capacity:</strong><?php echo $room['capacity']; ?> Guests
                        </div>

                        <div class="room-details">
                            <span class="price">$<?php echo number_format($room['price_per_night'], 2); ?>/night</span>
                            <a href="book_room.php?type_id=<?php echo $room['id']?>" class="btn">Book Now</a>
                        </div>
                    </div>
                </div>
                
                <!-- another room-->
                <div class="room-card">
                    <img src="<?php echo $room['image_url'] ?: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400'; ?>" alt="<?php echo htmlspecialchars($room['type_name']); ?>">
                    <div class="room-content">
                        <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                        <p><?php echo htmlspecialchars($room['description']); ?></p>
                        <div class="amenities">
                            <strong>Amenities:</strong><?php echo htmlspecialchars($room['amenities']); ?>
                        </div>

                        <div class="amenities">
                            <strong>Capacity:</strong><?php echo $room['capacity']; ?> Guests
                        </div>

                        <div class="room-details">
                            <span class="price">$<?php echo number_format($room['price_per_night'], 2); ?>/night</span>
                            <a href="book_room.php?type_id=<?php echo $room['id']?>" class="btn">Book Now</a>
                        </div>
                    </div>
                </div>

            <!-- another room-->
             <div class="room-card">
                    <img src="<?php echo $room['image_url'] ?: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400'; ?>" alt="<?php echo htmlspecialchars($room['type_name']); ?>">
                    <div class="room-content">
                        <h3><?php echo htmlspecialchars($room['type_name']); ?></h3>
                        <p><?php echo htmlspecialchars($room['description']); ?></p>
                        <div class="amenities">
                            <strong>Amenities:</strong><?php echo htmlspecialchars($room['amenities']); ?>
                        </div>

                        <div class="amenities">
                            <strong>Capacity:</strong><?php echo $room['capacity']; ?> Guests
                        </div>

                        <div class="room-details">
                            <span class="price">$<?php echo number_format($room['price_per_night'], 2); ?>/night</span>
                            <a href="book_room.php?type_id=<?php echo $room['id']?>" class="btn">Book Now</a>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Grand Hotel. All rights reserved. (R Industry)</p>
    </footer>
</body>
</html>