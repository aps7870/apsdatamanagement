<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

init_session();

// Get student ID from URL parameter
$student_id = isset($_GET['student_id']) ? sanitize_input($_GET['student_id']) : '';

if (!$student_id) {
    die("No student selected");
}

// Get student details
$student_data = get_student_details($student_id);

if (!$student_data) {
    die("Student not found");
}

// Get exam results
$query = "SELECT * FROM exam_results 
          WHERE student_id = '$student_id' 
          ORDER BY term, subject";
$result = mysqli_query($conn, $query);
$exam_results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $exam_results[$row['term']][] = $row;
}

// Get attendance
$query = "SELECT * FROM attendance 
          WHERE student_id = '$student_id' 
          ORDER BY term";
$result = mysqli_query($conn, $query);
$attendance = [];
while ($row = mysqli_fetch_assoc($result)) {
    $attendance[$row['term']] = $row;
}

// Get fee history
$query = "SELECT * FROM fees 
          WHERE student_id = '$student_id' 
          ORDER BY fee_date DESC";
$result = mysqli_query($conn, $query);
$fee_history = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fee_history[] = $row;
}

// Set headers for Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=student_data_" . $student_data['admission_no'] . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Start Excel file content
echo "<table border='1'>";

// Student Information
echo "<tr><td colspan='4' style='background-color: #e6e6e6;'><strong>Student Information</strong></td></tr>";
echo "<tr>
    <td><strong>Admission No</strong></td>
    <td>" . htmlspecialchars($student_data['admission_no']) . "</td>
    <td><strong>Admission Date</strong></td>
    <td>" . date('d-m-Y', strtotime($student_data['admission_date'])) . "</td>
</tr>";
echo "<tr>
    <td><strong>Student Name</strong></td>
    <td>" . htmlspecialchars($student_data['student_name']) . "</td>
    <td><strong>Class & Section</strong></td>
    <td>" . htmlspecialchars($student_data['class'] . ' - ' . $student_data['section']) . "</td>
</tr>";
echo "<tr>
    <td><strong>Date of Birth</strong></td>
    <td>" . date('d-m-Y', strtotime($student_data['date_of_birth'])) . "</td>
    <td><strong>Gender</strong></td>
    <td>" . htmlspecialchars($student_data['gender']) . "</td>
</tr>";
echo "<tr>
    <td><strong>Father's Name</strong></td>
    <td>" . htmlspecialchars($student_data['father_name']) . "</td>
    <td><strong>Mother's Name</strong></td>
    <td>" . htmlspecialchars($student_data['mother_name']) . "</td>
</tr>";
echo "<tr>
    <td><strong>Address</strong></td>
    <td colspan='3'>" . htmlspecialchars($student_data['address']) . "</td>
</tr>";

if ($student_data['bus_facility']) {
    echo "<tr>
        <td><strong>Bus Facility</strong></td>
        <td>Yes</td>
        <td><strong>Bus Stoppage</strong></td>
        <td>" . htmlspecialchars($student_data['bus_stoppage']) . "</td>
    </tr>";
}

// Add empty row for spacing
echo "<tr><td colspan='4'></td></tr>";

// Exam Results
foreach (['1st', '2nd', '3rd'] as $term) {
    if (isset($exam_results[$term])) {
        echo "<tr><td colspan='9' style='background-color: #e6e6e6;'><strong>" . $term . " Term Results</strong></td></tr>";
        echo "<tr>
            <th>Subject</th>
            <th>FA1</th>
            <th>FA2</th>
            <th>FA Avg</th>
            <th>SA1</th>
            <th>Practical</th>
            <th>Notebook</th>
            <th>Total</th>
            <th>Grade</th>
        </tr>";

        foreach ($exam_results[$term] as $result) {
            echo "<tr>
                <td>" . htmlspecialchars($result['subject']) . "</td>
                <td>" . $result['fa1_marks'] . "</td>
                <td>" . $result['fa2_marks'] . "</td>
                <td>" . $result['fa_average'] . "</td>
                <td>" . $result['sa1_marks'] . "</td>
                <td>" . $result['practical_marks'] . "</td>
                <td>" . $result['notebook_marks'] . "</td>
                <td>" . $result['total_marks'] . "</td>
                <td>" . $result['grade'] . "</td>
            </tr>";
        }

        if (isset($attendance[$term])) {
            echo "<tr><td colspan='9'>";
            echo "<strong>Attendance:</strong> " . 
                 $attendance[$term]['present_days'] . " / " . 
                 $attendance[$term]['total_working_days'] . 
                 " (" . number_format($attendance[$term]['attendance_percentage'], 2) . "%)";
            echo "</td></tr>";
        }

        // Add empty row for spacing
        echo "<tr><td colspan='9'></td></tr>";
    }
}

// Fee History
if (!empty($fee_history)) {
    echo "<tr><td colspan='6' style='background-color: #e6e6e6;'><strong>Fee History</strong></td></tr>";
    echo "<tr>
        <th>Date</th>
        <th>Month</th>
        <th>Tuition Fee</th>
        <th>Transport Fee</th>
        <th>Late Fee</th>
        <th>Total</th>
    </tr>";

    foreach ($fee_history as $fee) {
        echo "<tr>
            <td>" . date('d-m-Y', strtotime($fee['fee_date'])) . "</td>
            <td>" . htmlspecialchars($fee['month']) . "</td>
            <td>" . format_currency($fee['tuition_fee']) . "</td>
            <td>" . format_currency($fee['transport_fee']) . "</td>
            <td>" . format_currency($fee['late_fee']) . "</td>
            <td>" . format_currency($fee['total_fee']) . "</td>
        </tr>";
    }
}

echo "</table>";
?>


    
