-- Users table for teachers and administrators
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'admin') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admission_no VARCHAR(20) UNIQUE NOT NULL,
    admission_date DATE NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    class VARCHAR(10) NOT NULL,
    section VARCHAR(2) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    student_aadhar VARCHAR(12),
    father_name VARCHAR(100),
    father_aadhar VARCHAR(12),
    mother_name VARCHAR(100),
    mother_aadhar VARCHAR(12),
    address TEXT,
    bus_facility BOOLEAN DEFAULT FALSE,
    bus_stoppage VARCHAR(100),
    bus_fare DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Examination results table
CREATE TABLE IF NOT EXISTS exam_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    class VARCHAR(10),
    term ENUM('1st', '2nd', '3rd'),
    subject VARCHAR(50),
    fa1_marks DECIMAL(5,2),
    fa2_marks DECIMAL(5,2),
    fa_average DECIMAL(5,2),
    sa1_marks DECIMAL(5,2),
    notebook_marks DECIMAL(5,2),
    practical_marks DECIMAL(5,2),
    total_marks DECIMAL(5,2),
    grade VARCHAR(2),
    academic_year VARCHAR(10),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    term ENUM('1st', '2nd', '3rd'),
    total_working_days INT,
    present_days INT,
    attendance_percentage DECIMAL(5,2),
    academic_year VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Fees table
CREATE TABLE IF NOT EXISTS fees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    month VARCHAR(20),
    fee_date DATE,
    tuition_fee DECIMAL(10,2),
    transport_fee DECIMAL(10,2),
    late_fee DECIMAL(10,2),
    total_fee DECIMAL(10,2),
    payment_status ENUM('Paid', 'Pending') DEFAULT 'Pending',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert default admin users
INSERT INTO users (username, password, role, full_name) VALUES
('arunab', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Arunab Kr. Mahato'),
('rohit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Rohit Kr. Mahato');

-- Insert default teacher users
INSERT INTO users (username, password, role, full_name) VALUES
('palak', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Palak Gupta'),
('sunham', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Sunham Sen'),
('rinki', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Rinki Gupta'),
('manju', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Manju Mahato'),
('giribala', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Giribala Mahato'),
('lakhan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Lakhan Mahato'),
('padma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Padma Mahato'),
('ranjana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Ranjana Kumari'); 