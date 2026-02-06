<?php
require_once('../config.php');

function generate_pdf_report($report_type, $report_title, $data, $start_date, $end_date) {
    // Set headers for HTML to PDF rendering
    header('Content-Type: text/html; charset=utf-8');
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $report_title; ?></title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                font-size: 12px;
            }
            
            .header {
                text-align: center;
                border-bottom: 3px solid #667eea;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            
            .header h1 {
                color: #667eea;
                margin: 10px 0;
                font-size: 28px;
            }
            
            .header h2 {
                color: #333;
                margin: 5px 0;
                font-size: 20px;
            }
            
            .info {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            
            .info p {
                margin: 5px 0;
                color: #555;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            
            th {
                background: #667eea;
                color: white;
                padding: 12px;
                text-align: left;
                font-weight: bold;
            }
            
            td {
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }
            
            tr:nth-child(even) {
                background: #f8f9fa;
            }
            
            .status {
                padding: 5px 10px;
                border-radius: 5px;
                font-size: 11px;
                font-weight: bold;
            }
            
            .status-pending { background: #fff3cd; color: #856404; }
            .status-confirmed { background: #d1ecf1; color: #0c5460; }
            .status-accepted { background: #d4edda; color: #155724; }
            .status-completed { background: #d4edda; color: #155724; }
            .status-cancelled { background: #f8d7da; color: #721c24; }
            .status-active { background: #d4edda; color: #155724; }
            .status-inactive { background: #f8d7da; color: #721c24; }
            
            .footer {
                margin-top: 50px;
                text-align: center;
                color: #666;
                border-top: 2px solid #667eea;
                padding-top: 20px;
            }
            
            .summary {
                background: #e7f3ff;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
                border-left: 4px solid #667eea;
            }
            
            .summary strong {
                color: #667eea;
            }
            
            .print-btn {
                background: #667eea;
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                margin: 20px 0;
            }
            
            .print-btn:hover {
                background: #5568d3;
            }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()" class="print-btn">🖨️ Print / Save as PDF</button>
            <button onclick="window.close()" class="print-btn" style="background: #6c757d;">❌ Close</button>
        </div>
        
        <div class="header">
            <h1>🚕 ONLINE TAXI MANAGEMENT SYSTEM</h1>
            <h2><?php echo strtoupper($report_title); ?></h2>
        </div>
        
        <div class="info">
            <p><strong>Report Period:</strong> <?php echo date('F d, Y', strtotime($start_date)); ?> to <?php echo date('F d, Y', strtotime($end_date)); ?></p>
            <p><strong>Generated On:</strong> <?php echo date('F d, Y h:i A'); ?></p>
            <p><strong>Total Records:</strong> <?php echo count($data); ?></p>
        </div>
        
        <?php if (empty($data)): ?>
            <div class="summary">
                <p><strong>No data available for the selected criteria.</strong></p>
            </div>
        <?php else: ?>
            
            <?php if ($report_type == 'bookings'): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Passenger</th>
                            <th>Driver</th>
                            <th>Pickup Location</th>
                            <th>Drop-off Location</th>
                            <th>Date</th>
                            <th>Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_fare = 0;
                        foreach ($data as $row): 
                            $total_fare += $row['fare'];
                        ?>
                            <tr>
                                <td>#<?php echo $row['booking_id']; ?></td>
                                <td><?php echo $row['passenger_name']; ?></td>
                                <td><?php echo $row['driver_name'] ?: 'Not Assigned'; ?></td>
                                <td><?php echo $row['pickup_location']; ?></td>
                                <td><?php echo $row['dropoff_location']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td>$<?php echo number_format($row['fare'], 2); ?></td>
                                <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <p><strong>Total Bookings:</strong> <?php echo count($data); ?></p>
                    <p><strong>Total Revenue:</strong> $<?php echo number_format($total_fare, 2); ?></p>
                </div>
            
            <?php elseif ($report_type == 'passengers'): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
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
                                <td><?php echo $row['address'] ?: 'N/A'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <p><strong>Total Passengers:</strong> <?php echo count($data); ?></p>
                </div>
            
            <?php elseif ($report_type == 'drivers'): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
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
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <p><strong>Total Drivers:</strong> <?php echo count($data); ?></p>
                </div>
            
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
                                <td><?php echo date('F d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo $row['total_bookings']; ?></td>
                                <td>$<?php echo number_format($row['total_revenue'], 2); ?></td>
                                <td>$<?php echo number_format($row['avg_fare'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #667eea; color: white; font-weight: bold;">
                            <td>TOTAL</td>
                            <td><?php echo $total_bookings; ?></td>
                            <td>$<?php echo number_format($total_revenue, 2); ?></td>
                            <td>$<?php echo $total_bookings > 0 ? number_format($total_revenue / $total_bookings, 2) : '0.00'; ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="summary">
                    <p><strong>Total Bookings:</strong> <?php echo $total_bookings; ?></p>
                    <p><strong>Total Revenue:</strong> $<?php echo number_format($total_revenue, 2); ?></p>
                    <p><strong>Average Fare:</strong> $<?php echo $total_bookings > 0 ? number_format($total_revenue / $total_bookings, 2) : '0.00'; ?></p>
                </div>
            
            <?php elseif ($report_type == 'payments'): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Booking</th>
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
                                <td><?php echo date('F d, Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #667eea; color: white; font-weight: bold;">
                            <td colspan="4">TOTAL</td>
                            <td>$<?php echo number_format($total_amount, 2); ?></td>
                            <td colspan="4"><?php echo count($data); ?> Payments</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="summary">
                    <p><strong>Total Payments:</strong> <?php echo count($data); ?></p>
                    <p><strong>Total Amount:</strong> $<?php echo number_format($total_amount, 2); ?></p>
                    <p><strong>Average Payment:</strong> $<?php echo count($data) > 0 ? number_format($total_amount / count($data), 2) : '0.00'; ?></p>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <div class="footer">
            <p><strong>Online Taxi Management System</strong></p>
            <p>Group ID: F25PROJECT7B426 | Supervisor: Sumbal Javaid</p>
            <p>This is a computer-generated report</p>
        </div>
        
        <script>
            // Auto-print on load (optional - remove if you don't want auto-print)
            // window.onload = function() { window.print(); }
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>
