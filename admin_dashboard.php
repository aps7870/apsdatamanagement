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

// Handle form submission for new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $class = sanitize_input($_POST['class']);
    $section = sanitize_input($_POST['section']);
    $student_name = sanitize_input($_POST['student_name']);
    $admission_no = sanitize_input($_POST['admission_no']);
    $admission_date = sanitize_input($_POST['admission_date']);
    $dob = sanitize_input($_POST['dob']);
    $gender = sanitize_input($_POST['gender']);
    $student_aadhar = sanitize_input($_POST['student_aadhar']);
    $father_name = sanitize_input($_POST['father_name']);
    $father_aadhar = sanitize_input($_POST['father_aadhar']);
    $mother_name = sanitize_input($_POST['mother_name']);
    $mother_aadhar = sanitize_input($_POST['mother_aadhar']);
    $address = sanitize_input($_POST['address']);
    $bus_facility = isset($_POST['bus_facility']) ? 1 : 0;
    $bus_stoppage = sanitize_input($_POST['bus_stoppage']);
    $bus_fare = sanitize_input($_POST['bus_fare']);

    // Validate inputs
    $errors = [];
    if (!validate_aadhar($student_aadhar)) {
        $errors[] = 'Invalid student Aadhar number';
    }
    if (!validate_aadhar($father_aadhar)) {
        $errors[] = 'Invalid father Aadhar number';
    }
    if (!validate_aadhar($mother_aadhar)) {
        $errors[] = 'Invalid mother Aadhar number';
    }

    if (empty($errors)) {
        $query = "INSERT INTO students (
                    admission_no, admission_date, student_name, class, section,
                    date_of_birth, gender, student_aadhar, father_name,
                    father_aadhar, mother_name, mother_aadhar, address,
                    bus_facility, bus_stoppage, bus_fare
                ) VALUES (
                    '$admission_no', '$admission_date', '$student_name', '$class', '$section',
                    '$dob', '$gender', '$student_aadhar', '$father_name',
                    '$father_aadhar', '$mother_name', '$mother_aadhar', '$address',
                    $bus_facility, " . ($bus_facility ? "'$bus_stoppage'" : "NULL") . ", 
                    " . ($bus_facility ? "$bus_fare" : "NULL") . "
                )";

        if (mysqli_query($conn, $query)) {
            redirect('admin_dashboard.php', 'Student added successfully');
        } else {
            $error = 'Error adding student: ' . mysqli_error($conn);
        }
    }
}

// Get existing students
$query = "SELECT * FROM students ORDER BY class, section, student_name";
$students = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Aparna Public School</title>
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
                <h2>Add New Student</h2>
                
                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="POST" action="" id="addStudentForm">
                    <div class="form-group">
                        <label for="admission_no">Admission Number</label>
                        <input type="text" id="admission_no" name="admission_no" required>
                    </div>

                    <div class="form-group">
                        <label for="admission_date">Admission Date</label>
                        <input type="date" id="admission_date" name="admission_date" required>
                    </div>

                    <div class="form-group">
                        <label for="student_name">Student Name</label>
                        <input type="text" id="student_name" name="student_name" required>
                    </div>

                    <div class="form-group">
                        <label for="class">Class</label>
                        <select name="class" id="class" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class; ?>"><?php echo $class; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="section">Section</label>
                        <select name="section" id="section" required>
                            <option value="">Select Section</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section; ?>"><?php echo $section; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="student_aadhar">Student Aadhar Number</label>
                        <input type="text" id="student_aadhar" name="student_aadhar" pattern="[0-9]{12}" required>
                    </div>

                    <div class="form-group">
                        <label for="father_name">Father's Name</label>
                        <input type="text" id="father_name" name="father_name" required>
                    </div>

                    <div class="form-group">
                        <label for="father_aadhar">Father's Aadhar Number</label>
                        <input type="text" id="father_aadhar" name="father_aadhar" pattern="[0-9]{12}" required>
                    </div>

                    <div class="form-group">
                        <label for="mother_name">Mother's Name</label>
                        <input type="text" id="mother_name" name="mother_name" required>
                    </div>

                    <div class="form-group">
                        <label for="mother_aadhar">Mother's Aadhar Number</label>
                        <input type="text" id="mother_aadhar" name="mother_aadhar" pattern="[0-9]{12}" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Full Address</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="bus_facility" id="bus_facility">
                            Bus Facility Required
                        </label>
                    </div>

                    <div id="bus_details" style="display: none;">
                        <div class="form-group">
                            <label for="bus_stoppage">Bus Stoppage</label>
                            <input type="text" id="bus_stoppage" name="bus_stoppage">
                        </div>

                        <div class="form-group">
                            <label for="bus_fare">Bus Fare (₹)</label>
                            <input type="number" id="bus_fare" name="bus_fare" min="0" step="0.01">
                        </div>
                    </div>

                    <button type="submit" name="add_student">Add Student</button>
                </form>

                <h2 style="margin-top: 40px;">Existing Students</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Admission No</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Father's Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = mysqli_fetch_assoc($students)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class']); ?></td>
                                    <td><?php echo htmlspecialchars($student['section']); ?></td>
                                    <td><?php echo htmlspecialchars($student['father_name']); ?></td>
                                    <td>
                                        <a href="edit_student.php?id=<?php echo $student['id']; ?>" 
                                           style="color: #1e3c72; text-decoration: none; margin-right: 10px;">Edit</a>
                                        <a href="view_student.php?id=<?php echo $student['id']; ?>"
                                           style="color: #1e3c72; text-decoration: none;">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

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
        // Toggle bus facility details
        document.getElementById('bus_facility').addEventListener('change', function() {
            const busDetails = document.getElementById('bus_details');
            busDetails.style.display = this.checked ? 'block' : 'none';
            
            const busInputs = busDetails.getElementsByTagName('input');
            for (let input of busInputs) {
                input.required = this.checked;
            }
        });

        // Calculate age from DOB
        document.getElementById('dob').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            if (age < 3 || age > 20) {
                alert('Please check the date of birth. Age should be between 3 and 20 years.');
                this.value = '';
            }
        });

        // Validate Aadhar numbers
        const aadharInputs = document.querySelectorAll('input[pattern]');
        aadharInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substr(0, 12);
            });
        });
    </script>
</body>
</html> 