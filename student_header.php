<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'student') {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="menuStyle.css">
</head>
<body>
    <div class="navigation">
        <div class="menuToggle"></div> 
        <ul>
            <li class="list student-profile">
                <a href="student_profile.php">
                    <span class="icon icon-admin-profile"><img src="images/admin.png" alt="admin"></span> 
                    <span class="text">Student</span>
                </a>
            </li>
            <li class="list">
                <a href="student_view_application_status.php">
                    <span class="icon icon-applications"><img src="images/applications.png" alt="applications"></span> 
                    <span class="text">View Application Status</span>
                </a>
            </li>
            <li class="list">
                <a href="student_view_apply_accommodation.php">
                    <span class="icon icon-users"><img src="images/users.png" alt="users"></span> 
                    <span class="text">View Available Accommodation</span>
                </a>
            </li>
            <li class="list">
                <a href="logout.php">
                    <span class="icon icon-logout"><img src="images/logout.png" alt="logout"></span> 
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</body>
</html>
