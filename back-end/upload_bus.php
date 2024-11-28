<?php
session_start();
require_once 'db_connect.php';

// Initialize message variables
$success_message = "";
$error_message = "";

// Function to get all unique locations
function getLocations($conn) {
    $sql = "SELECT DISTINCT from_location FROM buses UNION SELECT DISTINCT to_location FROM buses";
    $result = $conn->query($sql);
    $locations = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $locations[] = $row['from_location'];
        }
    }
    return $locations;
}

$locations = getLocations($conn);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_manual'])) {
        $from_location = isset($_POST['from_location']) ? mysqli_real_escape_string($conn, $_POST['from_location']) : '';
        $to_location = isset($_POST['to_location']) ? mysqli_real_escape_string($conn, $_POST['to_location']) : '';
        $bus_name = isset($_POST['bus_name']) ? mysqli_real_escape_string($conn, $_POST['bus_name']) : '';
        $bus_number = isset($_POST['bus_number']) ? mysqli_real_escape_string($conn, $_POST['bus_number']) : '';
        $bus_route = isset($_POST['bus_route']) ? mysqli_real_escape_string($conn, $_POST['bus_route']) : '';

        // Insert data into the buses table
        $stmt = $conn->prepare("INSERT INTO buses (from_location, to_location, bus_name, bus_number, bus_route) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $from_location, $to_location, $bus_name, $bus_number, $bus_route);
        
        if ($stmt->execute()) {
            $bus_id = $stmt->insert_id; // Get the ID of the inserted bus record

            // Insert timings into bus_timings table
            $timing_errors = [];
            foreach ($_POST['departure_time'] as $index => $departure_time) {
                $arrival_time = $_POST['arrival_time'][$index];
                if (empty($departure_time) || empty($arrival_time)) {
                    continue; // Skip empty timings
                }

                $stmt = $conn->prepare("INSERT INTO bus_timings (bus_id, departure_time, arrival_time) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $bus_id, $departure_time, $arrival_time);
                
                if (!$stmt->execute()) {
                    $timing_errors[] = "Error adding timing entry!";
                }
            }

            if (empty($timing_errors)) {
                $success_message = "Bus details and timings added successfully!";
                // Refresh the locations list
                $locations = getLocations($conn);
            } else {
                $error_message = implode("<br>", $timing_errors);
            }
        } else {
            $error_message = "Error adding bus details!";
        }
    } elseif (isset($_POST['submit_bulk'])) {
        // Handle bulk import
        if (isset($_FILES['bulk_file']) && $_FILES['bulk_file']['error'] == 0) {
            $file_name = $_FILES['bulk_file']['name'];
            $file_size = $_FILES['bulk_file']['size'];
            $file_tmp = $_FILES['bulk_file']['tmp_name'];
            $file_type = $_FILES['bulk_file']['type'];
            $file_ext = strtolower(end(explode('.', $file_name)));
            
            $extensions = array("csv", "xlsx");
            
            if (in_array($file_ext, $extensions) === false) {
                $error_message = "Extension not allowed, please choose a CSV or XLSX file.";
            } elseif ($file_size > 2097152) {
                $error_message = 'File size must be less than 2 MB';
            } else {
                // Process the file
                if ($file_ext == "csv") {
                    $handle = fopen($file_tmp, "r");
                    $data = array();
                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $data[] = $row;
                    }
                    fclose($handle);
                } else {
                    // For XLSX files, you'll need to use a library like PhpSpreadsheet
                    // This is a placeholder for XLSX processing
                    $data = array(); // Replace this with actual XLSX processing
                }
                
                // Store the data in session for preview
                $_SESSION['bulk_import_data'] = $data;
                
                // Redirect to preview page
                header("Location: preview_bulk_import.php");
                exit();
            }
        } else {
            $error_message = "No file uploaded or an error occurred during upload.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bus Timings - Bus Timing App</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f39c12;
            --background-color: #f4f7f9;
            --text-color: #333;
            --card-bg: #ffffff;
            --sidebar-bg: #2c3e50;
            --sidebar-hover: #34495e;
        }

        .dark-theme {
            --primary-color: #3a7bd5;
            --secondary-color: #f1c40f;
            --background-color: #1a1a1a;
            --text-color: #f4f4f4;
            --card-bg: #2c2c2c;
            --sidebar-bg: #1c2833;
            --sidebar-hover: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            color: #ecf0f1;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .sidebar h1 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #ecf0f1;
            text-align: center;
        }

        .sidebar-menu {
            list-style-type: none;
        }

        .sidebar-menu li {
            margin-bottom: 15px;
        }

        .sidebar-menu a {
            color: #ecf0f1;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--sidebar-hover);
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: var(--card-bg);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--background-color);
            border-radius: 20px;
            padding: 5px 15px;
        }

        .search-bar input {
            border: none;
            background: transparent;
            padding: 5px;
            font-size: 14px;
            color: var(--text-color);
            width: 200px;
        }

        .search-bar i {
            color: var(--text-color);
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        form {
            display: grid;
            gap: 15px;
        }

        label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        input[type="time"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        button {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3a7bd5;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: 500;
        }

        .alert.success {
            background-color: #2ecc71;
            color: #fff;
        }

        .alert.error {
            background-color: #e74c3c;
            color: #fff;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 24px;
            cursor: pointer;
        }

        .theme-options {
            margin-top: 20px;
            padding: 10px;
            background-color: var(--sidebar-hover);
            border-radius: 5px;
        }

        .theme-options h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #ecf0f1;
        }

        .theme-btn {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 5px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .theme-btn:hover {
            background-color: #3a7bd5;
        }

        .timing-entry {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .import-section {
            margin-top: 20px;
            padding: 20px;
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .import-section h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .file-input {
            margin-bottom: 15px;
        }
        
        .file-input input[type="file"] {
            display: none;
        }
        
        .file-input label {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: #ffffff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .file-input label:hover {
            background-color: #3a7bd5;
        }
        
        #file-name {
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: fixed;
                top: 0;
                left: -100%;
                height: 100%;
                z-index: 1000;
                transition: left 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-top: 60px;
                padding: 20px;
            }

            .top-bar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 100;
                border-radius: 0;
            }

            .menu-toggle {
                display: block;
            }

            .search-bar {
                display: none;
            }

            .user-info span {
                display: none;
            }

            .timing-entry {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar" id="sidebar">
            <h1>Bus Timing App</h1>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="upload_bus.php" class="active"><i class="fas fa-bus"></i> Add Bus Data</a></li>
                <li><a href="upload_announcement.php"><i class="fas fa-bullhorn"></i> Add Announcement</a></li>
                <li><a href="upload_notification.php"><i class="fas fa-bell"></i> Add Notification</a></li>
                <li><a href="contact_data.php"><i class="fas fa-address-book"></i> Contact Database</a></li>
                <li><a href="recent_items.php"><i class="fas fa-clock"></i> Recent Items</a></li>
                <li><a href="../index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <div class="theme-options">
                <h3>Change Theme</h3>
                <button class="theme-btn" onclick="changeTheme('sidebar')">Sidebar Only</button>
                <button class="theme-btn" onclick="changeTheme('content')">Content Only</button>
                <button class="theme-btn" onclick="changeTheme('all')">Entire Page</button>
            </div>
        </div>
        <div class="main-content">
            <div class="top-bar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-info">
                    <img src="../uploads/admin.jpg" alt="Admin Avatar">
                    <span>Welcome Admin</span>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert success" id="successAlert"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert error" id="errorAlert"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Upload Bus Timings</h2>
                <form method="post" action="" class="bus-form">
                    <div class="form-group">
                        <label for="from_location">From Location:</label>
                        <input type="text" id="from_location" name="from_location" required>
                    </div>

                    <div class="form-group">
                        <label for="to_location">To Location:</label>
                        <input type="text" id="to_location" name="to_location" required>
                    </div>

                    <div class="form-group">
                        <label for="bus_name">Bus Name:</label>
                        <input type="text" id="bus_name" name="bus_name" required>
                    </div>

                    <div class="form-group">
                        <label for="bus_number">Bus Number:</label>
                        <input type="text" id="bus_number" name="bus_number" required>
                    </div>

                    <div class="form-group">
                        <label for="bus_route">Bus Route:</label>
                        <input type="text" id="bus_route" name="bus_route" required>
                    </div>
                    
                    <div id="timings">
                        <div class="timing-entry">
                            <label>Departure Time:</label>
                            <input type="time" name="departure_time[]" required>

                            <label>Arrival Time:</label>
                            <input type="time" name="arrival_time[]" required>

                            <button type="button" onclick="removeTiming(this)">Remove Timing</button>
                        </div>
                    </div>
                    <button type="button" onclick="addTiming()">Add Another Timing</button>
                    <button type="submit" name="submit_manual">Upload</button>
                </form>
            </div>

            <div class="import-section">
                <h3>Bulk Import Bus Data</h3>
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="file-input">
                        <input type="file" name="bulk_file" id="bulk_file" accept=".csv, .xlsx" required>
                        <label for="bulk_file">Choose File</label>
                        <span id="file-name">No file chosen</span>
                    </div>
                    <button type="submit" name="submit_bulk">Import</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addTiming() {
            const timingsDiv = document.getElementById('timings');
            const newEntry = document.createElement('div');
            newEntry.className = 'timing-entry';
            newEntry.innerHTML = `
                <label>Departure Time:</label>
                <input type="time" name="departure_time[]" required>

                <label>Arrival Time:</label>
                <input type="time" name="arrival_time[]" required>

                <button type="button" onclick="removeTiming(this)">Remove Timing</button>
            `;
            timingsDiv.appendChild(newEntry);
        }

        function removeTiming(button) {
            const entry = button.closest('.timing-entry');
            entry.remove();
        }

        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert || errorAlert) {
            setTimeout(() => {
                if (successAlert) successAlert.style.display = 'none';
                if (errorAlert) errorAlert.style.display = 'none';
            }, 3000);
        }

        function changeTheme(target) {
            const body = document.body;
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');

            switch (target) {
                case 'sidebar':
                    sidebar.classList.toggle('dark-theme');
                    break;
                case 'content':
                    mainContent.classList.toggle('dark-theme');
                    break;
                case 'all':
                    body.classList.toggle('dark-theme');
                    break;
            }

            localStorage.setItem('themePreference', JSON.stringify({
                sidebar: sidebar.classList.contains('dark-theme'),
                content: mainContent.classList.contains('dark-theme'),
                all: body.classList.contains('dark-theme')
            }));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = JSON.parse(localStorage.getItem('themePreference'));
            if (savedTheme) {
                if (savedTheme.sidebar) document.getElementById('sidebar').classList.add('dark-theme');
                if (savedTheme.content) document.querySelector('.main-content').classList.add('dark-theme');
                if (savedTheme.all) document.body.classList.add('dark-theme');
            }
        });

        document.getElementById('bulk_file').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
</body>
</html>