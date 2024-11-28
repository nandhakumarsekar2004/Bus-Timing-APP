<?php
session_start();
$message = '';
$submitted_data = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message_content = $_POST['message'] ?? '';
    
    // Store the submitted data in a CSV file
    $data = array($name, $email, $message_content, date('Y-m-d H:i:s'));
    $file = fopen('contact_data.csv', 'a');
    fputcsv($file, $data);
    fclose($file);
    
    $message = "Thank you for your message, $name! We'll get back to you soon.";
    $submitted_data = $data;
    $_SESSION['form_submitted'] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Thagadoor Bus</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --background-color: #ecf0f1;
            --text-color: #34495e;
            --light-text-color: #7f8c8d;
            --border-color: #bdc3c7;
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
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 80%);
            transform: rotate(30deg);
        }
        .header h1 {
            font-size: 28px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.15);
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
            outline: none;
        }
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            display: block;
            width: 100%;
        }
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }
        .alert-success {
            background-color: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
        }
        .contact-info {
            margin-top: 40px;
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        }
        .contact-info h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
            text-align: center;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .contact-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        .contact-item .material-icons {
            color: var(--accent-color);
            margin-right: 15px;
            font-size: 24px;
        }
        .contact-item a {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .contact-item a:hover {
            color: var(--primary-color);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--light-text-color);
            font-size: 12px;
            transition: color 0.3s;
        }
        .nav-item .material-icons {
            font-size: 24px;
            margin-bottom: 4px;
        }
        .nav-item.active, .nav-item:hover {
            color: var(--primary-color);
        }
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            .header h1 {
                font-size: 24px;
            }
            .card, .contact-info {
                padding: 20px;
            }
            .form-control, .btn {
                font-size: 14px;
            }
        }
        .thank-you-box {
            background-color: #d4edda;
            border: 2px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Contact Us</h1>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['form_submitted']) && $_SESSION['form_submitted']): ?>
            <div class="thank-you-box">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php unset($_SESSION['form_submitted']); ?>
        <?php endif; ?>

        <div class="card">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="contactForm">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="4" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>

        <div class="contact-info">
            <h3>Contact Information</h3>
            <div class="contact-item">
                <span class="material-icons">person</span>
                <span>Nandhakumar Sekar</span>
            </div>
            <div class="contact-item">
                <span class="material-icons">location_on</span>
                <span>Dharmapuri</span>
            </div>
            <div class="contact-item">
                <span class="material-icons">email</span>
                <a href="mailto:nandhusekar2602@gmail.com">nandhusekar2602@gmail.com</a>
            </div>
            <div class="contact-item">
                <span class="material-icons">phone</span>
                <a href="tel:+919786220194">09786220194</a>
            </div>
        </div>
    </div>

    <nav class="bottom-nav">
        <?php
        $navItems = [
            ['icon' => 'directions_bus', 'label' => 'Buses', 'href' => '../index.php'],
            ['icon' => 'announcement', 'label' => 'Announcements', 'href' => 'display_announcement.php'],
            ['icon' => 'notifications', 'label' => 'Notifications', 'href' => 'display_notification.php'],
            ['icon' => 'admin_panel_settings', 'label' => 'Admin', 'href' => '../back-end/admin_login.php'],
            ['icon' => 'contact_support', 'label' => 'Contact', 'href' => 'contact.php']
        ];
        foreach ($navItems as $item):
        ?>
            <a href="<?php echo htmlspecialchars($item['href']); ?>" class="nav-item <?php echo ($_SERVER['PHP_SELF'] == $item['href']) ? 'active' : ''; ?>">
                <span class="material-icons"><?php echo htmlspecialchars($item['icon']); ?></span>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                if (item.getAttribute('href').includes(currentPage)) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>