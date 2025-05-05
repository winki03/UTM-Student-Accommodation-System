<?php
session_start();
include 'db_connect.php';

// Function to fetch all applications from the database
function getAllApplications($conn) {
    $applications = [];
    $sql = "SELECT * FROM applications";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $applications = $result->fetch_all(MYSQLI_ASSOC);
    }
    return $applications;
}

// Function to update room availability
function updateRoomAvailability($conn, $collegeName, $roomType, $increment = true) {
    $roomColumn = $roomType === 'single' ? 'available_single_rooms' : 'available_double_rooms';
    $sql = "UPDATE colleges SET $roomColumn = $roomColumn " . ($increment ? '+ 1' : '- 1') . " WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $collegeName);
    $stmt->execute();
    $stmt->close();
}

// Function to update status of a specific application
function updateStatus($conn, $id, $newStatus, $reason = null) {
    $currentApplication = getCurrentApplication($conn, $id);
    $currentStatus = $currentApplication['status'];
    $collegeName = $currentApplication['college_name'];
    $roomType = $currentApplication['room_type'];
    $studentId = $currentApplication['student_id'];

    // Fetch student name from users table
    $studentName = getStudentName($conn, $studentId);

    // Update room availability based on the status change
    if ($currentStatus === 'approved' && $newStatus !== 'approved') {
        updateRoomAvailability($conn, $collegeName, $roomType, true); // Increment room availability
    } elseif ($currentStatus !== 'approved' && $newStatus === 'approved') {
        updateRoomAvailability($conn, $collegeName, $roomType, false); // Decrement room availability
    }

    if ($newStatus === 'rejected' && !empty($reason)) {
        $sql = "UPDATE applications SET status = ?, reason_for_rejection = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $newStatus, $reason, $id);
    } elseif ($newStatus !== 'rejected') {
        $sql = "UPDATE applications SET status = ?, reason_for_rejection = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $newStatus, $id);
    } else {
        // In case of rejected status and empty reason, handle as per your application logic
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $newStatus, $id);
    }
    $stmt->execute();
    $stmt->close();

    // If the application is approved, create payment and accommodation records
    if ($newStatus === 'approved') {
        $amount = ($roomType === 'single') ? 550.00 : 450.00;
        createPaymentRecord($conn, $id, $studentId, $studentName, $amount);
        createAccommodationRecord($conn, $studentId, $studentName, $collegeName, $roomType);
    }
}

// Function to fetch student name by student_id
function getStudentName($conn, $studentId) {
    $sql = "SELECT real_name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user['real_name'];
}

// Function to create a payment record
function createPaymentRecord($conn,$id, $studentId, $studentName, $amount) {
    $sql = "INSERT INTO payments (application_id, student_id, student_name, amount) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssd', $id, $studentId, $studentName, $amount);
    $stmt->execute();
    $stmt->close();
}

// Function to create an accommodation record
function createAccommodationRecord($conn, $studentId, $studentName, $collegeName, $roomType) {
    $sql = "INSERT INTO accommodation_records (student_id, student_name, college_name, room_type) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $studentId, $studentName, $collegeName, $roomType);
    $stmt->execute();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if (isset($id, $status)) {
        if ($status === 'rejected') {
            $reason = $_POST['reason'] ?? ''; // Ensure you sanitize and validate this input
            updateStatus($conn, $id, $status, $reason);
        } else {
            // Check if status changed from rejected to another status, then clear reason_for_rejection
            $currentApplication = getCurrentApplication($conn, $id);
            if ($currentApplication['status'] === 'rejected') {
                updateStatus($conn, $id, $status); // Clear reason_for_rejection
            } else {
                updateStatus($conn, $id, $status); // No need to clear reason_for_rejection
            }
        }
        
        // Redirect after update to prevent resubmission on page refresh
        header("Location: manager_dashboard.php");
        exit();
    }
}

// Function to fetch a specific application by ID
function getCurrentApplication($conn, $id) {
    $sql = "SELECT * FROM applications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();
    $stmt->close();
    return $application;
}

// Fetch all applications
$applications = getAllApplications($conn);

