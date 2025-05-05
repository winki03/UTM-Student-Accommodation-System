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

function addCollege($conn, $name, $single_room_capacity, $double_room_capacity, $available_single_rooms, $available_double_rooms) {
    $sql = "INSERT INTO colleges (name, single_room_capacity, double_room_capacity, available_single_rooms, available_double_rooms) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siiii', $name, $single_room_capacity, $double_room_capacity, $available_single_rooms, $available_double_rooms);
    return $stmt->execute();
}

function deleteCollege($conn, $college_name) {
    $sql = "DELETE FROM colleges WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $college_name);
    return $stmt->execute();
}

function updateCollege($conn, $college_name, $single_room_capacity, $double_room_capacity, $available_single_rooms, $available_double_rooms) {
    $sql = "UPDATE colleges SET single_room_capacity=?, double_room_capacity=?, available_single_rooms=?, available_double_rooms=? WHERE name=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiiss', $single_room_capacity, $double_room_capacity, $available_single_rooms, $available_double_rooms, $college_name);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_college'])) {
    $name = $_POST['name'];
    $single_room_capacity = $_POST['single_room_capacity'];
    $double_room_capacity = $_POST['double_room_capacity'];
    $available_single_rooms = $_POST['available_single_rooms'];
    $available_double_rooms = $_POST['available_double_rooms'];
    addCollege($conn, $name, $single_room_capacity, $double_room_capacity, $available_single_rooms, $available_double_rooms);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_college'])) {
    $college_name = $_POST['college_name'];
    deleteCollege($conn, $college_name);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_college'])) {
    $name = $_POST['name'];
    $single_room_capacity = $_POST['single_room_capacity'];
    $double_room_capacity = $_POST['double_room_capacity'];
    $available_single_rooms = $_POST['available_single_rooms'];
    $available_double_rooms = $_POST['available_double_rooms'];
    updateCollege($conn, $name, $single_room_capacity, $double_room_capacity, $available_single_rooms, $available_double_rooms);
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
    <?php include 'admin_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>Manage Colleges</h1>
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
            <button id="openAddModal"> +Add College</button>
        </div>
    
        <!-- Add College Modal -->
        <div id="addCollegeModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New College</h2>
                <form action="" method="POST">
                    <label for="name">College Name:</label>
                    <input type="text" id="name" name="name" required><br>

                    <label for="single_room_capacity">Single Room Capacity:</label>
                    <input type="number" id="single_room_capacity" name="single_room_capacity" required><br>

                    <label for="double_room_capacity">Double Room Capacity:</label>
                    <input type="number" id="double_room_capacity" name="double_room_capacity" required><br>

                    <label for="available_single_rooms">Available Single Rooms:</label>
                    <input type="number" id="available_single_rooms" name="available_single_rooms" required><br>

                    <label for="available_double_rooms">Available Double Rooms:</label>
                    <input type="number" id="available_double_rooms" name="available_double_rooms" required><br>

                    <button type="submit" name="add_college">Add College</button>
                    <button type="button" class="cancelBtn">Cancel</button>
                </form>
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
                        <th>Actions</th>
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
                        <td>
                            <button type="button" class="updateBtn">Update</button>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="college_name" value="<?php echo $college['name']; ?>">
                                <button type="submit" name="delete_college">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <!-- Update College Modal -->
        <div id="updateCollegeModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Update College</h2>
                <form action="" method="POST">
                    <input type="hidden" id="update_name" name="name" required><br>

                    <label for="update_single_room_capacity">Single Room Capacity:</label>
                    <input type="number" id="update_single_room_capacity" name="single_room_capacity" required><br>

                    <label for="update_double_room_capacity">Double Room Capacity:</label>
                    <input type="number" id="update_double_room_capacity" name="double_room_capacity" required><br>

                    <label for="update_available_single_rooms">Available Single Rooms:</label>
                    <input type="number" id="update_available_single_rooms" name="available_single_rooms" required><br>

                    <label for="update_available_double_rooms">Available Double Rooms:</label>
                    <input type="number" id="update_available_double_rooms" name="available_double_rooms" required><br>

                    <button type="submit" name="update_college">Update College</button>
                    <button type="button" class="cancelBtn">Cancel</button>
                </form>
            </div>
        </div>

        <?php include 'footer.html'; ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const addCollegeModal = document.getElementById('addCollegeModal');
        const updateCollegeModal = document.getElementById('updateCollegeModal');
        const openAddModalButton = document.getElementById('openAddModal');
        const searchInput = document.getElementById('search');
        const closeModalButtons = document.querySelectorAll('.close');
        const cancelButtons = document.querySelectorAll('.cancelBtn');

        openAddModalButton.onclick = function () {
            addCollegeModal.style.display = 'block';
        }

        window.onclick = function (event) {
            if (event.target == addCollegeModal) {
                addCollegeModal.style.display = 'none';
            } else if (event.target == updateCollegeModal) {
                updateCollegeModal.style.display = 'none';
            }
        }

        closeModalButtons.forEach(button => {
            button.onclick = () => {
                addCollegeModal.style.display = 'none';
                updateCollegeModal.style.display = 'none';
            }
        });

        cancelButtons.forEach(button => {
            button.onclick = () => {
                addCollegeModal.style.display = 'none';
                updateCollegeModal.style.display = 'none';
            }
        });

        document.querySelectorAll('.updateBtn').forEach(button => {
            button.onclick = function () {
                const row = button.closest('tr');
                const name = row.cells[0].textContent;
                const singleRoomCapacity = row.cells[1].textContent;
                const doubleRoomCapacity = row.cells[2].textContent;
                const availableSingleRooms = row.cells[3].textContent;
                const availableDoubleRooms = row.cells[4].textContent;

                document.getElementById('update_name').value = name;
                document.getElementById('update_single_room_capacity').value = singleRoomCapacity;
                document.getElementById('update_double_room_capacity').value = doubleRoomCapacity;
                document.getElementById('update_available_single_rooms').value = availableSingleRooms;
                document.getElementById('update_available_double_rooms').value = availableDoubleRooms;

                updateCollegeModal.style.display = 'block';
            }
        });

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
