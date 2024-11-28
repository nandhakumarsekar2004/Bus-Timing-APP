<?php
session_start();
require_once 'front-end/db_connect.php';

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light';
}

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$source = '';
$destination = '';
$departure_time = '';
$buses = [];

function getLocations($conn) {
    $sql = "SELECT DISTINCT from_location FROM buses UNION SELECT DISTINCT to_location FROM buses ORDER BY from_location";
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['source'], $_POST['destination'], $_POST['departure_time'])) {
        $source = $_POST['source'];
        $destination = $_POST['destination'];
        $departure_time = $_POST['departure_time'];

        // Convert input time to 24-hour format
        $departure_time_obj = DateTime::createFromFormat('H:i', $departure_time);
        $departure_time_24h = $departure_time_obj->format('H:i:s');

        $sql = "
        SELECT b.*, bt.departure_time, bt.arrival_time
        FROM buses b
        JOIN bus_timings bt ON b.bus_id = bt.bus_id
        WHERE b.from_location = ? AND b.to_location = ? 
        AND (
            (TIME(bt.departure_time) >= ? AND TIME(bt.departure_time) < '24:00:00')
            OR
            (TIME(bt.departure_time) >= '00:00:00' AND TIME(bt.departure_time) < ?)
        )
        ORDER BY 
            CASE 
                WHEN TIME(bt.departure_time) >= ? THEN 0 
                ELSE 1 
            END,
            TIME(bt.departure_time)
        ";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param("sssss", $source, $destination, $departure_time_24h, $departure_time_24h, $departure_time_24h);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $no_results = "No buses found for the given criteria.";
        } else {
            while ($row = $result->fetch_assoc()) {
                $row['departure_time'] = date('h:i A', strtotime($row['departure_time']));
                $row['arrival_time'] = date('h:i A', strtotime($row['arrival_time']));
                $buses[] = $row;
            }
        }
    }
}

if (isset($_POST['refresh'])) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['device_id'])) {
    $_SESSION['device_id'] = uniqid('device_', true);
}
$device_id = $_SESSION['device_id'];

$sql = "SELECT MAX(notification_id) as max_id FROM notifications";
$result = $conn->query($sql);
if (!$result) {
    die("Error checking for new notifications: " . $conn->error);
}
$row = $result->fetch_assoc();
$max_notification_id = $row['max_id'];

$sql = "SELECT last_viewed_notification_id FROM device_notification_status WHERE device_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement for device notification status: " . $conn->error);
}

$stmt->bind_param("s", $device_id);

if (!$stmt->execute()) {
    die("Error executing statement for device notification status: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_viewed_notification_id = $row['last_viewed_notification_id'];
} else {
    $insert_sql = "INSERT INTO device_notification_status (device_id, last_viewed_notification_id) VALUES (?, 0)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if ($insert_stmt === false) {
        die("Error preparing insert statement for device notification status: " . $conn->error);
    }
    
    $insert_stmt->bind_param("s", $device_id);
    
    if (!$insert_stmt->execute()) {
        die("Error executing insert statement for device notification status: " . $insert_stmt->error);
    }
    
    $last_viewed_notification_id = 0;
}

$show_notification_indicator = ($max_notification_id > $last_viewed_notification_id);

$latest_notification = null;
if ($show_notification_indicator) {
    $sql = "SELECT * FROM notifications WHERE notification_id > ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $last_viewed_notification_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $latest_notification = $result->fetch_assoc();
    }
}

$_SESSION['last_page'] = 'index.php';

