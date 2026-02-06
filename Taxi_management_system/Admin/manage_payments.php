<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment'])) {
    $payment_id = mysqli_real_escape_string($conn, $_POST['payment_id']);
    $payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
    $payment_date = date('Y-m-d H:i:s');
    
    $update_query = "UPDATE payments SET payment_status = '$payment_status', payment_date = '$payment_date' 
                    WHERE payment_id = $payment_id";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Payment status updated successfully!";
    } else {
        $error = "Failed to update payment status!";
    }
}

// Handle adding new payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_payment'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_id = mysqli_real_escape_string($conn, $_POST['transaction_id']);
    
    // Get booking details
    $booking_query = "SELECT passenger_id, driver_id FROM bookings WHERE booking_id = $booking_id";
    $booking_result = mysqli_query($conn, $booking_query);
    $booking = mysqli_fetch_assoc($booking_result);
    
    if ($booking) {
        $passenger_id = $booking['passenger_id'];
        $driver_id = $booking['driver_id'] ?: 'NULL';
        
        $insert_query = "INSERT INTO payments (booking_id, passenger_id, driver_id, amount, payment_method, 
                        payment_status, transaction_id, payment_date) 
                        VALUES ($booking_id, $passenger_id, $driver_id, $amount, '$payment_method', 
                        'completed', '$transaction_id', NOW())";
        
        if (mysqli_query($conn, $insert_query)) {
            $success = "Payment added successfully!";
        } else {
            $error = "Failed to add payment!";
        }
    } else {
        $error = "Invalid booking ID!";
    }
}

// Handle payment deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $payment_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM payments WHERE payment_id = $payment_id");
    header('Location: manage_payments.php');
    exit();
}

// Fetch all payments
$payments_query = "SELECT p.*, b.pickup_location, b.dropoff_location, 
                  pa.full_name as passenger_name, d.full_name as driver_name 
                  FROM payments p 
                  JOIN bookings b ON p.booking_id = b.booking_id 
                  JOIN passengers pa ON p.passenger_id = pa.passenger_id 
                  LEFT JOIN drivers d ON p.driver_id = d.driver_id 
                  ORDER BY p.created_at DESC";
$payments_result = mysqli_query($conn, $payments_query);

// Get payment statistics
$total_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments"))['count'];
$completed_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE payment_status = 'completed'"))['count'];
$pending_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE payment_status = 'pending'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'"))['total'] ?: 0;

// Fetch completed bookings for adding payments
$bookings_query = "SELECT b.booking_id, b.pickup_location, b.dropoff_location, b.fare, p.full_name 
                  FROM bookings b 
                  JOIN passengers p ON b.passenger_id = p.passenger_id 
                  WHERE b.status = 'completed' 
                  AND b.booking_id NOT IN (SELECT booking_id FROM payments)
                  ORDER BY b.created_at DESC";
