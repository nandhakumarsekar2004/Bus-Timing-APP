<?php
session_start();
require_once 'db_connect.php';
$query = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
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
            padding-bottom: 70px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #1976D2;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }
        .announcement {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .announcement:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .announcement h3 {
            color: #1976D2;
            margin-bottom: 10px;
            font-size: 20px;
            font-weight: 500;
        }
        .announcement p {
            color: #555;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .announcement img, .announcement video {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 4px;
            margin-top: 10px;
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
            .announcement {
                padding: 15px;
            }
            .announcement h3 {
                font-size: 18px;
            }
            .announcement p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Latest Announcements</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="announcement">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    <?php if (!empty($row['file_path'])): ?>
                        <?php 
                        $file_extension = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                        if (in_array($file_extension, ['mp4', 'mov', 'avi'])): ?>
                            <video controls>
                                <source src="<?php echo htmlspecialchars($row['file_path']); ?>" type="video/<?php echo $file_extension; ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($row['file_path']); ?>" data-lightbox="announcement-<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>">
                                <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Announcement Image">
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: #757575;">No announcements available.</p>
        <?php endif; ?>
    </div>
    
    <nav class="bottom-nav">
        <a href="../index.php" class="nav-item">
            <i class="material-icons">directions_bus</i>
            <span>Buses</span>
        </a>
        <a href="display_announcement.php" class="nav-item active">
            <i class="material-icons">announcement</i>
            <span>Announcements</span>
        </a>
        <a href="display_notification.php" class="nav-item">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
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