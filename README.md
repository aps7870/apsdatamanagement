# Aparna Public School - Student Record Management System

A comprehensive school management system built with PHP and MySQL for managing student records, examination results, fee submissions, and more.

## Features

1. **Multiple User Roles**
   - Teacher Login
   - Administrator Login
   - Public Access for Fee Submission and Student Data View

2. **Teacher Module**
   - Student Selection by Class and Section
   - Examination Entry (1st, 2nd, and 3rd Terms)
   - Subject-wise Marks Entry
   - Attendance Management

3. **Administrator Module**
   - Student Registration
   - Bio-data Management
   - Bus Facility Management
   - Complete Access to Teacher Module

4. **Fee Management**
   - Monthly Fee Collection
   - Automatic Fee Calculation
   - Late Fee Processing
   - Transport Fee Management
   - Fee History Tracking

5. **Student Data Viewer**
   - Comprehensive Student Information
   - Academic Performance Records
   - Attendance Records
   - Fee Payment History

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/Nginx)
- Modern Web Browser

## Installation

1. **Database Setup**
   ```sql
   # Import the database schema
   mysql -u your_username -p < database/schema.sql
   ```

2. **Configuration**
   - Copy `config/database.php` to your web server
   - Update database credentials in `config/database.php`:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'your_username');
     define('DB_PASSWORD', 'your_password');
     define('DB_NAME', 'aparna_school');
     ```

3. **Web Server Configuration**
   - Place all files in your web server's document root
   - Ensure proper permissions are set
   - Configure URL rewriting if needed

## Default Users

### Administrators
1. Username: arunab
   Password: password123

2. Username: rohit
   Password: password123

### Teachers
1. Username: palak
   Password: password123

2. Username: sunham
   Password: password123

(and so on for all teachers)

## Directory Structure

```
/
├── config/
│   └── database.php
├── css/
│   └── style.css
├── includes/
│   └── functions.php
├── index.php
├── teacher_login.php
├── admin_login.php
├── teacher_dashboard.php
├── admin_dashboard.php
├── fees.php
├── view_data.php
└── logout.php
```

## Security Features

- Password Hashing
- SQL Injection Prevention
- XSS Protection
- Session Management
- Input Validation

## Usage Guidelines

1. **Teacher Access**
   - Login with provided credentials
   - Select class and section
   - Enter marks and attendance
   - Submit and view results

2. **Administrator Access**
   - Manage student registration
   - Monitor fee collection
   - Access all teacher functions
   - View comprehensive reports

3. **Fee Submission**
   - Select student by class/section
   - Choose payment month
   - System calculates applicable fees
   - Generate payment receipt

4. **Data Viewing**
   - Public access to view student data
   - Search by class/section/name
   - View academic and fee records

## Support

For technical support or queries, please contact:
- Email: support@aparnapublicschool.com
- Phone: +91-XXXXXXXXXX

## License

This software is proprietary and confidential. Unauthorized copying, modification, distribution, or use is strictly prohibited.

© 2024 Aparna Public School. All rights reserved. 