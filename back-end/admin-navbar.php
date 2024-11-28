<?php
$navItems = [
    ["href" => "upload_bus.php", "icon" => "truck", "label" => "Add Bus"],
    ["href" => "upload_announcement.php", "icon" => "megaphone", "label" => "Announcements"],
    ["href" => "upload_notification.php", "icon" => "bell", "label" => "Notifications"],
    ["href" => "admin_dashboard.php", "icon" => "layout-dashboard", "label" => "Admin Dashboard"],
    ["href" => "contact_data.php", "icon" => "phone", "label" => "Contact"],
    ["href" => "recent_items.php", "icon" => "clock", "label" => "Recently Added Items"],
    ["href" => "../../index.php", "icon" => "log-out", "label" => "Logout"]
];

function renderNavItem($item) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $activeClass = ($currentPage == $item['href']) ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-600 hover:text-white';
    return "
    <a href='{$item['href']}' class='{$activeClass} flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 ease-in-out'>
        <i data-feather='{$item['icon']}' class='h-5 w-5 mr-2'></i>
        <span>{$item['label']}</span>
    </a>
    ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            padding-top: 4rem;
        }
        @media (max-width: 640px) {
            body {
                padding-top: 3rem;
            }
        }
        .mobile-menu-open {
            overflow: hidden;
        }
        #admin-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        #page-content {
            padding-top: 1rem;
        }
        #mobile-menu {
            position: fixed;
            top: 4rem;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
        }
        #mobile-menu.show {
            display: block;
        }
        #mobile-menu .menu-content {
            background-color: #2563eb;
            height: 100%;
            overflow-y: auto;
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav id="admin-navbar" class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="relative flex items-center justify-between h-16">
                <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                    <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <i data-feather="menu" class="h-6 w-6"></i>
                    </button>
                </div>
                <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-white font-bold text-lg">Admin Dashboard</span>
                    </div>
                    <div class="hidden sm:block sm:ml-6">
                        <div class="flex space-x-4">
                            <?php foreach ($navItems as $item) echo renderNavItem($item); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:hidden" id="mobile-menu">
            <div class="menu-content">
                <?php foreach ($navItems as $item) echo renderNavItem($item); ?>
            </div>
        </div>
    </nav>

    <div id="page-content" class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page content will be inserted here -->
    </div>

    <script>
        // Initialize Feather icons
        feather.replace();

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const body = document.body;

        mobileMenuButton.addEventListener('click', () => {
            const expanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', String(!expanded));
            mobileMenu.classList.toggle('show');
            body.classList.toggle('mobile-menu-open');
            
            const icon = mobileMenuButton.querySelector('i');
            if (expanded) {
                icon.setAttribute('data-feather', 'menu');
            } else {
                icon.setAttribute('data-feather', 'x');
            }
            feather.replace();
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (event) => {
            const isClickInside = mobileMenuButton.contains(event.target) || mobileMenu.contains(event.target);
            if (!isClickInside && mobileMenu.classList.contains('show')) {
                mobileMenuButton.setAttribute('aria-expanded', 'false');
                mobileMenu.classList.remove('show');
                body.classList.remove('mobile-menu-open');
                const icon = mobileMenuButton.querySelector('i');
                icon.setAttribute('data-feather', 'menu');
                feather.replace();
            }
        });

        // Close mobile menu when window is resized to larger screen
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 640 && mobileMenu.classList.contains('show')) {
                mobileMenuButton.setAttribute('aria-expanded', 'false');
                mobileMenu.classList.remove('show');
                body.classList.remove('mobile-menu-open');
                const icon = mobileMenuButton.querySelector('i');
                icon.setAttribute('data-feather', 'menu');
                feather.replace();
            }
        });
    </script>
</body>
</html>