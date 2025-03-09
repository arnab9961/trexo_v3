-- Tourism Management System Database

-- Create Database
CREATE DATABASE IF NOT EXISTS tourism_db;
USE tourism_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    user_type ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Destinations Table
CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Package_Destinations (Junction Table)
CREATE TABLE IF NOT EXISTS package_destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    destination_id INT NOT NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT,
    destination_id INT,
    booking_date DATE NOT NULL,
    travel_date DATE NOT NULL,
    num_travelers INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT,
    destination_id INT,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
);

-- Inquiries Table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Admin User
INSERT INTO users (username, password, email, full_name, user_type) 
VALUES ('admin', '$2y$10$8WxYR0AIj3UK/0TvNrQTVeQQV7d6WkKEG9.zBGQCrIVMimRnO5F0a', 'admin@tourism.com', 'Admin User', 'admin');

-- Insert Sample Destinations
INSERT INTO destinations (name, description, location, image, price, featured) VALUES
('Beach Paradise', 'Beautiful beaches with crystal clear water', 'Maldives', 'maldives.jpg', 1200.00, TRUE),
('Mountain Retreat', 'Peaceful mountain getaway with stunning views', 'Switzerland', 'switzerland.jpg', 1500.00, TRUE),
('Historical City', 'Explore ancient ruins and historical landmarks', 'Rome, Italy', 'rome.jpg', 900.00, FALSE),
('Tropical Jungle', 'Adventure through lush tropical rainforests', 'Amazon, Brazil', 'amazon.jpg', 1100.00, FALSE),
('Desert Safari', 'Experience the beauty of vast desert landscapes', 'Dubai, UAE', 'dubai.jpg', 800.00, TRUE);

-- Insert Sample Packages
INSERT INTO packages (name, description, price, duration, image, featured) VALUES
('Weekend Getaway', 'Perfect short break for busy professionals', 500.00, '3 days', 'weekend.jpg', TRUE),
('Family Adventure', 'Fun activities for the whole family', 2000.00, '7 days', 'family.jpg', TRUE),
('Romantic Escape', 'Ideal for couples looking for a romantic holiday', 1800.00, '5 days', 'romantic.jpg', FALSE),
('Backpacker Special', 'Budget-friendly tour for adventurous souls', 600.00, '10 days', 'backpacker.jpg', FALSE),
('Luxury Experience', 'Premium package with 5-star accommodations', 3500.00, '7 days', 'luxury.jpg', TRUE);

-- Connect Packages with Destinations
INSERT INTO package_destinations (package_id, destination_id) VALUES
(1, 1), (1, 5),
(2, 2), (2, 3), (2, 4),
(3, 1), (3, 2),
(4, 3), (4, 4),
(5, 1), (5, 2), (5, 5); 