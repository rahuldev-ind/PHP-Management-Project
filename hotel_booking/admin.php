<?php 
require_once 'config.php';

if(!isLoggedIn() || !isAdmin()){
    redirect('index.php');
}

$stats_query = "SELECT (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed') as confirmed_bookings,
                (SELECT COUNT(*) FROM bookings WHERE status = 'pending') as pending_bookings,
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT SUM(total_price) FROM booking WHERE status IN ('confirmed', 'completed')) as total_revenue";

$stats = $conn->query($stats_query)->fetch_assoc();

$booking_query = "SELECT b.*, u.name as user_name, u.email, r.room_number, rt.type_name
                    FROM bookings b JOIN users u ON b.user_id = u.id
                    JOIN rooms r ON b.room_id = r.id
                    JOIN room_types rt ON r.room_type_id = rt.id
                    ORDER BY b.created_at DESC LIMIT 10";

$booking = $conn->query($booking_query);

if(isset($_POST['update_status'])){
    $booking_id = (int)$_POST['booking_id'];
    $new_status = senitize($_POST['status']);
    $update = "UPDATE booking SET status = '$new_status' WHERE id = $booking_id";
    $conn->query($update);
    redirect('admin.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel booking system</title>
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
            max-width: 1400px;
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
            max-width: 1400px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-card.revenue .stat-value {
            color: #27ae60;
        }
        
        .stat-card.pending .stat-value {
            color: #f39c12;
        }
        
        .stat-card.confirmed .stat-value {
            color: #3498db;
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            color: #2c3e50;
            font-weight: 600;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
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
        
        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .actions-menu {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .actions-menu a {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .actions-menu a:hover {
            background: #5568d3;
        }
        
        @media (max-width: 768px) {
            table {
                font-size: 0.9rem;
            }
            
            table th, table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>🏨 Admin Dashboard</h1>
            <div>
                <a href="index.php">Home</a>
                <a href="admin.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Dashboard Overview</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card revenue">
                <h3>Total Revenue</h3>
                <div class="stat-value">$<?php echo number_format($stats['total_revenue'] ?? 0,2); ?></div>
            </div>

            <div class="stat-card confirmed">
                <h3>Confirmed Booking</h3>
                <div class="stat-value">$<?php echo $stats['confirmed_bookings']; ?></div>
            </div>

            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value">$<?php echo $stats['total_users']; ?></div>
            </div>
        </div>

        <div class="section">
            <h3>Recent Booking</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($booking['email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                            <td><?php echo $booking['guests']; ?></td>
                            <td><strong>$<?php echo number_format($booking['total_price'], 2) ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline; " action="">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="">Change Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            
                        </tr>

                        <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>