<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'accommodation_manager') {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accomodation Manager Dashboard</title>
    <link rel="stylesheet" href="menuStyle.css">
</head>
<body>
    <div class="navigation">
        <div class="menuToggle"></div>
        <ul>
            <li class="list manager_profile">
            <a href="manager_profile.php">
                    <span class="icon"><img src="images/Manager.png" alt="manager"></span>
                    <span class="text">Manager Profile</span>
                </a>
            </li>
            <li class="list">
                <a href="manager_dashboard.php">
                    <span class="icon"><img src="images/applications.png" alt="users"></span> 
                    <span class="text">Manage Application</span>
                </a>
            </li>
            <li class="list">
                <a href="view_college.php">
                    <span class="icon icon-colleges"><img src="images/colleges.png" alt="colleges"></span> 
                    <span class="text">View Colleges</span>
                </a>
            </li>
            <li class="list">
                <a href="manager_report.php">
                    <span class="icon"><img src="images/report.png" alt="report"></span> 
                    <span class="text">Accommodation Report</span>
                </a>
            </li>
            <li class="list">
                <a href="logout.php">
                    <span class="icon"><img src="images/logout.png" alt="logout"></span>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</body>
</html>
