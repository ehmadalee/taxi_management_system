<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get statistics
$total_passengers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM passengers"))['count'];
$total_drivers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM drivers"))['count'];
$total_taxis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM taxis"))['count'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$pending_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"))['count'];
$active_rides = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status IN ('accepted', 'in_progress')"))['count'];

// Fetch recent bookings
$recent_bookings_query = "SELECT b.*, p.full_name as passenger_name, d.full_name as driver_name, 
                         t.vehicle_number, t.model 
                         FROM bookings b 
                         LEFT JOIN passengers p ON b.passenger_id = p.passenger_id 
                         LEFT JOIN drivers d ON b.driver_id = d.driver_id 
                         LEFT JOIN taxis t ON b.taxi_id = t.taxi_id 
                         ORDER BY b.created_at DESC 
                         LIMIT 10";
$recent_bookings = mysqli_query($conn, $recent_bookings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9em;
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
    </style>
</head>
<body>
    <div class="navbar">
        <h1>⚙️ Admin Dashboard</h1>
        <div>
            <span>Welcome, <?php echo $admin_name; ?> | </span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="sidebar">
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="manage_passengers.php">👥 Manage Passengers</a>
        <a href="manage_drivers.php">🚗 Manage Drivers</a>
        <a href="manage_taxis.php">🚕 Manage Taxis</a>
        <a href="manage_bookings.php">📋 Manage Bookings</a>
        <a href="manage_payments.php">💳 Manage Payments</a>
        <a href="reports.php">📄 Reports</a>
    </div>
    
    <div class="main-content">
        <h2 style="margin-bottom: 20px;">System Overview</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="number"><?php echo $total_passengers; ?></div>
                <div class="label">Total Passengers</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🚗</div>
                <div class="number"><?php echo $total_drivers; ?></div>
                <div class="label">Total Drivers</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🚕</div>
                <div class="number"><?php echo $total_taxis; ?></div>
                <div class="label">Total Taxis</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">📋</div>
                <div class="number"><?php echo $total_bookings; ?></div>
                <div class="label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">⏳</div>
                <div class="number"><?php echo $pending_bookings; ?></div>
                <div class="label">Pending Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">🚀</div>
                <div class="number"><?php echo $active_rides; ?></div>
                <div class="label">Active Rides</div>
            </div>
        </div>
        
        <div class="card">
            <h2>Recent Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Driver</th>
                        <th>Pickup</th>
                        <th>Drop-off</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                        <tr>
                            <td>#<?php echo $booking['booking_id']; ?></td>
                            <td><?php echo $booking['passenger_name']; ?></td>
                            <td><?php echo $booking['driver_name'] ?: 'Not Assigned'; ?></td>
                            <td><?php echo substr($booking['pickup_location'], 0, 30) . '...'; ?></td>
                            <td><?php echo substr($booking['dropoff_location'], 0, 30) . '...'; ?></td>
                            <td><?php echo date('d M Y', strtotime($booking['created_at'])); ?></td>
                            <td><span class="status status-<?php echo $booking['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
