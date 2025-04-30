<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

init_session();

// Check if user is logged in and is a teacher
if (!is_logged_in() || !check_role('teacher')) {
    redirect('teacher_login.php', 'Please login first', 'error');
}

$classes = get_all_classes();
$sections = get_all_sections();
$selected_class = isset($_POST['class']) ? sanitize_input($_POST['class']) : '';
$selected_section = isset($_POST['section']) ? sanitize_input($_POST['section']) : '';
$selected_student = isset($_POST['student']) ? sanitize_input($_POST['student']) : '';
$selected_term = isset($_POST['term']) ? sanitize_input($_POST['term']) : '';

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

// Process exam entry form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_marks'])) {
    $student_id = sanitize_input($_POST['student']);
    $term = sanitize_input($_POST['term']);
    
    // Validate inputs
    if (empty($student_id) || empty($term)) {
        $error = 'Please select student and term';
    } else {
        // Process FA marks
        $fa1 = sanitize_input($_POST['fa1']);
        $fa2 = sanitize_input($_POST['fa2']);
        $fa_average = ($fa1 + $fa2) / 2;
        
        // Process SA marks
        $sa1 = sanitize_input($_POST['sa1']);
        $notebook = sanitize_input($_POST['notebook']);
        $total = $fa_average + $sa1 + $notebook;
        
        // Insert/Update exam results
        $subjects = [
            'English' => 75,
            'Hindi' => 75,
            'Maths' => 75,
            'SST' => 75,
            'Science' => 75,
            'Computer' => 50,
            'MSc' => 50,
            'GK' => 50
        ];
        
        foreach ($subjects as $subject => $max_marks) {
            $marks = sanitize_input($_POST[strtolower($subject)]);
            $practical = sanitize_input($_POST[strtolower($subject) . '_practical']);
            $notebook = sanitize_input($_POST[strtolower($subject) . '_notebook']);
            
            $query = "INSERT INTO exam_results 
                      (student_id, term, subject, fa1_marks, fa2_marks, fa_average, 
                       sa1_marks, practical_marks, notebook_marks, total_marks, created_by) 
                      VALUES 
                      ('$student_id', '$term', '$subject', '$fa1', '$fa2', '$fa_average',
                       '$marks', '$practical', '$notebook', 
                       " . ($marks + $practical + $notebook) . ", '{$_SESSION['user_id']}')
                      ON DUPLICATE KEY UPDATE
                      fa1_marks = '$fa1',
                      fa2_marks = '$fa2',
                      fa_average = '$fa_average',
                      sa1_marks = '$marks',
                      practical_marks = '$practical',
                      notebook_marks = '$notebook',
                      total_marks = " . ($marks + $practical + $notebook);
            
            mysqli_query($conn, $query);
        }
        
        // Handle Drawing grade
        $drawing_grade = sanitize_input($_POST['drawing_grade']);
        $query = "INSERT INTO exam_results 
                  (student_id, term, subject, grade, created_by)
                  VALUES 
                  ('$student_id', '$term', 'Drawing', '$drawing_grade', '{$_SESSION['user_id']}')
                  ON DUPLICATE KEY UPDATE
                  grade = '$drawing_grade'";
        mysqli_query($conn, $query);
        
        // Update attendance
        $total_days = sanitize_input($_POST['total_days']);
        $present_days = sanitize_input($_POST['present_days']);
        $attendance_percentage = ($present_days / $total_days) * 100;
        
        $query = "INSERT INTO attendance 
                  (student_id, term, total_working_days, present_days, attendance_percentage)
                  VALUES 
                  ('$student_id', '$term', '$total_days', '$present_days', '$attendance_percentage')
                  ON DUPLICATE KEY UPDATE
                  total_working_days = '$total_days',
                  present_days = '$present_days',
                  attendance_percentage = '$attendance_percentage'";
        mysqli_query($conn, $query);
        
        redirect('teacher_dashboard.php', 'Marks updated successfully');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Aparna Public School</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>APARNA PUBLIC SCHOOL</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
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
                                <?php foreach ($students as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $selected_student == $id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="term">Select Term</label>
                            <select name="term" id="term" required>
                                <option value="">Select Term</option>
                                <option value="1st" <?php echo $selected_term === '1st' ? 'selected' : ''; ?>>1st Term</option>
                                <option value="2nd" <?php echo $selected_term === '2nd' ? 'selected' : ''; ?>>2nd Term</option>
                                <option value="3rd" <?php echo $selected_term === '3rd' ? 'selected' : ''; ?>>3rd Term</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="select_student">Select Student</button>
                </form>

                <?php if ($selected_student && $selected_term): ?>
                    <form method="POST" action="" id="marksForm" style="margin-top: 30px;">
                        <input type="hidden" name="student" value="<?php echo $selected_student; ?>">
                        <input type="hidden" name="term" value="<?php echo $selected_term; ?>">

                        <h3>Formative Assessment</h3>
                        <div class="form-group">
                            <label for="fa1">FA1 Marks (out of 20)</label>
                            <input type="number" id="fa1" name="fa1" min="0" max="20" required>
                        </div>

                        <div class="form-group">
                            <label for="fa2">FA2 Marks (out of 20)</label>
                            <input type="number" id="fa2" name="fa2" min="0" max="20" required>
                        </div>

                        <h3>Subject Marks</h3>
                        <?php foreach (['English', 'Hindi', 'Maths', 'SST', 'Science'] as $subject): ?>
                            <div class="form-group">
                                <label><?php echo $subject; ?></label>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                                    <input type="number" name="<?php echo strtolower($subject); ?>" 
                                           placeholder="Marks (out of 75)" min="0" max="75" required>
                                    <input type="number" name="<?php echo strtolower($subject); ?>_practical" 
                                           placeholder="Practical (out of 20)" min="0" max="20" required>
                                    <input type="number" name="<?php echo strtolower($subject); ?>_notebook" 
                                           placeholder="Notebook (out of 5)" min="0" max="5" required>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach (['Computer', 'MSc', 'GK'] as $subject): ?>
                            <div class="form-group">
                                <label><?php echo $subject; ?></label>
                                <input type="number" name="<?php echo strtolower($subject); ?>" 
                                       placeholder="Marks (out of 50)" min="0" max="50" required>
                            </div>
                        <?php endforeach; ?>

                        <div class="form-group">
                            <label for="drawing_grade">Drawing Grade</label>
                            <select name="drawing_grade" id="drawing_grade" required>
                                <option value="">Select Grade</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>

                        <h3>Attendance</h3>
                        <div class="form-group">
                            <label for="total_days">Total Working Days</label>
                            <input type="number" id="total_days" name="total_days" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="present_days">Present Days</label>
                            <input type="number" id="present_days" name="present_days" min="0" required>
                        </div>

                        <button type="submit" name="submit_marks">Submit Marks</button>
                    </form>
                <?php endif; ?>

                <p style="margin-top: 20px; text-align: center;">
                    <a href="logout.php" style="color: #1e3c72; text-decoration: none;">Logout</a>
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
        
        // Validate attendance
        document.getElementById('present_days').addEventListener('input', function() {
            const totalDays = parseInt(document.getElementById('total_days').value);
            const presentDays = parseInt(this.value);
            
            if (presentDays > totalDays) {
                alert('Present days cannot be more than total working days');
                this.value = totalDays;
            }
        });
    </script>
</body>
</html> 