$announcement_sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 1";
$announcement_result = $conn->query($announcement_sql);
if (!$announcement_result) {
    die("Error fetching latest announcement: " . $conn->error);
}
$latest_announcement = $announcement_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thagadoor Bus - Search</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding-bottom: 80px;
            padding-top: 70px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-title {
            color: #007bff;
            font-weight: 500;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #fff;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #6c757d;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.3s ease;
        }
        .nav-item i {
            font-size: 24px;
            margin-bottom: 4px;
        }
        .nav-item.active {
            color: #007bff;
        }
        .nav-item:hover {
            color: #0056b3;
        }
        .notification-popup, .announcement-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 20px;
            max-width: 350px;
            width: 90%;
            z-index: 1000;
            display: none;
        }
        .notification-popup h3, .announcement-popup h3 {
            margin-bottom: 12px;
            color: #007bff;
        }
        .notification-popup .close, .announcement-popup .close {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 24px;
            color: #6c757d;
        }
        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .top-bar .app-icon {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 50%;
            overflow: hidden;
            animation: pulse 2s infinite;
        }
        .top-bar .app-name {
            font-size: 18px;
            font-weight: 500;
            margin-left: auto;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 150px;
            overflow-y: auto;
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        .autocomplete-active {
            background-color: DodgerBlue !important;
            color: #ffffff;
        }
        .autocomplete-wrapper {
            position: relative;
        }
        .announcement-popup img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border-radius: 8px;
        }
        .popup-content {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <img src="uploads/icon.png" alt="Thagadoor Bus Icon" class="app-icon">
        <span class="app-name">Thagadoor Bus</span>
    </div>

    <div class="container">
        <h2 class="mb-4">Search for Buses</h2>
        <form method="post" action="" class="mb-4">
            <div class="mb-3 autocomplete-wrapper">
                <label for="source" class="form-label">From Location:</label>
                <input type="text" class="form-control" id="source" name="source" required autocomplete="off">
                <div id="sourceAutocomplete" class="autocomplete-items"></div>
            </div>

            <div class="mb-3 autocomplete-wrapper">
                <label for="destination" class="form-label">To Location:</label>
                <input type="text" class="form-control" id="destination" name="destination" required autocomplete="off">
                <div id="destinationAutocomplete" class="autocomplete-items"></div>
            </div>

            <div class="mb-3">
                <label for="departure_time" class="form-label">Departure Time:</label>
                <input type="time" class="form-control" id="departure_time" name="departure_time" required>
            </div>

            <button type="button" class="btn btn-secondary mb-3" onclick="setCurrentTime()">Use Current Time</button>

            <button type="submit" class="btn btn-primary w-100">Search</button>
        </form>

        <form method="post" class="mb-4">
            <button type="submit" name="refresh" class="btn btn-success w-100">Refresh Page</button>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['refresh'])): ?>
            <?php if (!empty($buses)): ?>
                <h3 class="mb-3">Available Buses</h3>
                <?php foreach ($buses as $bus): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Bus Number: <?php echo htmlspecialchars($bus['bus_number']); ?></h5>
                            <p class="card-text"><strong>From:</strong> <?php echo htmlspecialchars($bus['from_location']); ?></p>
                            <p class="card-text"><strong>To:</strong> <?php echo  htmlspecialchars($bus['to_location']); ?></p>
                            <p class="card-text"><strong>Bus Name:</strong> <?php echo htmlspecialchars($bus['bus_name']); ?></p>
                            <p class="card-text"><strong>Bus Route:</strong> <?php echo htmlspecialchars($bus['bus_route']); ?></p>
                            <p class="card-text"><strong>Departure:</strong> <?php echo htmlspecialchars($bus['departure_time']); ?></p>
                            <p class="card-text"><strong>Arrival:</strong> <?php echo htmlspecialchars($bus['arrival_time']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (isset($no_results)): ?>
                <p class="text-center text-muted"><?php echo $no_results; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <nav class="bottom-nav">
        <a href="index.php" class="nav-item active">
            <i class="material-icons">directions_bus</i>
            <span>Buses</span>
        </a>
        <a href="front-end/display_announcement.php" class="nav-item">
            <i class="material-icons">announcement</i>
            <span>Announcements</span>
        </a>
        <a href="front-end/display_notification.php" class="nav-item">
            <i class="material-icons">notifications</i>
            <span>Notifications</span>
        </a>
        <a href="back-end/admin_login.php" class="nav-item">
            <i class="material-icons">admin_panel_settings</i>
            <span>Admin</span>
        </a>
        <a href="front-end/contact.php" class="nav-item">
            <i class="material-icons">contact_support</i>
            <span>Contact</span>
        </a>
    </nav>

    <?php if ($latest_notification): ?>
    <div id="notificationPopup" class="notification-popup">
        <span class="close" onclick="dismissNotification()">&times;</span>
        <div class="popup-content" onclick="redirectToNotifications()">
            <h3>New Notification</h3>
            <h4><?php echo htmlspecialchars($latest_notification['title']); ?></h4>
            <p><?php echo htmlspecialchars($latest_notification['message']); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($latest_announcement): ?>
    <div id="announcementPopup" class="announcement-popup">
        <span class="close" onclick="closeAnnouncement()">&times;</span>
        <div class="popup-content" onclick="redirectToAnnouncements()">
            <h3>Announcement</h3>
            <h4><?php echo htmlspecialchars($latest_announcement['title']); ?></h4>
            <p><?php echo htmlspecialchars($latest_announcement['description']); ?></p>
            <?php if (!empty($latest_announcement['file_path'])): ?>
                <img src="<?php echo htmlspecialchars($latest_announcement['file_path']); ?>" alt="Announcement Image">
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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

            const notificationPopup = document.getElementById('notificationPopup');
            if (notificationPopup) {
                notificationPopup.style.display = 'block';
            }

            const announcementPopup = document.getElementById('announcementPopup');
            if (announcementPopup) {
                announcementPopup.style.display = 'block';
            }
        });

        function closeAnnouncement() {
            document.getElementById('announcementPopup').style.display = 'none';
        }

        function dismissNotification() {
            const notificationPopup = document.getElementById('notificationPopup');
            if (notificationPopup) {
                notificationPopup.style.display = 'none';
            }
        }

        function redirectToNotifications() {
            window.location.href = 'front-end/display_notification.php';
        }

        function redirectToAnnouncements() {
            window.location.href = 'front-end/display_announcement.php';
        }

        function setCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('departure_time').value = `${hours}:${minutes}`;
        }

        function autocomplete(inp, arr) {
            var currentFocus;
            inp.addEventListener("input", function(e) {
                var a, b, i, val = this.value;
                closeAllLists();
                if (!val) { return false;}
                currentFocus = -1;
                a = document.getElementById(this.id + "Autocomplete");
                a.innerHTML = '';
                a.style.display = 'block';
                for (i = 0; i < arr.length; i++) {
                    if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                        b = document.createElement("DIV");
                        b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                        b.innerHTML += arr[i].substr(val.length);
                        b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                        b.addEventListener("click", function(e) {
                            inp.value = this.getElementsByTagName("input")[0].value;
                            closeAllLists();
                        });
                        a.appendChild(b);
                    }
                }
            });
            inp.addEventListener("keydown", function(e) {
                var x = document.getElementById(this.id + "Autocomplete");
                if (x) x = x.getElementsByTagName("div");
                if (e.keyCode == 40) {
                    currentFocus++;
                    addActive(x);
                } else if (e.keyCode == 38) {
                    currentFocus--;
                    addActive(x);
                } else if (e.keyCode == 13) {
                    e.preventDefault();
                    if (currentFocus > -1) {
                        if (x) x[currentFocus].click();
                    }
                }
            });
            function addActive(x) {
                if (!x) return false;
                removeActive(x);
                if (currentFocus >= x.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (x.length - 1);
                x[currentFocus].classList.add("autocomplete-active");
            }
            function removeActive(x) {
                for (var i = 0; i < x.length; i++) {
                    x[i].classList.remove("autocomplete-active");
                }
            }
            function closeAllLists(elmnt) {
                var x = document.getElementsByClassName("autocomplete-items");
                for (var i = 0; i < x.length; i++) {
                    if (elmnt != x[i] && elmnt != inp) {
                        x[i].style.display = 'none';
                    }
                }
            }
            document.addEventListener("click", function (e) {
                closeAllLists(e.target);
            });
        }

        var locations = <?php echo json_encode($locations); ?>;
        autocomplete(document.getElementById("source"), locations);
        autocomplete(document.getElementById("destination"), locations);
    </script>
</body>
</html>