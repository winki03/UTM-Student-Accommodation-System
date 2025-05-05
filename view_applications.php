<?php
include 'db_connect.php';

function getAllApplications($conn) {
    // Function to fetch all applications from the database
    $sql = "SELECT * FROM applications";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

$applications = getAllApplications($conn);

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
        .status-button {
            padding: 5px 10px;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: default;
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
        .filter-menu button.active {
            background-color: #62a2da;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="main-content">
        <div class="title">
            <h1>View Applications</h1>
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
            <div class = "search">
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
