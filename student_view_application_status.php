<?php
session_start();
include 'student_header.php';
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Prepare and execute the query to fetch application details with accommodation names
$sql = "SELECT applications.id, colleges.name AS accommodation_name, applications.room_type, applications.status, applications.reason_for_rejection
        FROM applications
        JOIN colleges ON applications.college_name = colleges.name
        WHERE applications.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
}

// Handle payment request
if (isset($_POST['make_payment'])) {
    $application_id = $_POST['application_id'];

    // Update the payment status to 'paid' in the payments table
    $sql_update_payment = "UPDATE payments SET status = 'paid' WHERE student_id = ? AND application_id = ?";
    $stmt_update_payment = $conn->prepare($sql_update_payment);
    $stmt_update_payment->bind_param('ss', $user_id, $application_id);
    if ($stmt_update_payment->execute()) {
        echo "<script>alert('Payment successful.');</script>";
        // Refresh the page to reflect the changes
        echo "<meta http-equiv='refresh' content='0'>";
    } else {
        echo "<script>alert('Failed to update payment status.');</script>";
    }
    $stmt_update_payment->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="studview.css">

    <style>
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
    </style>
</head>

<body>
    <div class="main-content">
        <div class="title">
            <h1>Application Status</h1>

            <p>Dear student, you can check the status of your accommodation applications below:</p>
            <p><span style="color: red;">*</span>Please note that only bookings that have been
                approved are eligible to make payment and view the booking slip.</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Application ID</th>
                    <th>Accommodation</th>
                    <th>Room Type</th>
                    <th>Status</th>
                    <th>Rejection Reason</th>
                    <th>Payment</th>
                    <th>Slip</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application['id']); ?></td>
                        <td><?php echo htmlspecialchars($application['accommodation_name']); ?></td>
                        <td><?php echo htmlspecialchars($application['room_type']); ?></td>
                        <td>
                            <button class="status-button <?php echo strtolower($application['status']); ?>">
                                <?php echo ucfirst($application['status']); ?>
                            </button>
                        </td>
                        <td><?php echo htmlspecialchars($application['reason_for_rejection']) ? htmlspecialchars($application['reason_for_rejection']) : 'N/A'; ?></td>
                        <td>
                            <?php if ($application['status'] === 'approved'): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id']); ?>">
                                    <button type="submit" name="make_payment">Make Payment</button>
                                </form>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($application['status'] === 'approved'): ?>
                                <a href="slip.php?application_id=<?php echo urlencode($application['id']); ?>">View Slip</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="6">No applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Sidebar toggle functionality
        let navigation = document.querySelector('.navigation');
        let menuToggle = document.querySelector('.menuToggle');
        let listItems = document.querySelectorAll('.list');
        let mainContent = document.querySelector('.main-content');

        menuToggle.addEventListener('click', function () {
            navigation.classList.toggle('active');
            if (navigation.classList.contains('active')) {
                mainContent.style.marginLeft = '310px'; // Adjust margin-left when sidebar is active
            } else {
                mainContent.style.marginLeft = '140px'; // Default margin-left when sidebar is inactive
            }
        });

        listItems.forEach(item => {
            item.addEventListener('click', function () {
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
            button.addEventListener('click', function () {
                const college = this.getAttribute('data-college');
                document.getElementById('college').value = college;
                document.getElementById('applyForm').style.display = 'block';
            });
        });

    </script>
    <?php include 'footer.html'; ?>
</body>

</html>

<?php
$conn->close();
?>
