<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'bookings';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch data based on report type
$data = array();
$report_title = '';

switch ($report_type) {
    case 'bookings':
        $report_title = 'Bookings Report';
        $query = "SELECT b.*, p.full_name as passenger_name, d.full_name as driver_name, 
                 t.vehicle_number, t.model 
                 FROM bookings b 
                 LEFT JOIN passengers p ON b.passenger_id = p.passenger_id 
                 LEFT JOIN drivers d ON b.driver_id = d.driver_id 
                 LEFT JOIN taxis t ON b.taxi_id = t.taxi_id 
                 WHERE DATE(b.created_at) BETWEEN '$start_date' AND '$end_date'
                 ORDER BY b.created_at DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        break;
        
    case 'passengers':
        $report_title = 'Passengers Report';
        $query = "SELECT * FROM passengers 
                 WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                 ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        break;
        
    case 'drivers':
        $report_title = 'Drivers Report';
        $query = "SELECT * FROM drivers 
                 WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                 ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        break;
        
    case 'revenue':
        $report_title = 'Revenue Report';
        $query = "SELECT DATE(created_at) as date, COUNT(*) as total_bookings, 
                 SUM(fare) as total_revenue, AVG(fare) as avg_fare 
                 FROM bookings 
                 WHERE status = 'completed' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                 GROUP BY DATE(created_at)
                 ORDER BY date DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        break;
        
    case 'payments':
        $report_title = 'Payments Report';
        $query = "SELECT p.*, b.pickup_location, b.dropoff_location,
                 pa.full_name as passenger_name, d.full_name as driver_name
                 FROM payments p
                 JOIN bookings b ON p.booking_id = b.booking_id
                 JOIN passengers pa ON p.passenger_id = pa.passenger_id
                 LEFT JOIN drivers d ON p.driver_id = d.driver_id
                 WHERE DATE(p.created_at) BETWEEN '$start_date' AND '$end_date'
                 ORDER BY p.created_at DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        break;
}

// Handle PDF download
if (isset($_GET['download']) && $_GET['download'] == 'pdf') {
    require_once 'generate_pdf.php';
    generate_pdf_report($report_type, $report_title, $data, $start_date, $end_date);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
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
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 10px;
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
        
        .btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .download-section {
            text-align: right;
            margin-bottom: 20px;
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
        <a href="manage_bookings.php">📋 Manage Bookings</a>
        <a href="manage_payments.php">💳 Manage Payments</a>
        <a href="reports.php" class="active">📄 Reports</a>
    </div>
    
    <div class="main-content">
        <div class="card">
            <h2>📄 Generate Reports</h2>
            
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label>Report Type</label>
                    <select name="report_type" onchange="this.form.submit()">
                        <option value="bookings" <?php echo $report_type == 'bookings' ? 'selected' : ''; ?>>Bookings Report</option>
                        <option value="passengers" <?php echo $report_type == 'passengers' ? 'selected' : ''; ?>>Passengers Report</option>
                        <option value="drivers" <?php echo $report_type == 'drivers' ? 'selected' : ''; ?>>Drivers Report</option>
                        <option value="revenue" <?php echo $report_type == 'revenue' ? 'selected' : ''; ?>>Revenue Report</option>
                        <option value="payments" <?php echo $report_type == 'payments' ? 'selected' : ''; ?>>Payments Report</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" onchange="this.form.submit()">
                </div>
                
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" onchange="this.form.submit()">
                </div>
            </form>
            
            <div class="download-section">
                <a href="?report_type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&download=pdf" class="btn btn-danger">📥 Download PDF Report</a>
            </div>
            
            <h3><?php echo $report_title; ?> (<?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?>)</h3>
            
            <?php if (empty($data)): ?>
                <p>No data available for the selected criteria.</p>
            <?php else: ?>
                
                <?php if ($report_type == 'bookings'): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Passenger</th>
                                <th>Driver</th>
                                <th>Pickup</th>
                                <th>Drop-off</th>
                                <th>Date</th>
                                <th>Fare</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td><?php echo $row['passenger_name']; ?></td>
                                    <td><?php echo $row['driver_name'] ?: 'Not Assigned'; ?></td>
                                    <td><?php echo substr($row['pickup_location'], 0, 30); ?></td>
                                    <td><?php echo substr($row['dropoff_location'], 0, 30); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>Rs<?php echo number_format($row['fare'], 2); ?></td>
                                    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                
                <?php elseif ($report_type == 'passengers'): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['passenger_id']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                
                <?php elseif ($report_type == 'drivers'): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>License Number</th>
                                <th>Registered Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['driver_id']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo $row['license_number']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                
                <?php elseif ($report_type == 'revenue'): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Bookings</th>
                                <th>Total Revenue</th>
                                <th>Average Fare</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_bookings = 0;
                            $total_revenue = 0;
                            foreach ($data as $row): 
                                $total_bookings += $row['total_bookings'];
                                $total_revenue += $row['total_revenue'];
                            ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo $row['total_bookings']; ?></td>
                                    <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                    <td>$<?php echo number_format($row['avg_fare'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight: bold; background: #f8f9fa;">
                                <td>TOTAL</td>
                                <td><?php echo $total_bookings; ?></td>
                                <td>$<?php echo number_format($total_revenue, 2); ?></td>
                                <td>$<?php echo $total_bookings > 0 ? number_format($total_revenue / $total_bookings, 2) : '0.00'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                
                <?php elseif ($report_type == 'payments'): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Passenger</th>
                                <th>Driver</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_amount = 0;
                            foreach ($data as $row):
                                $total_amount += $row['amount'];
                            ?>
                                <tr>
                                    <td>#<?php echo $row['payment_id']; ?></td>
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td><?php echo $row['passenger_name']; ?></td>
                                    <td><?php echo $row['driver_name'] ?: 'N/A'; ?></td>
                                    <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $row['payment_method'])); ?></td>
                                    <td><?php echo $row['transaction_id']; ?></td>
                                    <td><span class="status status-<?php echo $row['payment_status']; ?>"><?php echo ucfirst($row['payment_status']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight: bold; background: #f8f9fa;">
                                <td colspan="4">TOTAL</td>
                                <td>$<?php echo number_format($total_amount, 2); ?></td>
                                <td colspan="4"><?php echo count($data); ?> Payments</td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
