-- Online Taxi Management System Database
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
