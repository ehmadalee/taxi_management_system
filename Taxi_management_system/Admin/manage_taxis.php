<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle adding new taxi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_taxi'])) {
    $vehicle_number = mysqli_real_escape_string($conn, $_POST['vehicle_number']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    
    $insert_query = "INSERT INTO taxis (vehicle_number, model, capacity, year, color) 
                    VALUES ('$vehicle_number', '$model', '$capacity', '$year', '$color')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success = "Taxi added successfully!";
    } else {
        $error = "Failed to add taxi!";
    }
}

// Handle taxi deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $taxi_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM taxis WHERE taxi_id = $taxi_id");
    header('Location: manage_taxis.php');
    exit();
}

// Fetch all taxis
$taxis_query = "SELECT * FROM taxis ORDER BY created_at DESC";
$taxis_result = mysqli_query($conn, $taxis_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Taxis - Admin</title>
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
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 0.9em; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .btn { padding: 10px 20px; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-danger { background: #e74c3c; padding: 6px 12px; font-size: 0.85em; }
        .status { padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600; }
        .status-available { background: #d4edda; color: #155724; }
        .status-in_use { background: #fff3cd; color: #856404; }
        .status-maintenance { background: #f8d7da; color: #721c24; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
        <a href="manage_drivers.php">🚗 Manage Drivers</a>
        <a href="manage_taxis.php" class="active">🚕 Manage Taxis</a>
        <a href="manage_bookings.php">📋 Manage Bookings</a>
        <a href="manage_payments.php">💳 Manage Payments</a>
        <a href="reports.php">📄 Reports</a>
    </div>
    
    <div class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>➕ Add New Taxi</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Vehicle Number *</label>
                        <input type="text" name="vehicle_number" required placeholder="ABC-1234">
                    </div>
                    
                    <div class="form-group">
                        <label>Model *</label>
                        <input type="text" name="model" required placeholder="Toyota Camry">
                    </div>
                    
                    <div class="form-group">
                        <label>Capacity *</label>
                        <input type="number" name="capacity" required placeholder="4">
                    </div>
                    
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" name="year" placeholder="2023">
                    </div>
                    
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" placeholder="White">
                    </div>
                </div>
                
                <button type="submit" name="add_taxi" class="btn btn-primary">Add Taxi</button>
            </form>
        </div>
        
        <div class="card">
            <h2>🚕 All Taxis</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vehicle Number</th>
                        <th>Model</th>
                        <th>Capacity</th>
                        <th>Year</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($taxi = mysqli_fetch_assoc($taxis_result)): ?>
                        <tr>
                            <td>#<?php echo $taxi['taxi_id']; ?></td>
                            <td><?php echo $taxi['vehicle_number']; ?></td>
                            <td><?php echo $taxi['model']; ?></td>
                            <td><?php echo $taxi['capacity']; ?> passengers</td>
                            <td><?php echo $taxi['year']; ?></td>
                            <td><?php echo $taxi['color']; ?></td>
                            <td><span class="status status-<?php echo $taxi['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $taxi['status'])); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($taxi['created_at'])); ?></td>
                            <td>
                                <a href="?delete=<?php echo $taxi['taxi_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
