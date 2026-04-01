CREATE DATABASE IF NOT EXISTS hostel_management;
USE hostel_management;

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Seed an admin account
INSERT INTO admins (username, password) VALUES ('admin', 'admin123') ON DUPLICATE KEY UPDATE id=id;

-- Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    occupied INT DEFAULT 0
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    room_id INT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

-- Complaints Table
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    issue TEXT NOT NULL,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
