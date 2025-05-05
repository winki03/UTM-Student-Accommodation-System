<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'student') {
    header('Location: login.html');
    exit();
}

// Include database connection
include 'db_connect.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch application status
$sql = "SELECT * FROM applications WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$app_result = $stmt->get_result();
$application = $app_result->fetch_assoc();
?>