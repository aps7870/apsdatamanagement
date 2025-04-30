<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

init_session();

// Check if user is logged in and is an admin
if (!is_logged_in() || !check_role('admin')) {
    redirect('admin_login.php', 'Please login first', 'error');
}

$classes = get_all_classes();
$sections = get_all_sections();
$months = get_all_months();

$selected_class = isset($_POST['class']) ? sanitize_input($_POST['class']) : '';
$selected_section = isset($_POST['section']) ? sanitize_input($_POST['section']) : '';
$selected_student = isset($_POST['student']) ? sanitize_input($_POST['student']) : '';

// Get students based on class and section
$students = [];
if ($selected_class && $selected_section) {
    $query = "SELECT id, student_name, bus_facility, bus_fare FROM students 
              WHERE class = '$selected_class' 
              AND section = '$selected_section' 
              ORDER BY student_name";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $students[$row['id']] = [
            'name' => $row['student_name'],
            'bus_facility' => $row['bus_facility'],
            'bus_fare' => $row['bus_fare']
        ];
    }
}

// Process fee submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_fee'])) {
    $student_id = sanitize_input($_POST['student']);
    $month = sanitize_input($_POST['month']);
    $fee_date = sanitize_input($_POST['fee_date']);
    $tuition_fee = get_class_fee($selected_class);
    $transport_fee = isset($students[$student_id]['bus_facility']) && $students[$student_id]['bus_facility'] ? $students[$student_id]['bus_fare'] : 0;
    $late_fee = calculate_late_fee($fee_date);
    $total_fee = $tuition_fee + $transport_fee + $late_fee;

    $query = "INSERT INTO fees (
                student_id, month, fee_date, tuition_fee,
                transport_fee, late_fee, total_fee, created_by
              ) VALUES (
                '$student_id', '$month', '$fee_date', $tuition_fee,
                $transport_fee, $late_fee, $total_fee, '{$_SESSION['user_id']}'
              )";

    if (mysqli_query($conn, $query)) {
        redirect('fees.php', 'Fee submitted successfully');
    } else {
        $error = 'Error submitting fee: ' . mysqli_error($conn);
    }
}

// Get fee history
$fee_history = [];
if ($selected_student) {
    $query = "SELECT f.*, s.student_name, s.class, s.section 
              FROM fees f 
              JOIN students s ON f.student_id = s.id 
              WHERE f.student_id = '$selected_student' 
              ORDER BY f.fee_date DESC";
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
    <title>Fee Submission - Aparna Public School</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>APARNA PUBLIC SCHOOL</h1>
            <p>Fee Submission</p>
        </header>

        <main>
            <?php echo display_message(); ?>

            <div class="form-container" style="max-width: 800px;">
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
                                <?php foreach ($students as $id => $student): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $selected_student == $id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="select_student">Select Student</button>
                </form>

                <?php if ($selected_student): ?>
                    <form method="POST" action="" id="feeForm" style="margin-top: 30px;">
                        <input type="hidden" name="student" value="<?php echo $selected_student; ?>">
                        <input type="hidden" name="class" value="<?php echo $selected_class; ?>">

                        <div class="form-group">
                            <label for="month">Select Month</label>
                            <select name="month" id="month" required>
                                <option value="">Select Month</option>
                                <?php foreach ($months as $month): ?>
                                    <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fee_date">Fee Date</label>
                            <input type="date" id="fee_date" name="fee_date" required>
                        </div>

                        <div class="form-group">
                            <label>Tuition Fee</label>
                            <input type="text" value="<?php echo format_currency(get_class_fee($selected_class)); ?>" readonly>
                        </div>

                        <?php if ($students[$selected_student]['bus_facility']): ?>
                            <div class="form-group">
                                <label>Transport Fee</label>
                                <input type="text" value="<?php echo format_currency($students[$selected_student]['bus_fare']); ?>" readonly>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Late Fee</label>
                            <input type="text" id="late_fee" value="₹0.00" readonly>
                        </div>

                        <div class="form-group">
                            <label>Total Fee</label>
                            <input type="text" id="total_fee" readonly>
                        </div>

                        <button type="submit" name="submit_fee">Submit Fee</button>
                    </form>

                    <?php if (!empty($fee_history)): ?>
                        <h2 style="margin-top: 40px;">Fee History</h2>
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
        document.getElementById('class').addEventListener('change', function() {
            document.getElementById('studentSelectForm').submit();
        });
        
        document.getElementById('section').addEventListener('change', function() {
            document.getElementById('studentSelectForm').submit();
        });

        // Calculate late fee and total
        document.getElementById('fee_date').addEventListener('change', function() {
            const feeDate = new Date(this.value);
            const day = feeDate.getDate();
            const lateFee = day > 15 ? 50 : 0;
            document.getElementById('late_fee').value = `₹${lateFee.toFixed(2)}`;
            
            const tuitionFee = parseFloat(<?php echo get_class_fee($selected_class); ?>);
            const transportFee = <?php echo isset($students[$selected_student]['bus_fare']) ? $students[$selected_student]['bus_fare'] : 0; ?>;
            const totalFee = tuitionFee + transportFee + lateFee;
            
            document.getElementById('total_fee').value = `₹${totalFee.toFixed(2)}`;
        });
    </script>
</body>
</html> 