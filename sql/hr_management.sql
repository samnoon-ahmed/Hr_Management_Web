-- hr_management.sql: Minimal DB schema for HR Management Web App

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','employee'),
    status ENUM('active','inactive') DEFAULT 'active'
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    dob DATE,
    email VARCHAR(100),
    phone VARCHAR(20),
    designation VARCHAR(100),
    department VARCHAR(100),
    join_date DATE,
    status ENUM('active','inactive') DEFAULT 'active'
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    date DATE,
    in_time TIME,
    out_time TIME,
    location VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE leave_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reason TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('pending','approved','cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    description TEXT,
    date DATE,
    status ENUM('pending','approved','cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    published_date DATE
);

-- Example users for initial setup
INSERT INTO users (name, email, password, role, status) VALUES
('Admin User', 'admin@example.com', '$2y$10$tT7s2BZ7utRG3bRC3kxIoeLzk2oYJ1ZmN16V52ghjRBdJRYtoVAp.', 'admin', 'active'),
('Employee User', 'employee@example.com', '$2y$10$p/Fyzz83d/H5BhmNJCCLluyZU3us8n3ncALoJwp6iqpV8p.K/DsRS', 'employee', 'active');

-- Passwords: admin123 (admin), emp123 (employee) 