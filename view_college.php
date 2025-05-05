<?php
include 'db_connect.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Colleges</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
    <?php include 'manager_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>View Colleges</h1>
        </div>       
        
        <h3>College Statistics</h3>
        
        <div id="collegeStatistics" class="statistic">
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/totalcollege.png" alt="totalcolleges">
                    <p>Total Colleges </p>
                    <p><b><?php echo count($colleges); ?></b></p> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/singleroom.png" alt="totalsinglecapacity">
                    <p>Single Room Capicity </p>
                    <p><b><?php echo array_sum(array_column($colleges, 'single_room_capacity')); ?></b></p> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/doubleroom.png" alt="totaldoublecapacity">
                    <p>Double Room Capacity </p>
                    <p><b><?php echo array_sum(array_column($colleges, 'double_room_capacity')); ?></b></p>  
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/available.png" alt="totalavailablesingle">
                    <p>Available Single Room </p>
                    <p><b><?php echo array_sum(array_column($colleges, 'available_single_rooms')); ?></b></p> 
                </div>  
            </div>
            <div class="stat-box">
                <div class="stat-box-content">
                    <img src="images/available.png" alt="totalavailabledouble">
                    <p>Available Double Room </p>
                    <p><b><?php echo array_sum(array_column($colleges, 'available_double_rooms')); ?></b></p> 
                </div>  
            </div>
        </div>

        <div class="search-filter">
            <div class = "search">
                <input type="text" id="search" placeholder="Search Colleges...">
            </div>
        </div>

        <form action="" method="POST">
            <table border="1" id="collegeTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Single Room Capacity</th>
                        <th>Double Room Capacity</th>
                        <th>Available Single Rooms</th>
                        <th>Available Double Rooms</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($colleges as $college): ?>
                    <tr>
                        <td><?php echo $college['name']; ?></td>
                        <td><?php echo $college['single_room_capacity']; ?></td>
                        <td><?php echo $college['double_room_capacity']; ?></td>
                        <td><?php echo $college['available_single_rooms']; ?></td>
                        <td><?php echo $college['available_double_rooms']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <?php include 'footer.html'; ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');

        searchInput.oninput = function () {
            const searchValue = searchInput.value.toLowerCase();
            document.querySelectorAll('#collegeTable tbody tr').forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                if (name.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
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
