<?php
require_once '../config.php';

if (!isset($_SESSION['driver_id'])) {
    header('Location: login.php');
    exit();
}

$driver_id = $_SESSION['driver_id'];
$driver_name = $_SESSION['driver_name'];

// Handle ride status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE bookings SET status = '$new_status' 
                    WHERE booking_id = $booking_id AND driver_id = $driver_id";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Ride status updated successfully!";
    } else {
        $error = "Failed to update status: " . mysqli_error($conn);
    }
}

// Handle availability toggle
if (isset($_GET['toggle_availability'])) {
    $availability_query = "SELECT availability FROM drivers WHERE driver_id = $driver_id";
    $availability_result = mysqli_query($conn, $availability_query);
    $current_availability = mysqli_fetch_assoc($availability_result)['availability'];
    
    $new_availability = ($current_availability == 'available') ? 'not_available' : 'available';
    
    $update_availability = "UPDATE drivers SET availability = '$new_availability' WHERE driver_id = $driver_id";
    mysqli_query($conn, $update_availability);
    header('Location: dashboard.php');
    exit();
}

// Fetch driver's current availability
$availability_query = "SELECT availability FROM drivers WHERE driver_id = $driver_id";
$availability_result = mysqli_query($conn, $availability_query);
$driver_availability = mysqli_fetch_assoc($availability_result)['availability'];

// Fetch assigned rides
$rides_query = "SELECT b.*, p.full_name as passenger_name, p.phone as passenger_phone, 
                t.vehicle_number, t.model 
                FROM bookings b 
                JOIN passengers p ON b.passenger_id = p.passenger_id 
                LEFT JOIN taxis t ON b.taxi_id = t.taxi_id 
                WHERE b.driver_id = $driver_id 
                ORDER BY b.booking_time DESC";
$rides_result = mysqli_query($conn, $rides_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
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
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .navbar a, .navbar button {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .navbar a:hover, .navbar button:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .availability {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .available {
            background: #28a745;
        }
        
        .not-available {
            background: #dc3545;
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
        
        .btn {
            padding: 6px 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-info {
            background: #17a2b8;
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
        
        select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🚗 Driver Dashboard</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo $driver_name; ?></span>
            <span class="availability <?php echo ($driver_availability == 'available') ? 'available' : 'not-available'; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $driver_availability)); ?>
            </span>
            <a href="?toggle_availability=1">Toggle Availability</a>
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
            <h2>🚕 My Assigned Rides</h2>
            <?php if (mysqli_num_rows($rides_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Passenger</th>
                            <th>Contact</th>
                            <th>Pickup</th>
                            <th>Drop-off</th>
                            <th>Date/Time</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ride = mysqli_fetch_assoc($rides_result)): ?>
                            <tr>
                                <td>#<?php echo $ride['booking_id']; ?></td>
                                <td><?php echo $ride['passenger_name']; ?></td>
                                <td><?php echo $ride['passenger_phone']; ?></td>
                                <td><?php echo $ride['pickup_location']; ?></td>
                                <td><?php echo $ride['dropoff_location']; ?></td>
                                <td><?php echo date('d M Y h:i A', strtotime($ride['booking_time'])); ?></td>
                                <td><?php echo $ride['vehicle_number'] ? $ride['vehicle_number'] . ' (' . $ride['model'] . ')' : 'Not Assigned'; ?></td>
                                <td><span class="status status-<?php echo $ride['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $ride['status'])); ?></span></td>
                                <td>
                                    <?php if ($ride['status'] == 'confirmed' || $ride['status'] == 'accepted' || $ride['status'] == 'in_progress'): ?>
                                        <form method="POST" action="" style="display: inline-block;">
                                            <input type="hidden" name="booking_id" value="<?php echo $ride['booking_id']; ?>">
                                            <select name="status">
                                                <?php if ($ride['status'] == 'confirmed'): ?>
                                                    <option value="accepted">Accept</option>
                                                <?php elseif ($ride['status'] == 'accepted'): ?>
                                                    <option value="in_progress">Start Trip</option>
                                                <?php elseif ($ride['status'] == 'in_progress'): ?>
                                                    <option value="completed">Complete</option>
                                                <?php endif; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-success">Update</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No rides assigned yet. Please wait for admin to assign rides to you.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
