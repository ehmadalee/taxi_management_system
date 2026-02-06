<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle driver deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $driver_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM drivers WHERE driver_id = $driver_id");
    header('Location: manage_drivers.php');
    exit();
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $driver_id = $_GET['toggle_status'];
    $current_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM drivers WHERE driver_id = $driver_id"))['status'];
    $new_status = ($current_status == 'active') ? 'inactive' : 'active';
    mysqli_query($conn, "UPDATE drivers SET status = '$new_status' WHERE driver_id = $driver_id");
    header('Location: manage_drivers.php');
    exit();
}

// Fetch all drivers
$drivers_query = "SELECT * FROM drivers ORDER BY created_at DESC";
$drivers_result = mysqli_query($conn, $drivers_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 1.5em; }
        .navbar a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; }
        .sidebar { width: 250px; background: white; position: fixed; height: calc(100vh - 60px); box-shadow: 2px 0 10px rgba(0,0,0,0.1); padding: 20px 0; }
        .sidebar a { display: block; padding: 15px 25px; color: #333; text-decoration: none; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #667eea; color: white; }
        .main-content { margin-left: 250px; padding: 30px; }
        .card { background: white; border-radius: 10px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h2 { color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 0.9em; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .btn { padding: 6px 12px; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 0.85em; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-danger { background: #e74c3c; }
        .btn-warning { background: #f39c12; }
        .status { padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-available { background: #d4edda; color: #155724; }
        .status-not_available { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>⚙️ Admin Dashboard</h1>
        <div><a href="dashboard.php">Dashboard</a> <a href="logout.php">Logout</a></div>
    </div>
    
    <div class="sidebar">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="manage_passengers.php">👥 Manage Passengers</a>
        <a href="manage_drivers.php" class="active">🚗 Manage Drivers</a>
        <a href="manage_taxis.php">🚕 Manage Taxis</a>
        <a href="manage_bookings.php">📋 Manage Bookings</a>
        <a href="manage_payments.php">💳 Manage Payments</a>
        <a href="reports.php">📄 Reports</a>
    </div>
    
    <div class="main-content">
        <div class="card">
            <h2>🚗 All Drivers</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>License No.</th>
                        <th>Availability</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($driver = mysqli_fetch_assoc($drivers_result)): ?>
                        <tr>
                            <td>#<?php echo $driver['driver_id']; ?></td>
                            <td><?php echo $driver['full_name']; ?></td>
                            <td><?php echo $driver['username']; ?></td>
                            <td><?php echo $driver['email']; ?></td>
                            <td><?php echo $driver['phone']; ?></td>
                            <td><?php echo $driver['license_number']; ?></td>
                            <td><span class="status status-<?php echo $driver['availability']; ?>"><?php echo ucfirst(str_replace('_', ' ', $driver['availability'])); ?></span></td>
                            <td><span class="status status-<?php echo $driver['status']; ?>"><?php echo ucfirst($driver['status']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($driver['created_at'])); ?></td>
                            <td>
                                <a href="?toggle_status=<?php echo $driver['driver_id']; ?>" class="btn btn-warning">Toggle Status</a>
                                <a href="?delete=<?php echo $driver['driver_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
