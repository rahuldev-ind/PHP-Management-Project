<?php
include 'includes/db.php';
include 'includes/header.php';

// Get statistics
$patient_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients"))['count'];
$doctor_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM doctors"))['count'];
$appointment_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments"))['count'];
$pending_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments WHERE status='Pending'"))['count'];
?>

<div class="container">
    <h1>Hospital Management System</h1>
    <p>Welcome to the Hospital Management Dashboard</p>
    
    <style>
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stat-card h2 {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .stat-card p {
            font-size: 18px;
            opacity: 0.9;
        }
        .quick-links {
            margin-top: 30px;
        }
        .quick-links h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .quick-link {
            display: block;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .quick-link:hover {
            background-color: #45a049;
        }
    </style>
    
    <div class="stats">
        <div class="stat-card">
            <h2><?php echo $patient_count; ?></h2>
            <p>Total Patients</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $doctor_count; ?></h2>
            <p>Total Doctors</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $appointment_count; ?></h2>
            <p>Total Appointments</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $pending_appointments; ?></h2>
            <p>Pending Appointments</p>
        </div>
    </div>
    
    <div class="quick-links">
        <h3>Quick Actions</h3>
        <div class="links-grid">
            <a href="add_patient.php" class="quick-link">Add New Patient</a>
            <a href="add_doctor.php" class="quick-link">Add New Doctor</a>
            <a href="add_appointment.php" class="quick-link">Book Appointment</a>
            <a href="list_appointments.php" class="quick-link">View Appointments</a>
        </div>
    </div>
</div>
</body>
</html>
