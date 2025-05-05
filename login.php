<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute SQL statement to prevent SQL injection
    $stmt = $conn->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the user exists and the passwords match
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_level'] = $user['user_level'];

        // Redirect based on user level
        switch ($user['user_level']) {
            case 'admin':
                header('Location: admin_dashboard.php');
                break;
            case 'student':
                header('Location: student_view_application_status.php');
                break;
            case 'accommodation_manager':
                header('Location: manager_dashboard.php');
                break;
            default:
                echo '<p>User level not recognized.</p>';
        }
    } else {
        // Display error message using JavaScript
        echo '<script>';
        echo 'alert("Incorrect username or password.");';
        echo 'window.location.href = "login.html";'; // Redirect back to login page
        echo '</script>';
    }
}
?>
