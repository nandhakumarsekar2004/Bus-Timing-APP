<?php
session_start();
// Include the database connection
include 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Use a default user ID if not set

// Fetch all notifications from the database
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Get the maximum notification ID
$max_id_sql = "SELECT MAX(notification_id) as max_id FROM notifications";
$max_id_result = mysqli_query($conn, $max_id_sql);
$max_id_row = mysqli_fetch_assoc($max_id_result);
$max_notification_id = $max_id_row['max_id'];

// Update the user's last viewed notification ID
$update_sql = "INSERT INTO user_notification_status (user_id, last_viewed_notification_id) 
               VALUES (?, ?) 
               ON DUPLICATE KEY UPDATE last_viewed_notification_id = ?";
$update_stmt = $conn->prepare($update_sql);

if ($update_stmt === false) {
    die("Error preparing update statement: " . $conn->error);
}

$update_stmt->bind_param("iii", $user_id, $max_notification_id, $max_notification_id);

if (!$update_stmt->execute()) {
    die("Error executing update statement: " . $update_stmt->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notifications</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            padding-bottom: 80px;
        }

        h2 {
            color: #1976D2;
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: 500;
        }

        .notification {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .notification:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .notification h3 {
            color: #1976D2;
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 500;
        }

        .notification p {
            color: #555;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .notification small {
            color: #888;
            font-style: italic;
            font-size: 12px;
        }

        .no-notifications {
            text-align: center;
            font-size: 16px;
            color: #888;
            margin-top: 30px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #fff;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #757575;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.3s ease;
        }

        .nav-item i {
            font-size: 24px;
            margin-bottom: 2px;
        }

        .nav-item.active {
            color: #1976D2;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 22px;
            }

            .notification {
                padding: 15px;
            }

            .notification h3 {
                font-size: 16px;
            }

            .notification p {
                font-size: 13px;
            }

            .notification small {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Notifications</h2>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='notification'>";
                echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                echo "<p>" . htmlspecialchars($row['message']) . "</p>";
                echo "<small>Sent on: " . date('F j, Y, g:i a', strtotime($row['created_at'])) . "</small>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-notifications'>No notifications found.</p>";
        }
        ?>
    </div>

    <nav class="bottom-nav">
        <a href="../index.php" class="nav-item">
            <i class="material-icons">directions_bus</i>
            <span>Buses</span>
        </a>
        <a href="display_announcement.php" class="nav-item">
            <i class="material-icons">announcement</i>
            <span>Announcements</span>
        </a>
        <a href="display_notification.php" class="nav-item active">
            <i class="material-icons">notifications</i>
            <span>Notifications</span>
        </a>
        <a href="../back-end/admin_login.php" class="nav-item">
            <i class="material-icons">admin_panel_settings</i>
            <span>Admin</span>
        </a>
        <a href="contact.php" class="nav-item">
            <i class="material-icons">more_horiz</i>
            <span>More</span>
        </a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>