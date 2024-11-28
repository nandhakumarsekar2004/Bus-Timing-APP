<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Function to safely execute queries
function safe_query($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) {
        // Check if the error is due to a missing table or column
        if ($conn->errno == 1146 || $conn->errno == 1054) {
            return false; // Return false instead of dying
        } else {
            die("Query failed: " . $conn->error);
        }
    }
    return $result;
}

// Fetch summary data
$total_buses = safe_query($conn, "SELECT COUNT(*) as count FROM buses");
$total_buses = $total_buses ? $total_buses->fetch_assoc()['count'] : 0;

$total_announcements = safe_query($conn, "SELECT COUNT(*) as count FROM announcements");
$total_announcements = $total_announcements ? $total_announcements->fetch_assoc()['count'] : 0;

$total_notifications = safe_query($conn, "SELECT COUNT(*) as count FROM notifications");
$total_notifications = $total_notifications ? $total_notifications->fetch_assoc()['count'] : 0;

$total_contacts = safe_query($conn, "SELECT COUNT(*) as count FROM contact_data");
$total_contacts = $total_contacts ? $total_contacts->fetch_assoc()['count'] : 0;

// Fetch recent activity
$recent_activity_query = "
    (SELECT 'bus' as type, bus_name as title, created_at FROM buses ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'announcement' as type, title, created_at FROM announcements ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'notification' as type, title, created_at FROM notifications ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC LIMIT 10
";
$recent_activity = safe_query($conn, $recent_activity_query);

// If the query fails (possibly due to missing tables or columns), set $recent_activity to false
if (!$recent_activity) {
    $recent_activity = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bus Timing App</title>
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

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
        }

        .recent-activity {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .recent-activity h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--text-color);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
        }

        .activity-icon i {
            color: #ffffff;
        }

        .activity-details {
            flex: 1;
        }

        .activity-details h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .activity-details p {
            font-size: 14px;
            color: var(--text-color);
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
                <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="upload_bus.php"><i class="fas fa-bus"></i> Add Bus Data</a></li>
                <li><a href="upload_announcement.php"><i class="fas fa-bullhorn"></i> Add Announcement</a></li>
                <li><a href="upload_notification.php"><i class="fas fa-bell"></i> Add Notification</a></li>
                <li><a href="contact_data.php"><i class="fas fa-address-book"></i> Contact Database</a></li>
                <li><a href="recent_items.php"><i class="fas fa-sign-out-alt"></i> Recent Items</a></li>
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
                    <span>Welcome Nandhakumar Sekar</span>
                </div>
            </div>
            <div class="summary-cards">
                <div class="card">
                    <h3>Total Buses</h3>
                    <p><?php echo $total_buses; ?></p>
                </div>
                <div class="card">
                    <h3>Announcements</h3>
                    <p><?php echo $total_announcements; ?></p>
                </div>
                <div class="card">
                    <h3>Notifications</h3>
                    <p><?php echo $total_notifications; ?></p>
                </div>
                <div class="card">
                    <h3>Contacts</h3>
                    <p><?php echo $total_contacts; ?></p>
                </div>
            </div>
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <?php if ($recent_activity && $recent_activity->num_rows > 0): ?>
                    <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo $activity['type'] === 'bus' ? 'bus' : ($activity['type'] === 'announcement' ? 'bullhorn' : 'bell'); ?>"></i>
                            </div>
                            <div class="activity-details">
                                <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                <p><?php echo $activity['type']; ?> - <?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No recent activity to display.</p>
                <?php endif; ?>
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
    </script>
</body>
</html>