// Count applications based on status
$totalApplications = count($applications);
$totalPendingApplications = count(array_filter($applications, function($application) { return $application['status'] == 'pending'; }));
$totalApprovedApplications = count(array_filter($applications, function($application) { return $application['status'] == 'approved'; }));
$totalRejectedApplications = count(array_filter($applications, function($application) { return $application['status'] == 'rejected'; }));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .statistic {
            gap: 50px; /* Adds space between each box */
        }
        .status-button {
            padding: 5px 10px;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
        }
        .pending,
        .pending:hover {
            background-color: #fffc4d;
            color: black;
        }
        .approved,
        .approved:hover {
            background-color: #a3ff4d;
        }
        .rejected,
        .rejected:hover {
            background-color: #ff4d4d;
        }
        .reason-input {
            display: none; /* Initially hide reason input */
        }
    </style>
</head>
<body>
    <?php include 'manager_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>Manage Applications</h1>
        </div>
        
        <h3>Applications Statistics</h3>

        <div id="applicationStatistics" class="statistic">
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/totalapplication.png" alt="totalapplications">
                    <div class="stat-box-text">
                       <p>Total Applications </p>
                       <p><b><?php echo $totalApplications; ?></b></p> 
                    </div> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/pending.png" alt="totalpending">
                    <div class="stat-box-text">
                       <p>Pending Applications </p>
                       <p><b><?php echo $totalPendingApplications; ?></b></p> 
                    </div> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/approved.png" alt="totalapproved">
                    <div class="stat-box-text">
                       <p>Approved Applications </p>
                       <p><b><?php echo $totalApprovedApplications; ?></b></p> 
                    </div> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/rejected.png" alt="totalpending">
                    <div class="stat-box-text">
                       <p>Rejected Applications </p>
                       <p><b><?php echo $totalRejectedApplications; ?></b></p> 
                    </div> 
                </div>  
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter">
            <div class="search">
                <input type="text" id="search" placeholder="Search application...">
            </div>
            <div class="filter-menu" id="filterMenu">
                <button data-filter="" class="active">All</button>
                <button data-filter="pending">Pending</button>
                <button data-filter="approved">Approved</button>
                <button data-filter="rejected">Rejected</button>
            </div>
        </div>

        <table border="1">
            <thead>
                <tr>
                    <th>Application ID</th>
                    <th>Student ID</th>
                    <th>College Name</th>
                    <th>Room Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                    <th>Rejection reason</th>
                </tr>
            </thead>
            <tbody id="applicationTable">
                <?php foreach ($applications as $application): ?>
                <tr data-status="<?php echo strtolower($application['status']); ?>">
                    <td><?php echo $application['id']; ?></td>
                    <td><?php echo $application['student_id']; ?></td>
                    <td><?php echo $application['college_name']; ?></td>
                    <td><?php echo $application['room_type']; ?></td>
                    <td>
                        <button class="status-button <?php echo strtolower($application['status']); ?>">
                            <?php echo ucfirst($application['status']); ?>
                        </button>
                    </td>
                    <td>
                        <form action="manager_dashboard.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $application['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $application['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $application['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                            <?php if ($application['status'] == 'rejected'): ?>
                                <input type="text" name="reason" placeholder="Reason for rejection" required>
                            <?php endif; ?>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                    <td><?php echo $application['reason_for_rejection']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php include 'footer.html'; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search');
            const applicationTable = document.getElementById('applicationTable');
            const filterMenu = document.getElementById('filterMenu');
            const filterButtons = filterMenu.querySelectorAll('button');

            searchInput.oninput = () => {
                const searchValue = searchInput.value.toLowerCase();
                searchApplications(searchValue);
            };

            filterButtons.forEach(button => {
                button.onclick = () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    filterApplications(button.getAttribute('data-filter'));
                };
            });

            function filterApplications(filter) {
                const rows = applicationTable.querySelectorAll('tr');
                rows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    if (filter === "" || status === filter) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

            function searchApplications(search) {
                const rows = applicationTable.querySelectorAll('tr');
                rows.forEach(row => {
                    const id = row.children[0].textContent.toLowerCase();
                    const studentId = row.children[1].textContent.toLowerCase();
                    const collegeName = row.children[2].textContent.toLowerCase();
                    const roomType = row.children[3].textContent.toLowerCase();
                    const status = row.getAttribute('data-status');
                    if (id.includes(search) || studentId.includes(search) || collegeName.includes(search) || roomType.includes(search) || status.includes(search)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

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
