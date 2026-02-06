<?php
function generate_pdf_report($report_type, $report_title, $data, $start_date, $end_date) {
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $report_title) . '_' . date('Y-m-d') . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Since we can't use external PDF libraries, we'll generate a well-formatted text report
    // that browsers will prompt to download
    
    $output = "";
    $output .= "=========================================================\n";
    $output .= "        ONLINE TAXI MANAGEMENT SYSTEM\n";
    $output .= "              " . strtoupper($report_title) . "\n";
    $output .= "=========================================================\n\n";
    $output .= "Report Period: " . date('M d, Y', strtotime($start_date)) . " - " . date('M d, Y', strtotime($end_date)) . "\n";
    $output .= "Generated on: " . date('M d, Y h:i A') . "\n\n";
    $output .= "---------------------------------------------------------\n\n";
    
    if (empty($data)) {
        $output .= "No data available for the selected criteria.\n";
    } else {
        if ($report_type == 'bookings') {
            $output .= sprintf("%-8s %-20s %-20s %-15s %-15s\n", "ID", "Passenger", "Driver", "Fare", "Status");
            $output .= str_repeat("-", 100) . "\n";
            
            foreach ($data as $row) {
                $output .= sprintf(
                    "%-8s %-20s %-20s %-15s %-15s\n",
                    "#" . $row['booking_id'],
                    substr($row['passenger_name'], 0, 20),
                    substr($row['driver_name'] ?: 'Not Assigned', 0, 20),
                    "$" . number_format($row['fare'], 2),
                    ucfirst($row['status'])
                );
                $output .= "  Pickup: " . $row['pickup_location'] . "\n";
                $output .= "  Drop-off: " . $row['dropoff_location'] . "\n";
                $output .= "  Date: " . date('M d, Y h:i A', strtotime($row['booking_time'])) . "\n\n";
            }
            
            $output .= "\nTotal Bookings: " . count($data) . "\n";
            
        } elseif ($report_type == 'passengers') {
            $output .= sprintf("%-8s %-25s %-30s %-15s %-15s\n", "ID", "Name", "Email", "Phone", "Status");
            $output .= str_repeat("-", 100) . "\n";
            
            foreach ($data as $row) {
                $output .= sprintf(
                    "%-8s %-25s %-30s %-15s %-15s\n",
                    "#" . $row['passenger_id'],
                    substr($row['full_name'], 0, 25),
                    substr($row['email'], 0, 30),
                    $row['phone'],
                    ucfirst($row['status'])
                );
            }
            
            $output .= "\nTotal Passengers: " . count($data) . "\n";
            
        } elseif ($report_type == 'drivers') {
            $output .= sprintf("%-8s %-25s %-30s %-15s %-15s\n", "ID", "Name", "Email", "License", "Status");
            $output .= str_repeat("-", 100) . "\n";
            
            foreach ($data as $row) {
                $output .= sprintf(
                    "%-8s %-25s %-30s %-15s %-15s\n",
                    "#" . $row['driver_id'],
                    substr($row['full_name'], 0, 25),
                    substr($row['email'], 0, 30),
                    $row['license_number'],
                    ucfirst($row['status'])
                );
            }
            
            $output .= "\nTotal Drivers: " . count($data) . "\n";
            
        } elseif ($report_type == 'revenue') {
            $output .= sprintf("%-15s %-15s %-15s %-15s\n", "Date", "Bookings", "Revenue", "Avg Fare");
            $output .= str_repeat("-", 70) . "\n";
            
            $total_bookings = 0;
            $total_revenue = 0;
            
            foreach ($data as $row) {
                $total_bookings += $row['total_bookings'];
                $total_revenue += $row['total_revenue'];
                
                $output .= sprintf(
                    "%-15s %-15s %-15s %-15s\n",
                    date('M d, Y', strtotime($row['date'])),
                    $row['total_bookings'],
                    "$" . number_format($row['total_revenue'], 2),
                    "$" . number_format($row['avg_fare'], 2)
                );
            }
            
            $output .= str_repeat("-", 70) . "\n";
            $output .= sprintf(
                "%-15s %-15s %-15s %-15s\n",
                "TOTAL",
                $total_bookings,
                "$" . number_format($total_revenue, 2),
                "$" . ($total_bookings > 0 ? number_format($total_revenue / $total_bookings, 2) : "0.00")
            );
        }
    }
    
    $output .= "\n\n";
    $output .= "=========================================================\n";
    $output .= "       End of Report - Taxi Management System\n";
    $output .= "=========================================================\n";
    
    echo $output;
}
?>
