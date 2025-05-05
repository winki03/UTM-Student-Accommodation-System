<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] != 'admin') {
    header('Location: login.html');
    exit();
}

// Include database connection
include 'db_connect.php';

// Function to fetch all users from the database
function getAllUsers($conn, $filter = null) {
    $sql = "SELECT * FROM users";
    if ($filter) {
        $sql .= " WHERE user_level = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $filter);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Function to add a new user
function addUser($conn, $id, $username, $password, $real_name, $phone, $email, $user_level) {
    $sql = "INSERT INTO users (id, username, password, real_name, phone, email, user_level) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $id, $username, $password, $real_name, $phone, $email, $user_level);
    return $stmt->execute();
}

// Function to delete a user
function deleteUser($conn, $user_id) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_id);
    return $stmt->execute();
}

// Function to update a user
function updateUser($conn, $user_id, $username, $real_name, $phone, $email, $user_level) {
    $sql = "UPDATE users SET username=?, real_name=?, phone=?, email=?, user_level=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $username, $real_name, $phone, $email, $user_level, $user_id);
    return $stmt->execute();
}

// Check if form is submitted for adding a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $real_name = $_POST['real_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $user_level = $_POST['user_level'];

    if (!empty($username)) {
        addUser($conn, $id, $username, $password, $real_name, $phone, $email, $user_level);
    }
}

// Check if form is submitted for deleting a user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    deleteUser($conn, $user_id);
}

// Check if form is submitted for updating user details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['id'];
    $username = $_POST['username'];
    $real_name = $_POST['real_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $user_level = $_POST['user_level'];
    
    if (isset($user_id, $username, $real_name, $phone, $email, $user_level)) {
        updateUser($conn, $user_id, $username, $real_name, $phone, $email, $user_level);
    }
}

$filter = isset($_POST['filter']) ? $_POST['filter'] : null;
$users = getAllUsers($conn, $filter);

$totalUsers = count($users);
$totalStudents = count(array_filter($users, function($user) { return $user['user_level'] == 'student'; }));
$totalAdmins = count(array_filter($users, function($user) { return $user['user_level'] == 'admin'; }));
$totalManagers = count(array_filter($users, function($user) { return $user['user_level'] == 'accommodation_manager'; }));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .title{
            margin-top: 20px;
        }
    </style>
