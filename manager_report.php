<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_level'], ['accommodation_manager', 'admin'])) {
    header('Location: login.html');
    exit();
}

if ($_SESSION['user_level'] == 'admin') {
    include 'admin_header.php';
} else {
    include 'manager_header.php';
}

// Include database connection
include 'db_connect.php';

// Accommodation records sorting and searching
$sort_by_accommodation = isset($_GET['sort_by_accommodation']) ? $_GET['sort_by_accommodation'] : 'student_name';
$search_query_accommodation = isset($_GET['search_accommodation']) ? $_GET['search_accommodation'] : '';

$sql_accommodation = "SELECT * FROM accommodation_records WHERE student_id LIKE '%$search_query_accommodation%' ORDER BY $sort_by_accommodation";
$result_accommodation = $conn->query($sql_accommodation);

if ($conn->error) {
    die("Error: " . $conn->error);
}

// Payment status sorting and searching
$sort_by_payment = isset($_GET['sort_by_payment']) ? $_GET['sort_by_payment'] : 'student_id';
$search_query_payment = isset($_GET['search_payment']) ? $_GET['search_payment'] : '';

$sql_payment = "SELECT * FROM payments WHERE student_id LIKE '%$search_query_payment%' ORDER BY $sort_by_payment";
$result_payment = $conn->query($sql_payment);

if ($conn->error) {
    die("Error: " . $conn->error);
}

// Check if the room update form was submitted
if (isset($_POST['update_room'])) {
    $record_id = $_POST['record_id'];
    $new_room_id = $_POST['room_id'];

    // Get the college name and room type for the current record
    $sql_get_current = "SELECT college_name, room_type FROM accommodation_records WHERE record_id = ?";
    $stmt_get_current = $conn->prepare($sql_get_current);
    $stmt_get_current->bind_param('i', $record_id);
    $stmt_get_current->execute();
    $result_get_current = $stmt_get_current->get_result();
    $current_record = $result_get_current->fetch_assoc();
    $college_name = $current_record['college_name'];
    $room_type = $current_record['room_type'];
    $stmt_get_current->close();

    // Check if the new room number already exists for the same college and room type
    $sql_check_room = "SELECT * FROM accommodation_records WHERE room_id = ? AND college_name = ? AND room_type = ?";
    $stmt_check_room = $conn->prepare($sql_check_room);
    $stmt_check_room->bind_param('sss', $new_room_id, $college_name, $room_type);
    $stmt_check_room->execute();
    $result_check_room = $stmt_check_room->get_result();
    $stmt_check_room->close();

    if ($result_check_room->num_rows > 0) {
        echo "<script>alert('Room number already exists for the same college and room type. Please choose a different room number.');</script>";
    } else {
        // Update the room number if it doesn't exist
        $sql_update_room = "UPDATE accommodation_records SET room_id = ? WHERE record_id = ?";
        $stmt_update_room = $conn->prepare($sql_update_room);
        $stmt_update_room->bind_param('si', $new_room_id, $record_id);
        if ($stmt_update_room->execute()) {
            echo "<script>alert('Room number updated successfully.');</script>";
            // Refresh the page to reflect the changes
            echo "<meta http-equiv='refresh' content='0'>";
        } else {
            echo "<script>alert('Failed to update room number.');</script>";
        }
        $stmt_update_room->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommodation Records</title>
    <link rel="stylesheet" href="managerStyle.css">
    <style>
        .tabs {
            overflow: hidden;
        }
        .tab {
            float: left;
            border: 1px solid #ccc;
            cursor: pointer;
            padding: 10px 20px;
            margin-right: 5px;
            background-color:lemonchiffon;
        }
        .tab-content {
            clear: both;
            display: none;
            padding: 20px;
            border: 1px solid #ccc;
            border-top: none;
        }
        .active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <div class="main-content">
    <div class="container">
        <h1>Accommodation Records</h1>
        <div class="tabs">
            <div class="tab" onclick="showTab('accommodation')">Accommodation Status</div>
            <div class="tab" onclick="showTab('payment')">Payment Status</div>
        </div>
        <div id="accommodation" class="tab-content">
            <h2>Accommodation Status</h2>
            <form method="GET" action="">
                <input type="text" name="search_accommodation" placeholder="Search by student id" value="<?php echo htmlspecialchars($search_query_accommodation); ?>">
                <input type="submit" value="Search">
            </form>

            <table>
                <thead>
                    <tr>
                        <th><a href="?sort_by_accommodation=student_id">Student Id</a></th>
                        <th><a href="?sort_by_accommodation=student_name">Student Name</a></th>
                        <th>Room Number</th>
                        <th><a href="?sort_by_accommodation=college_name">College Name</a></th>
                        <th><a href="?sort_by_accommodation=room_type">Room Type</a></th>
                        <th>Check In Date</th>
                        <th>Check Out Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_accommodation->num_rows > 0) {
                        while($row = $result_accommodation->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                            echo "<td>
                            <form method='POST' action=''>
                            <input type='hidden' name='record_id' value='" . htmlspecialchars($row['record_id']) . "'>
                            <input type='text' name='room_id' value='" . htmlspecialchars($row['room_id']) . "'>
                            <button type='submit' name='update_room'>Update</button>
                            </form>
                            </td>";
                            echo "<td>" . htmlspecialchars($row['college_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['room_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['check_in_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['check_out_date']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div id="payment" class="tab-content">
            <h2>Payment Status</h2>
            <form method="GET" action="">
                <input type="text" name="search_payment" placeholder="Search by student id" value="<?php echo htmlspecialchars($search_query_payment); ?>">
                <input type="submit" value="Search">
            </form>

            <table>
                <thead>
                    <tr>
                        <th><a href="?sort_by_payment=student_id">Student Id</a></th>
                        <th>Student Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_payment->num_rows > 0) {
                        while($row = $result_payment->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['payment_date']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No payment records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <script>
        function showTab(tabId) {
            var tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        }

        // Show the first tab by default
        document.addEventListener('DOMContentLoaded', function() {
            showTab('accommodation');
        });

        // Sidebar toggle functionality
        let navigation = document.querySelector('.navigation');
        let menuToggle = document.querySelector('.menuToggle');
        let listItems = document.querySelectorAll('.list');
        let mainContent = document.querySelector('.main-content');

        menuToggle.addEventListener('click', function() {
            navigation.classList.toggle('active');
            if (navigation.classList.contains('active')) {
                mainContent.style.marginLeft = '270px'; // Adjust margin-left when sidebar is active
            } else {
                mainContent.style.marginLeft = '0px'; // Default margin-left when sidebar is inactive
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
    <?php include 'footer.html'; ?>
</body>
</html>

<?php
$conn->close();
?>
