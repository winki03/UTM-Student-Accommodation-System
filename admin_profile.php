<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Include database connection
include 'db_connect.php';

// Function to fetch user details by user_id
function getUserDetails($conn, $user_id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Function to update user details
function updateUser($conn, $user_id, $username, $real_name, $phone, $email, $user_level, $password = null) {
    $sql = "UPDATE users SET username=?, real_name=?, phone=?, email=?, user_level=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $username, $real_name, $phone, $email, $user_level, $password, $user_id);
    return $stmt->execute();
}

// Fetch the logged-in user's details
$user_id = $_SESSION['user_id'];
$user = getUserDetails($conn, $user_id);

// Check if form is submitted for updating user details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $real_name = $_POST['real_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $user_level = $_POST['user_level'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    if (isset($username, $real_name, $phone, $email, $user_level)) {
        updateUser($conn, $user_id, $username, $real_name, $phone, $email, $user_level, $password);
        // Refresh user details after update
        $user = getUserDetails($conn, $user_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-content{
            width:70%;
        }

        .profile-form form {
            display: grid;
            grid-template-columns: repeat(2, 1fr); 
            gap: 70px; 
        }
        .profile-form .input-group {
            display: flex;
            flex-direction: column;
        }
        .profile-form .input-group label,
        .profile-form .input-group select {
            margin: 0 0 5px 0;
            color: #004076;
        }
        .profile-form .input-group input{
            margin:0 0 30px 0;
        }
        .profile-form input[type=text], input[type=password], input[type=email], select, input[type=number]{
            padding:5px;
            border: 1px solid #004076;
            border-radius: 20px;
        }
        .profile-form button{
            margin-top:50px;
            grid-column: span 2;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>User Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>
        </div>

        <!-- User Profile Form -->
        <div class="profile-form">
            <form action="" method="POST">
                <div class="input-group">
                    <label for="id">ID:</label>
                    <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($user['id']); ?>" readonly>
                    
                    <label for="real_name">Real Name:</label>
                    <input type="text" id="real_name" name="real_name" value="<?php echo htmlspecialchars($user['real_name']); ?>" readonly>

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>"required>
                </div>

                <div class="input-group">
                    <label for="phone">Phone No:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                    <label for="user_level">User Level:</label>
                    <select id="user_level" name="user_level">
                        <option value="student" <?php if ($user['user_level'] == 'student') echo 'selected'; ?>>Student</option>
                        <option value="admin" <?php if ($user['user_level'] == 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="accommodation_manager" <?php if ($user['user_level'] == 'accommodation_manager') echo 'selected'; ?>>Accommodation Manager</option>
                    </select>
                </div>
                <button type="submit" name="update_user">Update Profile</button>
            </form>
        </div>
    </div>
    <script>
        // Sidebar toggle functionality
        let navigation = document.querySelector('.navigation');
            let menuToggle = document.querySelector('.menuToggle');
            let listItems = document.querySelectorAll('.list');
            let mainContent = document.querySelector('.main-content');

            menuToggle.addEventListener('click', function() {
                navigation.classList.toggle('active');
                if (navigation.classList.contains('active')) {
                    mainContent.style.marginLeft = '310px'; // Adjust margin-left when sidebar is active
                } else {
                    mainContent.style.marginLeft = '140px'; // Default margin-left when sidebar is inactive
                }
            });
            
            listItems.forEach(item => {
                item.addEventListener('click', function() {
                    listItems.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Set active menu item based on current URL
            let currentUrl = window.location.href;
            listItems.forEach(item => {
                let anchor = item.querySelector('a');
                if (anchor.href === currentUrl) {
                    item.classList.add('active');
                }
            });
    </script>
</body>
</html>