$bookings_result = mysqli_query($conn, $bookings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f6fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 1.5em; }
        .navbar a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; }
        .sidebar { width: 250px; background: white; position: fixed; height: calc(100vh - 60px); box-shadow: 2px 0 10px rgba(0,0,0,0.1); padding: 20px 0; overflow-y: auto; }
        .sidebar a { display: block; padding: 15px 25px; color: #333; text-decoration: none; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #667eea; color: white; }
        .main-content { margin-left: 250px; padding: 30px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-card .icon { font-size: 2.5em; margin-bottom: 10px; }
        .stat-card .number { font-size: 2em; font-weight: bold; color: #667eea; margin-bottom: 5px; }
        .stat-card .label { color: #666; font-size: 0.9em; }
        
        .card { background: white; border-radius: 10px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h2 { color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-size: 0.9em; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        
        .btn { padding: 10px 20px; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-success { background: #28a745; padding: 6px 12px; font-size: 0.85em; }
        .btn-danger { background: #e74c3c; padding: 6px 12px; font-size: 0.85em; }
        .btn-warning { background: #f39c12; padding: 6px 12px; font-size: 0.85em; }
        
        .status { padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #d1ecf1; color: #0c5460; }
        
        .payment-method { padding: 5px 10px; border-radius: 5px; font-size: 0.85em; font-weight: 600; background: #e7f3ff; color: #004085; }
        
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 5% auto; padding: 30px; border-radius: 10px; width: 80%; max-width: 600px; box-shadow: 0 5px 30px rgba(0,0,0,0.3); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: #000; }
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
        <a href="manage_taxis.php">🚕 Manage Taxis</a>
        <a href="manage_bookings.php">📋 Manage Bookings</a>
        <a href="manage_payments.php">💳 Manage Payments</a>
        <a href="manage_payments.php" class="active">💳 Manage Payments</a>
        <a href="reports.php">📄 Reports</a>
    </div>
    
    <div class="main-content">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <h2 style="margin-bottom: 20px;">Payment Management</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">💳</div>
                <div class="number"><?php echo $total_payments; ?></div>
                <div class="label">Total Payments</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">✅</div>
                <div class="number"><?php echo $completed_payments; ?></div>
                <div class="label">Completed</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">⏳</div>
                <div class="number"><?php echo $pending_payments; ?></div>
                <div class="label">Pending</div>
            </div>
            
            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="number">Rs<?php echo number_format($total_revenue, 2); ?></div>
                <div class="label">Total Revenue</div>
            </div>
        </div>
        
        <div class="card">
            <h2>➕ Add New Payment</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Select Booking *</label>
                        <select name="booking_id" required>
                            <option value="">-- Select Completed Booking --</option>
                            <?php 
                            mysqli_data_seek($bookings_result, 0);
                            while ($booking = mysqli_fetch_assoc($bookings_result)): 
                            ?>
                                <option value="<?php echo $booking['booking_id']; ?>">
                                    #<?php echo $booking['booking_id']; ?> - <?php echo $booking['full_name']; ?> - $<?php echo $booking['fare']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Amount (Rs) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="mobile_wallet">Mobile Wallet</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Transaction ID </label>
                        <input type="text" name="transaction_id" required placeholder="TXN-12345">
                    </div>
                </div>
                
                <button type="submit" name="add_payment" class="btn btn-primary">Add Payment</button>
            </form>
        </div>
        
        <div class="card">
            <h2>💳 All Payments</h2>

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
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                        <tr>
                            <td>#<?php echo $payment['payment_id']; ?></td>
                            <td>#<?php echo $payment['booking_id']; ?></td>
                            <td><?php echo $payment['passenger_name']; ?></td>
                            <td><?php echo $payment['driver_name'] ?: 'N/A'; ?></td>
                            <td><strong>Rs<?php echo number_format($payment['amount'], 2); ?></strong></td>
                            <td><span class="payment-method"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span></td>
                            <td><?php echo $payment['transaction_id']; ?></td>
                            <td><?php echo $payment['payment_date'] ? date('M d, Y h:i A', strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                            <td><span class="status status-<?php echo $payment['payment_status']; ?>"><?php echo ucfirst($payment['payment_status']); ?></span></td>
                            <td>
                                <?php if ($payment['payment_status'] == 'pending'): ?>
                                    <button class="btn btn-success" onclick="openUpdateModal(<?php echo $payment['payment_id']; ?>, '<?php echo $payment['passenger_name']; ?>', <?php echo $payment['amount']; ?>)">Update</button>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $payment['payment_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Update Payment Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeUpdateModal()">&times;</span>
            <h2>Update Payment Status</h2>
            <form method="POST" action="">
                <input type="hidden" id="modal_payment_id" name="payment_id">
                
                <div class="form-group">
                    <label>Payment Info</label>
                    <input type="text" id="modal_payment_info" readonly>
                </div>
                
                <div class="form-group">
                    <label>Payment Status *</label>
                    <select name="payment_status" required>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                
                <button type="submit" name="update_payment" class="btn btn-primary" style="width: 100%; padding: 12px;">Update Payment</button>
            </form>
        </div>
    </div>
    
    <script>
        function openUpdateModal(paymentId, passenger, amount) {
            document.getElementById('modal_payment_id').value = paymentId;
            document.getElementById('modal_payment_info').value = 'Payment #' + paymentId + ' - ' + passenger + ' - $' + amount;
            document.getElementById('updateModal').style.display = 'block';
        }
        
        function closeUpdateModal() {
            document.getElementById('updateModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('updateModal')) {
                closeUpdateModal();
            }
        }
    </script>
</body>
</html>