</head>
<>
    <?php include 'admin_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>Admin Dashboard</h1>
            <p>Welcome, Admin!</p>
        </div>
        
        <h3>User Statistics</h3>
        <div id="userStatistics" class="statistic"> 
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/totaluser.png" alt="totaluser">
                    <div class="stat-box-text">
                       <p>Total Users </p>
                       <p><b><?php echo $totalUsers; ?></b></p> 
                    </div> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/totalstudent.png" alt="totalstudent">
                    <div class="stat-box-text">
                       <p>Total Students </p>
                       <p><b><?php echo $totalStudents; ?></b></p> 
                    </div> 
                </div> 
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/totaladmin.png" alt="totaladmin">
                    <div class="stat-box-text">
                       <p>Total Admins </p>
                       <p><b><?php echo $totalAdmins; ?></b></p> 
                    </div> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/totalmanager.png" alt="totalmanager">
                    <div class="stat-box-text">
                       <p>Total Managers </p>
                       <p><b><?php echo $totalManagers; ?></b></p> 
                    </div> 
                </div>  
            </div>
        </div>


        <!-- Search and Filter Section -->
        <div class="search-filter">
            <div class = "search">
                <input type="text" id="search" placeholder="Search user...">
            </div>
            <div class="filter-menu" id="filterMenu">
                <button data-filter="" class="active">All User</button>
                <button data-filter="admin">Admin</button>
                <button data-filter="accommodation_manager">Accommodation Manager</button>
                <button data-filter="student">Student</button>
            </div>
            <button id="addUserBtn"> + Add New User</button>
        </div>
        

        <!-- Display all users -->
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Real Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>User Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['real_name']; ?></td>
                    <td><?php echo $user['phone']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['user_level']; ?></td>
                    <td>
                        <button class="updateUserBtn" data-id="<?php echo $user['id']; ?>">Update</button>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add User Modal -->
        <div id="addUserModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New User</h2>
                <form action="" method="POST">
                    <label for="id">User ID:</label>
                    <input type="text" id="id" name="id" required><br>

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required><br>

                    <label for="password">Password: </label>
                    <input type="password" id="password" name="password" required><br>

                    <label for="real_name">Real Name:</label>
                    <input type="text" id="real_name" name="real_name" required><br>

                    <label for="phone">Phone No:</label>
                    <input type="text" id="phone" name="phone"><br>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email"><br>

                    <label for="user_level">User Level:</label>
                    <select id="user_level" name="user_level">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                        <option value="accommodation_manager">Accommodation Manager</option>
                    </select><br>

                    <button type="submit" name="add_user">Add User</button>
                    <button type="button" class="cancelBtn">Cancel</button>
                </form>
            </div>
        </div>

        <!-- Update User Modal -->
        <div id="updateUserModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Update User</h2>
                <form action="" method="POST" id="updateUserForm">
                    <input type="hidden" id="update_id" name="id">
                    
                    <label for="update_username">Username:</label>
                    <input type="text" id="update_username" name="username" required><br>

                    <label for="update_real_name">Real Name:</label>
                    <input type="text" id="update_real_name" name="real_name" required><br>

                    <label for="update_phone">Phone No:</label>
                    <input type="text" id="update_phone" name="phone"><br>

                    <label for="update_email">Email :</label>
                    <input type="email" id="update_email" name="email"><br>

                    <label for="update_user_level">User Level:</label>
                    <select id="update_user_level" name="user_level">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                        <option value="accommodation_manager">Accommodation Manager</option>
                    </select><br>

                    <button type="submit" name="update_user">Update User</button>
                    <button type="button" class="cancelBtn">Cancel</button>
                </form>
            </div>
        </div>

        <?php include 'footer.html'; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addUserModal = document.getElementById('addUserModal');
            const updateUserModal = document.getElementById('updateUserModal');
            const addUserBtn = document.getElementById('addUserBtn');
            const closeModalButtons = document.querySelectorAll('.close, .cancelBtn');
            const updateUserBtns = document.querySelectorAll('.updateUserBtn');
            const filterButtons = document.querySelectorAll('.filter-menu button');
            const searchInput = document.getElementById('search');
            
            // Open add user modal
            addUserBtn.onclick = () => {
                addUserModal.style.display = 'block';
            }
            
            // Close modals
            closeModalButtons.forEach(button => {
                button.onclick = () => {
                    addUserModal.style.display = 'none';
                    updateUserModal.style.display = 'none';
                }
            });
            
            // Open update user modal and populate fields
            updateUserBtns.forEach(button => {
                button.onclick = (e) => {
                    const userId = e.target.getAttribute('data-id');
                    const userRow = e.target.closest('tr');
                    const username = userRow.children[1].textContent;
                    const realName = userRow.children[2].textContent;
                    const phone = userRow.children[3].textContent;
                    const email = userRow.children[4].textContent;
                    const userLevel = userRow.children[5].textContent;
                    
                    document.getElementById('update_id').value = userId;
                    document.getElementById('update_username').value = username;
                    document.getElementById('update_real_name').value = realName;
                    document.getElementById('update_phone').value = phone;
                    document.getElementById('update_email').value = email;
                    document.getElementById('update_user_level').value = userLevel;
                    
                    updateUserModal.style.display = 'block';
                }
            });
            
            // Close modals when clicking outside
            window.onclick = (event) => {
                if (event.target == addUserModal) {
                    addUserModal.style.display = 'none';
                } else if (event.target == updateUserModal) {
                    updateUserModal.style.display = 'none';
                }
            }
        
            // Filter users based on the clicked button
            filterButtons.forEach(button => {
                button.onclick = () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    const filterValue = button.getAttribute('data-filter');
                    filterUsers(filterValue);
                }
            });
            
            // Filter users function
            function filterUsers(filter) {
                const rows = document.querySelectorAll('#userTable tr');
                rows.forEach(row => {
                    const userLevel = row.children[5].textContent.toLowerCase().trim();
                    if (filter === "" || userLevel === filter) {
                        row.style.display = "";
                    } 
                    else {
                        row.style.display = "none";
                    }
                });
            }

            // Search users based on input
            searchInput.oninput = () => {
                const searchValue = searchInput.value.toLowerCase();
                const rows = document.querySelectorAll('#userTable tr');
                rows.forEach(row => {
                    const cells = Array.from(row.children).map(cell => cell.textContent.toLowerCase());
                    const rowContainsSearchValue = cells.some(cellValue => cellValue.includes(searchValue));
                    if (rowContainsSearchValue) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            };

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
        });
    </script>
</body>
</html>
