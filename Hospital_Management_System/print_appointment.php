<?php
include 'includes/db.php';

// Get appointment ID
if (!isset($_GET['id'])) {
    header("Location: list_appointments.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch appointment details with patient and doctor info
$sql = "SELECT 
            a.*, 
            p.name as patient_name, 
            p.age as patient_age,
            p.gender as patient_gender,
            p.contact as patient_contact,
            p.address as patient_address,
            d.name as doctor_name, 
            d.specialization,
            d.phone as doctor_phone
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = '$id'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    header("Location: list_appointments.php");
    exit();
}

$appointment = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Slip - #<?php echo $appointment['id']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #4CAF50;
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .appointment-id {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-left: 4px solid #4CAF50;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 13px;
            display: block;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .appointment-date-time {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        
        .appointment-date-time .date {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .appointment-date-time .time {
            font-size: 20px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .instructions {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .instructions h4 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            margin-left: 20px;
            color: #856404;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
        
        .button-group {
            text-align: center;
            margin: 30px 0 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-print {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-print:hover {
            background-color: #45a049;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        /* Print styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 20px;
            }
            
            .button-group {
                display: none;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🏥 City Hospital</h1>
            <p>123 Healthcare Street, Medical District, Mumbai, MH 400001</p>
            <p>Phone: +91-22-1234-5678 | Email: info@cityhospital.com</p>
        </div>
        
        <!-- Appointment ID Badge -->
        <div style="text-align: center;">
            <div class="appointment-id">
                Appointment ID: #<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>
        
        <!-- Date and Time Highlight -->
        <div class="appointment-date-time">
            <div class="date">
                📅 <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?>
            </div>
            <div class="time">
                🕐 <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
            </div>
        </div>
        
        <!-- Patient Information -->
        <div class="section">
            <div class="section-title">Patient Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($appointment['patient_name']); ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Age / Gender</span>
                    <span class="info-value"><?php echo $appointment['patient_age'] . ' years / ' . $appointment['patient_gender']; ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Contact Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($appointment['patient_contact']); ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Patient ID</span>
                    <span class="info-value">#<?php echo str_pad($appointment['patient_id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Doctor Information -->
        <div class="section">
            <div class="section-title">Doctor Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Doctor Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($appointment['doctor_name']); ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Specialization</span>
                    <span class="info-value"><?php echo htmlspecialchars($appointment['specialization']); ?></span>
                </div>

                <div class="info-item">
                    <span class="info-label">Contact Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($appointment['doctor_phone']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                        <?php echo $appointment['status']; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Reason for Visit -->
        <?php if (!empty($appointment['reason'])): ?>
        <div class="section">
            <div class="section-title">Reason for Visit</div>
            <div class="info-item">
                <span class="info-value"><?php echo nl2br(htmlspecialchars($appointment['reason'])); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Instructions -->
        <div class="instructions">
            <h4>⚠️ Important Instructions:</h4>
            <ul>
                <li>Please arrive 15 minutes before your appointment time</li>
                <li>Bring your previous medical records and prescriptions</li>
                <li>Carry a valid ID proof</li>
                <li>In case of cancellation, inform us 24 hours in advance</li>
                <li>For any queries, call us at +91-22-1234-5678</li>
            </ul>
        </div>
        
        <!-- Action Buttons -->
        <div class="button-group">
            <button onclick="window.print()" class="btn btn-print">🖨️ Print Appointment Slip</button>
            <a href="list_appointments.php" class="btn btn-secondary">📋 View All Appointments</a>
            <a href="index.php" class="btn btn-secondary">🏠 Go to Dashboard</a>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Generated on: <?php echo date('d/m/Y h:i A'); ?></p>
            <p>This is a computer-generated appointment slip and does not require a signature.</p>
            <p><strong>City Hospital - Caring for You, Always</strong></p>
        </div>
    </div>
    
    <script>
        // Optional: Auto-print dialog on page load
        // Uncomment the line below if you want automatic print dialog
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
