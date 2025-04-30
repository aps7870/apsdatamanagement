<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aparna Public School - Student Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>APARNA PUBLIC SCHOOL</h1>
            <p>Excellence in Education</p>
        </header>

        <main class="dashboard">
            <div class="card-container">
                <a href="teacher_login.php" class="card">
                    <div class="card-icon">👩‍🏫</div>
                    <h2>Teacher's Login</h2>
                    <p>Access student records and manage examination results</p>
                </a>

                <a href="admin_login.php" class="card">
                    <div class="card-icon">👨‍💼</div>
                    <h2>Administration Login</h2>
                    <p>Manage student data and school administration</p>
                </a>

                <a href="fees.php" class="card">
                    <div class="card-icon">💰</div>
                    <h2>Fees Submission</h2>
                    <p>Submit and manage student fees</p>
                </a>

                <a href="view_data.php" class="card">
                    <div class="card-icon">📊</div>
                    <h2>View Student Data</h2>
                    <p>Access student information and records</p>
                </a>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Aparna Public School. All rights reserved.</p>
        </footer>
    </div>
</body>
</html> 