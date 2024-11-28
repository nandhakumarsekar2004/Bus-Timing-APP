<?php
session_start();
require_once 'db_connect.php';

// Initialize message variables
$success_message = "";
$error_message = "";

function handleDeletion($conn, $delete_id, $delete_type) {
    $types = [
        'announcement' => [
            'sql' => "DELETE FROM announcements WHERE id = ?",
            'success' => "Announcement deleted successfully!",
            'error' => "Error deleting announcement: "
        ],
        'notification' => [
            'sql' => "DELETE FROM notifications WHERE notification_id = ?",
            'success' => "Notification deleted successfully!",
            'error' => "Error deleting notification: "
        ],
        'bus' => [
            'sql' => "DELETE FROM buses WHERE bus_id = ?",
            'success' => "Bus entry deleted successfully!",
            'error' => "Error deleting bus entry: "
        ]
    ];

    if (array_key_exists($delete_type, $types)) {
        $stmt = $conn->prepare($types[$delete_type]['sql']);
        if ($stmt) {
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                return $types[$delete_type]['success'];
            }
            return $types[$delete_type]['error'] . $stmt->error;
        }
        return "Error preparing statement: " . $conn->error;
    }
    return "Invalid delete type.";
}

// Usage Example
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['delete_type'])) {
    $success_message = handleDeletion($conn, $_POST['delete_id'], $_POST['delete_type']);
}

// Fetch announcements
$announcements_result = $conn->query("SELECT id, title, description, file_path, created_at FROM announcements ORDER BY created_at DESC");
if ($announcements_result === false) {
    $error_message .= "Error fetching announcements: " . $conn->error . "<br>";
}

// Fetch notifications
$notifications_result = $conn->query("SELECT notification_id, title, message, created_at, is_read FROM notifications ORDER BY created_at DESC");
if ($notifications_result === false) {
    $error_message .= "Error fetching notifications: " . $conn->error . "<br>";
}

// Fetch buses
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$buses_sql = "SELECT b.*, 
               GROUP_CONCAT(DISTINCT CONCAT(bt.departure_time, ' - ', bt.arrival_time) ORDER BY bt.departure_time SEPARATOR ', ') as bus_times 
               FROM buses b 
               LEFT JOIN bus_timings bt ON b.bus_id = bt.bus_id";

if (!empty($search_query)) {
    $buses_sql .= " WHERE b.bus_name LIKE ? OR b.from_location LIKE ? OR b.to_location LIKE ?";
    $buses_sql .= " GROUP BY b.bus_id ORDER BY b.created_at DESC";
    $stmt = $conn->prepare($buses_sql);
    $search_param = "%$search_query%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $buses_result = $stmt->get_result();
} else {
    $buses_sql .= " GROUP BY b.bus_id ORDER BY b.created_at DESC";
    $buses_result = $conn->query($buses_sql);
}

if ($buses_result === false) {
    $error_message .= "Error fetching buses: " . $conn->error . "<br>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Items - Bus Timing App</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #333333;
            --primary-color: #00BFFF;
            --secondary-color: #FFA500;
            --accent-color: #32CD32;
            --sidebar-bg: #f0f0f0;
            --card-bg: #ffffff;
        }

        .dark-mode {
            --bg-color: #2E2E2E;
            --text-color: #D3D3D3;
            --primary-color: #00BFFF;
            --secondary-color: #FFA500;
            --accent-color: #32CD32;
            --sidebar-bg: #1E1E1E;
            --card-bg: #3E3E3E;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--sidebar-bg);
            padding: 20px;
            transition: background-color 0.3s;
        }

        .sidebar h1 {
            font-size: 24px;
            margin-bottom: 30px;
            color: var(--primary-color);
        }

        .sidebar-menu {
            list-style-type: none;
        }

        .sidebar-menu li {
            margin-bottom: 15px;
        }

        .sidebar-menu a {
            color: var(--text-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: #ffffff;
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
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--card-bg);
            border-radius: 20px;
            padding: 5px 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-bar input {
            border: none;
            background: transparent;
            padding: 5px;
            color: var(--text-color);
        }

        .search-bar i {
            color: var(--primary-color);
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
            transition: transform 0.3s, box-shadow 0.3s;
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
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--text-color);
        }

        th {
            background-color: var(--primary-color);
            color: #ffffff;
        }

        tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .delete-form button {
            background-color: #ff6b6b;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-form button:hover {
            background-color: #ff4757;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .alert.success {
            background-color: var(--accent-color);
            color: #ffffff;
        }

        .alert.error {
            background-color: #ff6b6b;
            color: #ffffff;
        }

        #darkModeToggle {
            background-color: var(--primary-color);
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #darkModeToggle:hover {
            background-color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                margin-bottom: 20px;
            }

            .main-content {
                padding: 20px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                width: 100%;
                margin-bottom: 15px;
            }

            .user-info {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h1>Bus Timing App</h1>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="upload_bus.php"><i class="fas fa-bus"></i> Add Bus Data</a></li>
                <li><a href="upload_announcement.php"><i class="fas fa-bullhorn"></i> Add Announcement</a></li>
                <li><a href="upload_notification.php"><i class="fas fa-bell"></i> Add Notification</a></li>
                <li><a href="contact_data.php"><i class="fas fa-address-book"></i> Contact Database</a></li>
                <li><a href="recent_items.php" class="active"><i class="fas fa-clock"></i> Recent Items</a></li>
                <li><a href="../index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <button id="darkModeToggle">Toggle Dark Mode</button>
        </div>
        <div class="main-content">
            <div class="top-bar">
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
            <?php elseif ($error_message): ?>
                <div class="alert error" id="errorAlert"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="card">
                <h2>Recent Announcements</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $announcements_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <form method="POST" class="delete-form">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="delete_type" value="announcement">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Recent Notifications</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Created At</th>
                            <th>Is Read</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $notifications_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td><?php echo $row['is_read'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <form method="POST" class="delete-form">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['notification_id']; ?>">
                                        <input type="hidden" name="delete_type" value="notification">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Recent Bus Entries</h2>
                <form method="GET" action="recent_items.php" class="search-form">
                    <input type="text" name="search" placeholder="Search by Bus Name or Location" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
                <table>
                    <thead>
                        <tr>
                            <th>Bus Name</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Times</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $buses_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['bus_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['from_location']); ?></td>
                                <td><?php echo htmlspecialchars($row['to_location']); ?></td>
                                <td><?php echo htmlspecialchars($row['bus_times']); ?></td>
                                <td>
                                    <form method="POST" class="delete-form">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['bus_id']; ?>">
                                        <input type="hidden" name="delete_type" value="bus">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', body.classList.contains('dark-mode'));
        });

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            body.classList.add('dark-mode');
        }

        // Show alert for 2 seconds, then hide it
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        if (successAlert || errorAlert) {
            setTimeout(() => {
                if (successAlert) successAlert.style.display = 'none';
                if (errorAlert) errorAlert.style.display = 'none';
            }, 2000);
        }
    </script>
</body>
</html>