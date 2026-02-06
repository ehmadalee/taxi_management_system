<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle booking assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_booking'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $driver_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
    $taxi_id = mysqli_real_escape_string($conn, $_POST['taxi_id']);
    $fare = mysqli_real_escape_string($conn, $_POST['fare']);
    
    $update_query = "UPDATE bookings SET driver_id = '$driver_id', taxi_id = '$taxi_id', 
                    fare = '$fare', status = 'confirmed' 
                    WHERE booking_id = $booking_id";
    
    if (mysqli_query($conn, $update_query)) {
        // Update taxi status
        mysqli_query($conn, "UPDATE taxis SET status = 'in_use' WHERE taxi_id = $taxi_id");
        $success = "Booking assigned successfully!";
    } else {
        $error = "Failed to assign booking!";
    }
}

// Handle booking deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $booking_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM bookings WHERE booking_id = $booking_id");
    header('Location: manage_bookings.php');
    exit();
}

// Fetch all bookings
$bookings_query = "SELECT b.*, p.full_name as passenger_name, p.phone as passenger_phone,
                  d.full_name as driver_name, t.vehicle_number, t.model 
                  FROM bookings b 
                  LEFT JOIN passengers p ON b.passenger_id = p.passenger_id 
                  LEFT JOIN drivers d ON b.driver_id = d.driver_id 
                  LEFT JOIN taxis t ON b.taxi_id = t.taxi_id 
                  ORDER BY b.created_at DESC";
$bookings_result = mysqli_query($conn, $bookings_query);

// Fetch available drivers
$drivers_query = "SELECT * FROM drivers WHERE status = 'active' AND availability = 'available'";
$drivers_result = mysqli_query($conn, $drivers_query);

// Fetch available taxis
$taxis_query = "SELECT * FROM taxis WHERE status = 'available'";
$taxis_result = mysqli_query($conn, $taxis_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
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
        
        .sidebar {
            width: 250px;
            background: white;
            position: fixed;
            height: calc(100vh - 60px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        
        .sidebar a {
            display: block;
            padding: 15px 25px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: #667eea;
            color: white;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
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
            font-size: 0.9em;
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
            font-size: 0.85em;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
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
        <h1>⚙️ Admin Dashboard</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="sidebar">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="manage_passengers.php">👥 Manage Passengers</a>
        <a href="manage_drivers.php">🚗 Manage Drivers</a>
        <a href="manage_taxis.php">🚕 Manage Taxis</a>
        <a href="manage_bookings.php" class="active">📋 Manage Bookings</a>
        <a href="reports.php">📄 Reports</a>
    </div>
    
    <div class="main-content">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>📋 All Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Contact</th>
                        <th>Pickup</th>
                        <th>Drop-off</th>
                        <th>Date/Time</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Fare</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                        <tr>
                            <td>#<?php echo $booking['booking_id']; ?></td>
                            <td><?php echo $booking['passenger_name']; ?></td>
                            <td><?php echo $booking['passenger_phone']; ?></td>
                            <td><?php echo substr($booking['pickup_location'], 0, 20) . '...'; ?></td>
                            <td><?php echo substr($booking['dropoff_location'], 0, 20) . '...'; ?></td>
                            <td><?php echo date('d M Y h:i A', strtotime($booking['booking_time'])); ?></td>
                            <td><?php echo $booking['driver_name'] ?: 'Not Assigned'; ?></td>
                            <td><?php echo $booking['vehicle_number'] ?: 'Not Assigned'; ?></td>
                            <td>Rs<?php echo $booking['fare'] ?: '0.00'; ?></td>
                            <td><span class="status status-<?php echo $booking['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?></span></td>
                            <td>
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <button class="btn btn-success" onclick="openAssignModal(<?php echo $booking['booking_id']; ?>, '<?php echo addslashes($booking['passenger_name']); ?>', '<?php echo addslashes($booking['pickup_location']); ?>', '<?php echo addslashes($booking['dropoff_location']); ?>')">Assign</button>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $booking['booking_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Assignment Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAssignModal()">&times;</span>
            <h2>Assign Driver & Taxi</h2>
            <form method="POST" action="">
                <input type="hidden" id="modal_booking_id" name="booking_id">
                
                <div class="form-group">
                    <label>Passenger Info</label>
                    <input type="text" id="modal_passenger_info" readonly>
                </div>
                
                <div class="form-group">
                    <label>Select Driver *</label>
                    <select name="driver_id" required>
                        <option value="">-- Select Driver --</option>
                        <?php 
                        mysqli_data_seek($drivers_result, 0);
                        while ($driver = mysqli_fetch_assoc($drivers_result)): 
                        ?>
                            <option value="<?php echo $driver['driver_id']; ?>">
                                <?php echo $driver['full_name'] . ' (' . $driver['license_number'] . ')'; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Select Taxi *</label>
                    <select name="taxi_id" required>
                        <option value="">-- Select Taxi --</option>
                        <?php 
                        mysqli_data_seek($taxis_result, 0);
                        while ($taxi = mysqli_fetch_assoc($taxis_result)): 
                        ?>
                            <option value="<?php echo $taxi['taxi_id']; ?>">
                                <?php echo $taxi['vehicle_number'] . ' - ' . $taxi['model'] . ' (' . $taxi['color'] . ')'; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Fare ($) *</label>
                    <input type="number" step="0.01" name="fare" required placeholder="0.00">
                </div>
                
                <button type="submit" name="assign_booking" class="btn btn-success" style="width: 100%; padding: 12px;">Assign Booking</button>
            </form>
        </div>
    </div>
    
    <script>
        function openAssignModal(bookingId, passenger, pickup, dropoff) {
            document.getElementById('modal_booking_id').value = bookingId;
            document.getElementById('modal_passenger_info').value = passenger + ' | ' + pickup + ' → ' + dropoff;
            document.getElementById('assignModal').style.display = 'block';
        }
        
        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('assignModal')) {
                closeAssignModal();
            }
        }
    </script>
</body>
</html>
