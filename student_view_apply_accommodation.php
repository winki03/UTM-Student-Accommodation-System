<?php
include 'db_connect.php';

// Function to check if the student has already applied
function hasStudentApplied($conn, $student_id) {
    $sql = "SELECT COUNT(*) as count FROM applications WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

// Function to get the status of the latest application
function getLatestApplicationStatus($conn, $student_id) {
    $sql = "SELECT status FROM applications WHERE student_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'];
    }
    return null;
}

// Fetch available accommodations from the database
function getAllColleges($conn) {
    $sql = "SELECT * FROM colleges";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$colleges = getAllColleges($conn);

// Check if form is submitted for applying for accommodation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_accommodation'])) {
    $student_id = $_POST['student_id'];
    $college_name = $_POST['college'];
    $room_type = $_POST['room_type'];

    // Check if the student has already applied
    if (hasStudentApplied($conn, $student_id)) {
        // Check the status of the latest application
        $latest_status = getLatestApplicationStatus($conn, $student_id);

        if ($latest_status === 'rejected' || $latest_status === null) {
            // Insert the application into the database with 'pending' status
            $sql = "INSERT INTO applications (id, student_id, college_name, room_type, status) VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $application_id = uniqid(); // Generate a unique ID for the application
            $stmt->bind_param('ssss', $application_id, $student_id, $college_name, $room_type);
            if ($stmt->execute()) {
                echo "<script>alert('Application submitted successfully.')</script>";
            } else {
                echo "<script>alert('Failed to submit application.')</script>";
            }
        } else {
            echo "<script>alert('You cannot apply again. Your previous application status is {$latest_status}.')</script>";
        }
    } 
}

// Check if form is submitted for applying for accommodation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_accommodation'])) {
    $student_id = $_POST['student_id'];
    $college_name = $_POST['college'];
    $room_type = $_POST['room_type'];

    // Check if the student has already applied
    if (hasStudentApplied($conn, $student_id)) {
        // Check the status of the latest application
        $latest_status = getLatestApplicationStatus($conn, $student_id);

        if ($latest_status === 'rejected' || $latest_status === null) {
            // Insert the application into the database with 'pending' status
            $sql = "INSERT INTO applications (id, student_id, college_name, room_type, status) VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $application_id = uniqid(); // Generate a unique ID for the application
            $stmt->bind_param('ssss', $application_id, $student_id, $college_name, $room_type);
            if ($stmt->execute()) {
                echo "<script>alert('Application submitted successfully.')</script>";
            } else {
                echo "<script>alert('Failed to submit application.')</script>";
            }
        } else {
            echo "<script>alert('You cannot apply again. Your previous application status is {$latest_status}.')</script>";
        }
    } 
    //Handle the case when the student has no previous applications
    else { 
        // Insert the application into the database with 'pending' status
        $sql = "INSERT INTO applications (id, student_id, college_name, room_type, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $application_id = uniqid(); // Generate a unique ID for the application
        $stmt->bind_param('ssss', $application_id, $student_id, $college_name, $room_type);
        if ($stmt->execute()) {
            echo "<script>alert('Application submitted successfully.')</script>";
        } else {
            echo "<script>alert('Failed to submit application.')</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View and Apply Accommodations</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="apply_form_style.css">
    <style>
        /* Add any additional styles specific to this page */
        .stat-box-content{
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .stat-box-content p{
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <?php include 'student_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>View and Apply Accommodations</h1>
        </div>

        <!-- Display Available Accommodations -->
        <table border="1" id="collegeTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Available Single Rooms</th>
                    <th>Available Double Rooms</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($colleges as $college): ?>
                <tr>
                    <td><?php echo $college['name']; ?></td>
                    <td><?php echo $college['available_single_rooms']; ?></td>
                    <td><?php echo $college['available_double_rooms']; ?></td>
                    <td>
                        <button type="button" class="applyBtn" data-college="<?php echo $college['name']; ?>">Apply</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            </tbody>
        </table>
        </div>
        <div id="applyForm" class="modal" style="display:none;">
                <div class="modal-content">
                <h2>Please select your accommodation:</h2>
                                <form action="" method="POST">
                                    <label for="student_id">Student ID:</label>
                                    <input type="text" id="student_id" name="student_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>" required><br>
                                                    
                                    <label for="college">College Name:</label>
                                    <select id="college" name="college" required>
                                        <option value="KRP">KRP</option>
                                        <option value="KTC">KTC</option>
                                        <option value="KTDI">KTDI</option>
                                        <!-- Add more options as needed -->
                                    </select><br>
                                    
                                    <label for="room_type">Room Type:</label>
                                    <select id="room_type" name="room_type" required>
                                        <option value="single">Single</option>
                                        <option value="double">Double</option>
                                    </select><br>
                                    
                                    <button type="submit" name="apply_accommodation">Submit</button>
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="college_name" value="<?php echo $college['name']; ?>">
                                    </form>
                                </form>
                            </div>
                </div>       
        </form>
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

        document.querySelectorAll('.applyBtn').forEach(button => {
            button.addEventListener('click', function() {
                const college = this.getAttribute('data-college');
                document.getElementById('college').value = college;
                document.getElementById('applyForm').style.display = 'block';
            });
        });

    </script>
    <?php include 'footer.html'; ?>

</body>
</html>
