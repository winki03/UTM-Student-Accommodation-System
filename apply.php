<?php
// Include database connection
include 'db_connect.php';

$college = '';
if (isset($_GET['college'])) {
    $college = $_GET['college'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Accommodation</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="apply_form_style.css">
</head>
<body>
    <h1>Apply for Accommodation</h1>

    <form action="submit_application.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        
        <label for="college">College:</label>
        <input type="text" id="college" name="college" value="<?php echo htmlspecialchars($college); ?>" readonly><br>
        
        <label for="room_type">Room Type:</label>
        <select id="room_type" name="room_type" required>
            <option value="single">Single</option>
            <option value="double">Double</option>
        </select><br>
        
        <button type="submit" name="apply_accommodation">Submit Application</button>
    </form>
</body>
</html>
