<?php
// Update the path to point to the vendor directory in your project
require_once __DIR__ . '/vendor/autoload.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// No session or role checks

if (!isset($_POST['student_id'])) {
    die('No student selected');
}

$student_id = sanitize_input($_POST['student_id']);

// Get student details
$student_data = get_student_details($student_id);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('Aparna Public School')
    ->setTitle('Student Data - ' . $student_data['student_name'])
    ->setSubject('Student Academic Record')
    ->setDescription('Academic record for ' . $student_data['student_name']);

// Add Student Information
$sheet->setCellValue('A1', 'STUDENT INFORMATION');
$sheet->mergeCells('A1:D1');
$sheet->getStyle('A1')->getFont()->setBold(true);

// Basic Information
$row = 2;
$basicInfo = [
    ['Admission No', $student_data['admission_no'], 'Admission Date', date('d-m-Y', strtotime($student_data['admission_date']))],
    ['Student Name', $student_data['student_name'], 'Class & Section', $student_data['class'] . ' - ' . $student_data['section']],
    ['Date of Birth', date('d-m-Y', strtotime($student_data['date_of_birth'])), 'Gender', $student_data['gender']],
    ['Father\'s Name', $student_data['father_name'], 'Mother\'s Name', $student_data['mother_name']],
    ['Address', $student_data['address'], '', '']
];

foreach ($basicInfo as $info) {
    $sheet->setCellValue('A' . $row, $info[0]);
    $sheet->setCellValue('B' . $row, $info[1]);
    $sheet->setCellValue('C' . $row, $info[2]);
    $sheet->setCellValue('D' . $row, $info[3]);
    $row++;
}

// Get exam results
$query = "SELECT * FROM exam_results WHERE student_id = ? ORDER BY term, subject";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$exam_results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $exam_results[$row['term']][] = $row;
}

// Add Exam Results
$currentRow = $row + 2;
foreach (['1st', '2nd', '3rd'] as $term) {
    if (isset($exam_results[$term])) {
        $sheet->setCellValue('A' . $currentRow, $term . ' TERM RESULTS');
        $sheet->mergeCells('A' . $currentRow . ':I' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        // Headers
        $headers = ['Subject', 'FA1', 'FA2', 'FA Avg', 'SA1', 'Practical', 'Notebook', 'Total', 'Grade'];
        foreach (range('A', 'I') as $index => $col) {
            $sheet->setCellValue($col . $currentRow, $headers[$index]);
            $sheet->getStyle($col . $currentRow)->getFont()->setBold(true);
        }
        $currentRow++;

        // Data
        foreach ($exam_results[$term] as $result) {
            $sheet->setCellValue('A' . $currentRow, $result['subject']);
            $sheet->setCellValue('B' . $currentRow, $result['fa1_marks']);
            $sheet->setCellValue('C' . $currentRow, $result['fa2_marks']);
            $sheet->setCellValue('D' . $currentRow, $result['fa_average']);
            $sheet->setCellValue('E' . $currentRow, $result['sa1_marks']);
            $sheet->setCellValue('F' . $currentRow, $result['practical_marks']);
            $sheet->setCellValue('G' . $currentRow, $result['notebook_marks']);
            $sheet->setCellValue('H' . $currentRow, $result['total_marks']);
            $sheet->setCellValue('I' . $currentRow, $result['grade']);
            $currentRow++;
        }
        $currentRow += 2;
    }
}

// Auto-size columns
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="student_data_' . $student_data['admission_no'] . '_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

// Save file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 