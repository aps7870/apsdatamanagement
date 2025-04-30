<?php
// Start session if not already started
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    init_session();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user role
function check_role($required_role) {
    init_session();
    return isset($_SESSION['role']) && $_SESSION['role'] === $required_role;
}

// Redirect with message
function redirect($location, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $location");
    exit();
}

// Display flash message
function display_message() {
    if (isset($_SESSION['message'])) {
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        return "<div class='alert alert-{$type}'>{$message}</div>";
    }
    return '';
}

// Sanitize input
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Calculate student age from DOB
function calculate_age($dob) {
    $birth_date = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birth_date->diff($today)->y;
    return $age;
}

// Get class fee based on class name
function get_class_fee($class) {
    $fees = [
        'NURSERY' => 600,
        'LKG' => 600,
        'UKG' => 600,
        'I' => 800,
        'II' => 850,
        'III' => 900,
        'IV' => 950,
        'V' => 1000,
        'VI' => 1000,
        'VII' => 1000,
        'VIII' => 1000,
        'IX' => 1000,
        'X' => 1000
    ];
    
    return isset($fees[$class]) ? $fees[$class] : 1000;
}

// Calculate late fee
function calculate_late_fee($payment_date) {
    $payment_day = (int)date('d', strtotime($payment_date));
    return ($payment_day > 15) ? 50 : 0;
}

// Get all classes
function get_all_classes() {
    return [
        'NURSERY', 'LKG', 'UKG',
        'I', 'II', 'III', 'IV', 'V',
        'VI', 'VII', 'VIII', 'IX', 'X'
    ];
}

// Get all sections
function get_all_sections() {
    return ['A', 'B', 'C'];
}

// Get all months
function get_all_months() {
    return [
        'January', 'February', 'March', 'April',
        'May', 'June', 'July', 'August',
        'September', 'October', 'November', 'December'
    ];
}

// Validate Aadhar number
function validate_aadhar($aadhar) {
    return preg_match('/^[0-9]{12}$/', $aadhar);
}

// Format currency
function format_currency($amount) {
    return '₹' . number_format($amount, 2);
}

// Get student details by ID
function get_student_details($student_id) {
    global $conn;
    $student_id = sanitize_input($student_id);
    $query = "SELECT * FROM students WHERE id = '$student_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Calculate exam result statistics
function calculate_exam_stats($student_id, $term) {
    global $conn;
    $student_id = sanitize_input($student_id);
    $term = sanitize_input($term);
    
    $query = "SELECT 
                SUM(total_marks) as total_obtained,
                COUNT(*) as total_subjects,
                AVG(total_marks) as average
              FROM exam_results 
              WHERE student_id = '$student_id' 
              AND term = '$term'";
    
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Get attendance percentage
function get_attendance_percentage($student_id, $term) {
    global $conn;
    $student_id = sanitize_input($student_id);
    $term = sanitize_input($term);
    
    $query = "SELECT attendance_percentage 
              FROM attendance 
              WHERE student_id = '$student_id' 
              AND term = '$term'";
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['attendance_percentage'] : 0;
}
?> 