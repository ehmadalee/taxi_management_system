<?php
require_once '../config.php';

if (!isset($_SESSION['passenger_id'])) {
    header('Location: login.php');
    exit();
}

$passenger_id = $_SESSION['passenger_id'];
$passenger_name = $_SESSION['passenger_name'];

// Handle new booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_taxi'])) {
    $pickup_location = mysqli_real_escape_string($conn, $_POST['pickup_location']);
    $dropoff_location = mysqli_real_escape_string($conn, $_POST['dropoff_location']);
    $booking_time = mysqli_real_escape_string($conn, $_POST['booking_time']);
    
    $insert_query = "INSERT INTO bookings (passenger_id, pickup_location, dropoff_location, booking_time, status) 
                    VALUES ('$passenger_id', '$pickup_location', '$dropoff_location', '$booking_time', 'pending')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success = "Booking created successfully! Waiting for admin confirmation.";
    } else {
        $error = "Failed to create booking: " . mysqli_error($conn);
    }
}

// Handle booking cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];
    $update_query = "UPDATE bookings SET status = 'cancelled' 
                    WHERE booking_id = $booking_id AND passenger_id = $passenger_id";
    mysqli_query($conn, $update_query);
    header('Location: dashboard.php');
    exit();
}

// Fetch passenger's bookings with payment info
$bookings_query = "SELECT b.*, d.full_name as driver_name, t.vehicle_number, t.model,
                  p.payment_id, p.payment_status, p.payment_method, p.amount as paid_amount
                  FROM bookings b 
                  LEFT JOIN drivers d ON b.driver_id = d.driver_id 
                  LEFT JOIN taxis t ON b.taxi_id = t.taxi_id 
                  LEFT JOIN payments p ON b.booking_id = p.booking_id
                  WHERE b.passenger_id = $passenger_id 
                  ORDER BY b.created_at DESC";
$bookings_result = mysqli_query($conn, $bookings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 1.5em;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 0.9em;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-in_progress {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🚕 Passenger Dashboard</h1>
        <div>
            <span>Welcome, <?php echo $passenger_name; ?> | </span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>📍 Book a New Taxi</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Pickup Location *</label>
                    <input type="text" name="pickup_location" required placeholder="Enter pickup address">
                </div>
                
                <div class="form-group">
                    <label>Drop-off Location *</label>
                    <input type="text" name="dropoff_location" required placeholder="Enter destination">
                </div>
                
                <div class="form-group">
                    <label>Booking Date & Time *</label>
                    <input type="datetime-local" name="booking_time" required>
                </div>
                
                <button type="submit" name="book_taxi" class="btn">Book Taxi</button>
            </form>
        </div>
        
        <div class="card">
            <h2>📋 My Bookings</h2>
            <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Pickup</th>
                            <th>Drop-off</th>
                            <th>Date/Time</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Fare</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                            <tr>
                                <td>#<?php echo $booking['booking_id']; ?></td>
                                <td><?php echo $booking['pickup_location']; ?></td>
                                <td><?php echo $booking['dropoff_location']; ?></td>
                                <td><?php echo date('d M Y h:i A', strtotime($booking['booking_time'])); ?></td>
                                <td><?php echo $booking['driver_name'] ?: 'Not Assigned'; ?></td>
                                <td><?php echo $booking['vehicle_number'] ? $booking['vehicle_number'] . ' (' . $booking['model'] . ')' : 'Not Assigned'; ?></td>
                                <td><strong>Rs<?php echo number_format($booking['fare'] ?: 0, 2); ?></strong></td>
                                <td>
                                    <?php if ($booking['payment_id']): ?>
                                        <span class="status status-<?php echo $booking['payment_status']; ?>">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                        <br><small style="color: #666;"><?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?></small>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.9em;">Not Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="status status-<?php echo $booking['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?></span></td>
                                <td>
                                    <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                        <a href="?cancel=<?php echo $booking['booking_id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bookings found. Book your first taxi above!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
