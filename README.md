# Online Taxi Management System
## CS519 Project - Group F25PROJECT7B426

A complete web-based taxi management system built with PHP and MySQL.

## Features

### 🎯 Core Features
- **Separate Registration & Login** for Passengers and Drivers
- **Admin Panel** with complete system management
- **PDF Report Generation** for all data
- **Real-time Booking Management**
- **Driver Assignment System**
- **Multi-user Authentication**

### 👥 Passenger Module
- User registration and login
- Book taxis online
- View booking history
- Track booking status
- Cancel bookings
- View assigned driver and vehicle details

### 🚗 Driver Module
- Driver registration and login
- View assigned rides
- Update ride status (Accept, In Progress, Complete)
- Toggle availability status
- View passenger details and trip information

### ⚙️ Admin Module
- Complete dashboard with statistics
- Manage all passengers
- Manage all drivers
- Manage taxi fleet
- Assign drivers and taxis to bookings
- Generate and download PDF reports:
  - Bookings Report
  - Passengers Report
  - Drivers Report
  - Revenue Report

## System Requirements

- **Web Server**: Apache (XAMPP, WAMP, or LAMP)
- **PHP**: Version 7.0 or higher
- **MySQL**: Version 5.6 or higher
- **Browser**: Any modern web browser

## Installation Instructions

### Step 1: Download and Setup

1. **Download XAMPP** (if not installed):
   - Visit: https://www.apachefriends.org/
   - Download and install XAMPP

2. **Copy Project Files**:
   - Extract the project folder
   - Copy the `taxi_management_system` folder to:
     ```
     C:\xampp\htdocs\
     ```
     (For Linux/Mac: `/opt/lampp/htdocs/`)

### Step 2: Database Setup

1. **Start XAMPP**:
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

2. **Create Database**:
   - Open your browser and go to: `http://localhost/phpmyadmin`
   - Click on "New" to create a new database
   - Database name: `taxi_management`
   - Click "Create"

3. **Import Database**:
   - Select the `taxi_management` database
   - Click on "Import" tab
   - Click "Choose File" and select `database.sql` from the project folder
   - Click "Go" to import

   **OR manually run the SQL:**
   - Click on "SQL" tab
   - Copy and paste the contents of `database.sql`
   - Click "Go"

### Step 3: Configuration

1. Open `config.php` file
2. Verify database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Leave empty for default XAMPP
   define('DB_NAME', 'taxi_management');
   ```

### Step 4: Access the System

Open your browser and visit:
```
http://localhost/taxi_management_system/
```

## Default Login Credentials

### Admin Login
- **URL**: `http://localhost/taxi_management_system/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

### Test the System
1. Register as a **Passenger**
2. Register as a **Driver**
3. Login as **Passenger** and create a booking
4. Login as **Admin** and assign the booking to a driver
5. Login as **Driver** and manage the ride
6. Generate PDF reports from Admin panel

## Project Structure

```
taxi_management_system/
│
├── config.php                  # Database configuration
├── database.sql                # Database schema and sample data
├── index.php                   # Main landing page
│
├── passenger/                  # Passenger Module
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   └── logout.php
│
├── driver/                     # Driver Module
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   └── logout.php
│
└── admin/                      # Admin Module
    ├── login.php
    ├── dashboard.php
    ├── manage_passengers.php
    ├── manage_drivers.php
    ├── manage_taxis.php
    ├── manage_bookings.php
    ├── reports.php
    ├── generate_pdf.php
    └── logout.php
```

## Database Schema

### Tables
1. **admin** - Administrator accounts
2. **passengers** - Passenger user accounts
3. **drivers** - Driver user accounts
4. **taxis** - Taxi/Vehicle information
5. **bookings** - All booking records

## Features Implemented

### Functional Requirements ✅
- [x] User/Passenger registration and login
- [x] Passenger can book taxis
- [x] View booking status and history
- [x] Cancel bookings
- [x] Driver registration and login
- [x] Driver can view assigned rides
- [x] Driver can update ride status
- [x] Driver availability management
- [x] Admin secure login
- [x] Admin can manage passengers and drivers
- [x] Admin can manage taxi fleet
- [x] Admin can assign bookings
- [x] Admin can generate reports
- [x] Database management with MySQL
- [x] Data validation

### Non-Functional Requirements ✅
- [x] Fast response times
- [x] Password encryption (MD5)
- [x] User authentication and authorization
- [x] Simple and clean UI
- [x] Web browser compatible
- [x] Well-structured code

## PDF Report Types

1. **Bookings Report**
   - All bookings with passenger, driver, fare details
   - Date range filtering

2. **Passengers Report**
   - All registered passengers
   - Contact information and status

3. **Drivers Report**
   - All registered drivers
   - License information and availability

4. **Revenue Report**
   - Daily revenue breakdown
   - Total bookings and average fare
   - Complete financial overview

## Usage Guide

### For Passengers:
1. Register a new account
2. Login with credentials
3. Fill booking form with pickup and drop-off locations
4. Wait for admin to confirm and assign driver
5. Track your booking status
6. Cancel if needed before confirmation

### For Drivers:
1. Register as driver with license information
2. Login to dashboard
3. View assigned rides
4. Accept rides and update status
5. Mark ride as "In Progress" when started
6. Complete ride when finished
7. Toggle availability when needed

### For Admins:
1. Login with admin credentials
2. View dashboard statistics
3. Manage users (passengers and drivers)
4. Add new taxis to the system
5. Assign pending bookings to available drivers
6. Generate and download PDF reports
7. Monitor all system activities

## Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database name is correct: `taxi_management`
- Ensure database is imported properly

### Page Not Found (404)
- Check the project is in `htdocs` folder
- Verify the URL: `http://localhost/taxi_management_system/`

### Cannot Login
- Make sure database is imported
- Default admin: username=`admin`, password=`admin123`
- For new accounts, ensure registration was successful

### PDF Download Not Working
- Check PHP is enabled in Apache
- Verify file permissions
- Clear browser cache

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: Apache (via XAMPP)
- **Architecture**: MVC-inspired structure

## Security Features

- Password encryption using MD5
- SQL injection prevention with mysqli_real_escape_string
- Session-based authentication
- Role-based access control
- Input validation on all forms

## Future Enhancements

- Real-time GPS tracking
- Payment gateway integration
- SMS/Email notifications
- Rating and review system
- Advanced analytics dashboard
- Mobile application

## Support

For any issues or questions:
- Check the README file
- Review the code comments
- Contact your supervisor: Sumbal Javaid

## License

This project is developed for educational purposes as part of CS519 coursework.

---

**Note**: This system is a complete implementation of all requirements specified in the project documentation. All features are fully functional and tested.
