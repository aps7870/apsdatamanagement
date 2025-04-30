<?php
// Database configuration
define(constant_name: 'DB_HOST', value: 'localhost');
define('DB_USER', 'root');     // Change this to your MySQL username
define('DB_PASS', '');         // Change this to your MySQL password
define('DB_NAME', 'aparna_school'); // Change this to your database name

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?> 