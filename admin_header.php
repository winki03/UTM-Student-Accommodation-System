<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'admin') {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="menuStyle.css">
</head>
<body>
<div class="navigation">
    <div class="menuToggle"></div>
    <ul>
        <li class="list admin-profile">
            <a href="admin_profile.php">
                <span class="icon icon-admin-profile"><img src="images/admin.png" alt="admin"></span> 
                <span class="text">Admin</span>
            </a>
        </li>
        <li class="list">
            <a href="admin_dashboard.php">
                <span class="icon icon-users"><img src="images/users.png" alt="users"></span> 
                <span class="text">Manage Users</span>
            </a>
        </li>
        <li class="list">
            <a href="view_applications.php">
                <span class="icon icon-applications"><img src="images/applications.png" alt="applications"></span> 
                <span class="text">View Applications</span>
            </a>
        </li>
        <li class="list">
            <a href="manager_report.php">
                <span class="icon"><img src="images/report.png" alt="report"></span> 
                <span class="text">Accommodation Report</span>
            </a>
        </li>
        <li class="list">
            <a href="manage_colleges.php">
                <span class="icon icon-colleges"><img src="images/colleges.png" alt="colleges"></span> 
                <span class="text">Manage Colleges</span>
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

