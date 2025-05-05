<?php
include 'db_connect.php'; // Include your database connection script here

if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    // Prepare and execute query to fetch application details including student's name, accommodation details, and payment information
    $sql = "SELECT applications.student_id, applications.college_name, applications.room_type, users.real_name,
                   payments.amount, payments.status
            FROM applications
            JOIN users ON applications.student_id = users.id
            LEFT JOIN accommodation_records ON applications.student_id = accommodation_records.student_id
                                              AND applications.college_name = accommodation_records.college_name
                                              AND applications.room_type = accommodation_records.room_type
            LEFT JOIN payments ON applications.student_id = payments.student_id
            WHERE applications.id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        trigger_error($conn->error);
    }
    $stmt->bind_param('s', $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
        trigger_error($stmt->error);
    }

    // Check if a row is found
    if ($result->num_rows > 0) {
        $application = $result->fetch_assoc();
        $student_name = htmlspecialchars($application['real_name']);
        $student_id = htmlspecialchars($application['student_id']);
        $college_name = htmlspecialchars($application['college_name']);
        $room_type = htmlspecialchars($application['room_type']);
        $payment_amount = htmlspecialchars($application['amount']);
        $payment_status = htmlspecialchars($application['status']);

        ?>


        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Booking Slip</title>
            <link rel="stylesheet" href="slip.css">
        </head>

        <body>
            <div class="slip-container">
                <h1>Booking Confirmation Slip</h1>
                <p><strong>Student Name:</strong> <?php echo $student_name; ?></p>
                <p><strong>Student ID:</strong> <?php echo $student_id; ?></p>
                <br>
                <br>
                <table>
                    <tr>
                        <th>College Name</th>
                        <th>Room Type</th>
                    

                    </tr>
                    <tr>
                        <td><?php echo $college_name; ?></td>
                        <td><?php echo $room_type; ?></td>
             
                    </tr>
                </table>
                <br>
                <br>
                <p><strong>Payment Amount:</strong> <?php echo $payment_amount; ?></p>
                <p><strong>Payment Status:</strong> <?php echo $payment_status; ?></p>
            </div>
        </body>

        </html>
        <?php
    } else {
        echo "<p>No application found with ID: " . htmlspecialchars($application_id) . "</p>";
    }

} else {
    // Handle case where application_id parameter is missing in URL
    echo "<p>Application ID not provided.</p>";
}
?>