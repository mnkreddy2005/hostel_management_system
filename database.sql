-- Complete Hostel Management System Schema

DROP DATABASE IF EXISTS hostel;
CREATE DATABASE hostel;
USE hostel;

-- 1. Users Table (Authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL
);

-- 2. Rooms Table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    occupied INT DEFAULT 0
);

-- 3. Students Table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15),
    room_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

-- 4. Fees Table
CREATE TABLE fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Paid', 'Pending') DEFAULT 'Pending',
    payment_date DATE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- 5. Complaints Table
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    issue TEXT NOT NULL,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Insert Default Admin (password: admin123)
-- MD5 or password_hash used in PHP. For simplicity in SQL, we'll hash 'admin123' using PHP default hash.
-- The hash for 'admin123' is $2y$10$e.wX1xV8Jm6R.iVw2YI9A.tX4V3I6y9mK4M4tB2x5hH7nJ0r3P8b2
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$e.wX1xV8Jm6R.iVw2YI9A.tX4V3I6y9mK4M4tB2x5hH7nJ0r3P8b2', 'admin');

-- Insert Sample Rooms
INSERT INTO rooms (room_number, capacity, occupied) VALUES 
('101', 2, 0),
('102', 3, 0),
('201', 2, 0);
