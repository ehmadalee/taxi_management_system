-- Online Taxi Management System Database with Payments
-- Created for CS519 Project

CREATE DATABASE IF NOT EXISTS taxi_management;
USE taxi_management;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Passengers Table
CREATE TABLE IF NOT EXISTS passengers (
    passenger_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Drivers Table
CREATE TABLE IF NOT EXISTS drivers (
    driver_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    address TEXT,
    availability ENUM('available', 'not_available') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Taxis Table
CREATE TABLE IF NOT EXISTS taxis (
    taxi_id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_number VARCHAR(20) UNIQUE NOT NULL,
    model VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    year INT,
    color VARCHAR(30),
    status ENUM('available', 'in_use', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    passenger_id INT NOT NULL,
    driver_id INT,
    taxi_id INT,
    pickup_location VARCHAR(255) NOT NULL,
    dropoff_location VARCHAR(255) NOT NULL,
    booking_time DATETIME NOT NULL,
    pickup_time DATETIME,
    status ENUM('pending', 'confirmed', 'accepted', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    fare DECIMAL(10, 2),
    distance DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL,
    FOREIGN KEY (taxi_id) REFERENCES taxis(taxi_id) ON DELETE SET NULL
);

-- Payments Table (NEW)
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    passenger_id INT NOT NULL,
    driver_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'mobile_wallet', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    payment_date DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL
);

-- Insert Default Admin
INSERT INTO admin (username, password, email, full_name) 
VALUES ('admin', MD5('admin123'), 'admin@taxistand.com', 'System Administrator');

-- Insert Sample Taxis
INSERT INTO taxis (vehicle_number, model, capacity, year, color, status) VALUES
('ABC-1234', 'Toyota Camry', 4, 2022, 'White', 'available'),
('XYZ-5678', 'Honda Accord', 4, 2021, 'Black', 'available'),
('DEF-9012', 'Hyundai Elantra', 4, 2023, 'Silver', 'available'),
('GHI-3456', 'Ford Fusion', 4, 2020, 'Blue', 'available'),
('JKL-7890', 'Nissan Altima', 4, 2022, 'Red', 'available');

-- Insert Sample Passengers
INSERT INTO passengers (username, password, full_name, email, phone, address, status) VALUES
('john_doe', MD5('password123'), 'John Doe', 'john@example.com', '555-0101', '123 Main St', 'active'),
('jane_smith', MD5('password123'), 'Jane Smith', 'jane@example.com', '555-0102', '456 Oak Ave', 'active');

-- Insert Sample Drivers
INSERT INTO drivers (username, password, full_name, email, phone, license_number, address, availability, status) VALUES
('driver_mike', MD5('password123'), 'Mike Johnson', 'mike@example.com', '555-0201', 'DL-12345', '789 Pine St', 'available', 'active'),
('driver_sarah', MD5('password123'), 'Sarah Williams', 'sarah@example.com', '555-0202', 'DL-67890', '321 Elm St', 'available', 'active');

-- Insert Sample Bookings
INSERT INTO bookings (passenger_id, driver_id, taxi_id, pickup_location, dropoff_location, booking_time, status, fare, distance) VALUES
(1, 1, 1, '123 Main St, Downtown', '456 Business Plaza', '2026-02-05 09:00:00', 'completed', 25.50, 8.5),
(2, 2, 2, '789 Shopping Mall', '321 Airport Terminal', '2026-02-05 10:30:00', 'completed', 45.00, 15.2),
(1, 1, 3, '555 Hotel Street', '888 Convention Center', '2026-02-05 14:00:00', 'in_progress', 30.00, 10.0);

-- Insert Sample Payments
INSERT INTO payments (booking_id, passenger_id, driver_id, amount, payment_method, payment_status, transaction_id, payment_date) VALUES
(1, 1, 1, 25.50, 'credit_card', 'completed', 'TXN-2026020501', '2026-02-05 09:30:00'),
(2, 2, 2, 45.00, 'cash', 'completed', 'TXN-2026020502', '2026-02-05 11:00:00'),
(3, 1, 1, 30.00, 'mobile_wallet', 'pending', 'TXN-2026020503', NULL);
