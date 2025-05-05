<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_accommodation'])) {
    $name = $_POST['student_id'];
    $college = $_POST['college'];
    $room_type = $_POST['room_type'];

    // Process the application logic here, e.g., update database with application details

    // Redirect to view_application_status.php or any other relevant page
    header('Location: view_application_status.php');
    exit();
}
?>