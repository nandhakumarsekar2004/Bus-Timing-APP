<?php
session_start();
require_once 'db_connect.php';

$error_message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and escape inputs
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Hash the password using MD5

    // Prepare SQL query to check if the user exists
    $sql = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user is found, verify the password
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Compare hashed password with the one in the database
        if ($password == $row['password']) {
            // Password is correct, start session and redirect to admin dashboard
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin'] = $row['username'];

            // Check if headers are already sent
            if (!headers_sent()) {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                echo "Headers already sent, cannot redirect.";
            }
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding-bottom: 70px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #1976D2;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 500;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #1976D2;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #1565C0;
        }
        .password-container {
            position: relative;
        }
        .password-container i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #757575;
        }
        .error-message {
            color: #d32f2f;
            text-align: center;
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
            transition: color 0.3s;
        }
        .nav-item i {
            font-size: 24px;
            margin-bottom: 2px;
        }
        .nav-item.active {
            color: #1976D2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <i class="material-icons" id="togglePassword">visibility</i>
            </div>

            <input type="submit" value="Login">
        </form>
        <?php
        if (!empty($error_message)) {
            echo "<p class='error-message'>" . htmlspecialchars($error_message) . "</p>";
        }
        ?>
    </div>

    <nav class="bottom-nav">
        <a href="../index.php" class="nav-item">
            <i class="material-icons">directions_bus</i>
            <span>Buses</span>
        </a>
        <a href="../front-end/display_announcement.php" class="nav-item">
            <i class="material-icons">announcement</i>
            <span>Announcements</span>
        </a>
        <a href="../front-end/display_notification.php" class="nav-item">
            <i class="material-icons">notifications</i>
            <span>Notifications</span>
        </a>
        <a href="../back-end/admin_login.php" class="nav-item active">
            <i class="material-icons">admin_panel_settings</i>
            <span>Admin</span>
        </a>
        <a href="../front-end/contact.php" class="nav-item">
            <i class="material-icons">more_horiz</i>
            <span>More</span>
        </a>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const passwordField = document.querySelector('#password');
            let timeout;

            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.textContent = type === 'password' ? 'visibility' : 'visibility_off';

                clearTimeout(timeout);
                if (type === 'text') {
                    timeout = setTimeout(() => {
                        passwordField.setAttribute('type', 'password');
                        togglePassword.textContent = 'visibility';
                    }, 5000); // Hide password after 5 seconds
                }
            });
        });
    </script>
</body>
</html>