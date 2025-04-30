<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

init_session();

$classes = get_all_classes();
$sections = get_all_sections();

$selected_class = isset($_POST['class']) ? sanitize_input($_POST['class']) : '';
$selected_section = isset($_POST['section']) ? sanitize_input($_POST['section']) : '';
$selected_student = isset($_POST['student']) ? sanitize_input($_POST['student']) : '';

// Get students based on class and section
$students = [];
if ($selected_class && $selected_section) {
    $query = "SELECT id, student_name FROM students 
              WHERE class = '$selected_class' 
              AND section = '$selected_section' 
              ORDER BY student_name";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $students[$row['id']] = $row['student_name'];
    }
}

// Get student details if selected
$student_data = null;
$exam_results = [];
$attendance = [];
$fee_history = [];

if ($selected_student) {
    // Get student details
    $student_data = get_student_details($selected_student);

    // Get exam results
    $query = "SELECT * FROM exam_results 
              WHERE student_id = '$selected_student' 
              ORDER BY term, subject";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $exam_results[$row['term']][] = $row;
    }

    // Get attendance
    $query = "SELECT * FROM attendance 
              WHERE student_id = '$selected_student' 
              ORDER BY term";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $attendance[$row['term']] = $row;
    }

    // Get fee history
    $query = "SELECT * FROM fees 
              WHERE student_id = '$selected_student' 
              ORDER BY fee_date DESC";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $fee_history[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Data - Aparna Public School</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container">
        <header>
            <h1>APARNA PUBLIC SCHOOL</h1>
            <p>View Student Data</p>
        </header>

        <main>
            <div class="form-container" style="max-width: 1000px;">
                <form method="POST" action="" id="studentSelectForm">
                    <div class="form-group">
                        <label for="class">Select Class</label>
                        <select name="class" id="class" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class; ?>" <?php echo $selected_class === $class ? 'selected' : ''; ?>>
                                    <?php echo $class; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="section">Select Section</label>
                        <select name="section" id="section" required>
                            <option value="">Select Section</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section; ?>" <?php echo $selected_section === $section ? 'selected' : ''; ?>>
                                    <?php echo $section; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($students)): ?>
                        <div class="form-group">
                            <label for="student">Select Student</label>
                            <select name="student" id="student" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $selected_student == $id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="view_data">View Data</button>
                </form>

                <?php if ($student_data): ?>
                    <div style="margin-top: 40px;">
                        <h2>Student Information</h2>
                        <!-- <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['teacher', 'admin'])): ?> -->

                            <!-- <?php endif; ?> -->
                        <div class="table-container">
                            <table>
                                <tr>
                                    <th>Admission No</th>
                                    <td><?php echo htmlspecialchars($student_data['admission_no']); ?></td>
                                    <th>Admission Date</th>
                                    <td><?php echo date('d-m-Y', strtotime($student_data['admission_date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Student Name</th>
                                    <td><?php echo htmlspecialchars($student_data['student_name']); ?></td>
                                    <th>Class & Section</th>
                                    <td><?php echo htmlspecialchars($student_data['class'] . ' - ' . $student_data['section']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date of Birth</th>
                                    <td><?php echo date('d-m-Y', strtotime($student_data['date_of_birth'])); ?></td>
                                    <th>Gender</th>
                                    <td><?php echo htmlspecialchars($student_data['gender']); ?></td>
                                </tr>
                                <tr>
                                    <th>Father's Name</th>
                                    <td><?php echo htmlspecialchars($student_data['father_name']); ?></td>
                                    <th>Mother's Name</th>
                                    <td><?php echo htmlspecialchars($student_data['mother_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td colspan="3"><?php echo htmlspecialchars($student_data['address']); ?></td>
                                </tr>
                                <?php if ($student_data['bus_facility']): ?>
                                    <tr>
                                        <th>Bus Facility</th>
                                        <td>Yes</td>
                                        <th>Bus Stoppage</th>
                                        <td><?php echo htmlspecialchars($student_data['bus_stoppage']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                            <div style="text-align: center; margin-bottom: 20px; margin-top: 30px;">
                                <a href="export.php?student_id=<?php echo $selected_student; ?>"><button type="button" class="download-btn">
                                    Download Student Data (Excel)
                                    </button></a>
                            </div>
                        </div>

                        <?php foreach (['1st', '2nd', '3rd'] as $term): ?>
                            <?php if (isset($exam_results[$term])): ?>
                                <h3 style="margin-top: 30px;"><?php echo $term; ?> Term Results</h3>
                                <div class="table-container">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>FA1</th>
                                                <th>FA2</th>
                                                <th>FA Avg</th>
                                                <th>SA1</th>
                                                <th>Practical</th>
                                                <th>Notebook</th>
                                                <th>Total</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($exam_results[$term] as $result): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($result['subject']); ?></td>
                                                    <td><?php echo $result['fa1_marks']; ?></td>
                                                    <td><?php echo $result['fa2_marks']; ?></td>
                                                    <td><?php echo $result['fa_average']; ?></td>
                                                    <td><?php echo $result['sa1_marks']; ?></td>
                                                    <td><?php echo $result['practical_marks']; ?></td>
                                                    <td><?php echo $result['notebook_marks']; ?></td>
                                                    <td><?php echo $result['total_marks']; ?></td>
                                                    <td><?php echo $result['grade']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if (isset($attendance[$term])): ?>
                                    <div style="margin-top: 20px;">
                                        <p><strong>Attendance:</strong>
                                            <?php echo $attendance[$term]['present_days']; ?> /
                                            <?php echo $attendance[$term]['total_working_days']; ?>
                                            (<?php echo number_format($attendance[$term]['attendance_percentage'], 2); ?>%)
                                        </p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (!empty($fee_history)): ?>
                            <h3 style="margin-top: 30px;">Fee History</h3>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Month</th>
                                            <th>Tuition Fee</th>
                                            <th>Transport Fee</th>
                                            <th>Late Fee</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fee_history as $fee): ?>
                                            <tr>
                                                <td><?php echo date('d-m-Y', strtotime($fee['fee_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($fee['month']); ?></td>
                                                <td><?php echo format_currency($fee['tuition_fee']); ?></td>
                                                <td><?php echo format_currency($fee['transport_fee']); ?></td>
                                                <td><?php echo format_currency($fee['late_fee']); ?></td>
                                                <td><?php echo format_currency($fee['total_fee']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <p style="margin-top: 20px; text-align: center;">
                    <a href="index.php" style="color: #1e3c72; text-decoration: none;">← Back to Home</a>
                </p>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Aparna Public School. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Auto-submit form when class or section changes
        document.getElementById('class').addEventListener('change', function () {
            document.getElementById('studentSelectForm').submit();
        });

        document.getElementById('section').addEventListener('change', function () {
            document.getElementById('studentSelectForm').submit();
        });
    </script>
</body>

</html>