<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['bulk_import_data'])) {
    $csv_url = 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/bus%20data-GbeOtZiEOQ2qPt7Yyq16w2DbSgJRB7.csv';
    $csv_content = file_get_contents($csv_url);
    
    if ($csv_content === false) {
        die("Failed to fetch CSV file from the URL.");
    }
    
    $lines = explode("\n", $csv_content);
    $data = array_map('str_getcsv', $lines);
    
    $_SESSION['bulk_import_data'] = $data;
} else {
    $data = $_SESSION['bulk_import_data'];
}

function formatTime($time) {
    $parsed_time = date_parse($time);
    
    if ($parsed_time['error_count'] > 0 || preg_match('/^\d{1,2}:\d{2}\s?(?:AM|PM)$/i', $time)) {
        return strtoupper($time);
    }
    
    if (isset($parsed_time['hour']) && isset($parsed_time['minute'])) {
        return date('h:i A', strtotime($parsed_time['hour'] . ':' . $parsed_time['minute']));
    }
    
    return strtoupper($time);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_import'])) {
    $success_count = 0;
    $error_count = 0;

    foreach ($data as $index => $row) {
        if ($index == 0) continue; // Skip header row

        $from_location = $row[0];
        $to_location = $row[1];
        $bus_name = $row[2];
        $bus_number = $row[3];
        $bus_route = $row[4];
        $departure_time = formatTime($row[6]);
        $arrival_time = formatTime($row[7]);

        // Insert into buses table
        $stmt = $conn->prepare("INSERT INTO buses (from_location, to_location, bus_name, bus_number, bus_route) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $from_location, $to_location, $bus_name, $bus_number, $bus_route);
        
        if ($stmt->execute()) {
            $bus_id = $stmt->insert_id;

            // Insert into bus_timings table
            $stmt = $conn->prepare("INSERT INTO bus_timings (bus_id, departure_time, arrival_time) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $bus_id, $departure_time, $arrival_time);
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
        } else {
            $error_count++;
        }
    }

    $message = "Import completed. Successfully imported: $success_count. Errors: $error_count.";
    unset($_SESSION['bulk_import_data']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Bulk Import - Bus Timing App</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid var(--text-color);
            padding: 10px;
            text-align: left;
        }
        
        th {
            background-color: var(--primary-color);
            color: #ffffff;
        }

        button, .btn {
            background-color: var(--primary-color);
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        button:hover, .btn:hover {
            background-color: var(--secondary-color);
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

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 24px;
            cursor: pointer;
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

            table {
                font-size: 14px;
            }

            th, td {
                padding: 5px;
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

            <?php if (isset($message)): ?>
                <div class="alert success" id="messageAlert"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Preview Bulk Import</h2>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>From Location</th>
                                <th>To Location</th>
                                <th>Bus Name</th>
                                <th>Bus Number</th>
                                <th>Bus Route</th>
                                <th>Departure Time</th>
                                <th>Arrival Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $index => $row): ?>
                                <?php if ($index == 0) continue; // Skip header row ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row[0]); ?></td>
                                    <td><?php echo htmlspecialchars($row[1]); ?></td>
                                    <td><?php echo htmlspecialchars($row[2]); ?></td>
                                    <td><?php echo htmlspecialchars($row[3]); ?></td>
                                    <td><?php echo htmlspecialchars($row[4]); ?></td>
                                    <td><?php echo htmlspecialchars(formatTime($row[6])); ?></td>
                                    <td><?php echo htmlspecialchars(formatTime($row[7])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <form method="post" action="">
                    <button type="submit" name="confirm_import">Confirm Import</button>
                    <a href="upload_bus.php" class="btn">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script>
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

        const messageAlert = document.getElementById('messageAlert');
        if (messageAlert) {
            setTimeout(() => {
                messageAlert.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>