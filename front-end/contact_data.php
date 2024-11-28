<?php
// Read the CSV file
$file = 'contact_data.csv';
$data = array();

if (file_exists($file)) {
    $handle = fopen($file, 'r');
    while (($row = fgetcsv($handle)) !== false) {
        $data[] = $row;
    }
    fclose($handle);
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $index = $_POST['delete'];
    if (isset($data[$index])) {
        unset($data[$index]);
        $data = array_values($data); // Re-index the array
        
        // Write the updated data back to the CSV file
        $handle = fopen($file, 'w');
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<?php include '../back-end/admin-navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submissions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fadeIn { animation: fadeIn 0.5s ease-out; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-400 to-purple-500 p-4">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6 animate-fadeIn mb-16">
        <h1 class="text-2xl font-bold text-center mb-6">Contact Form Submissions</h1>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Message</th>
                        <th scope="col" class="px-6 py-3">Submitted At</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $index => $row): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row[0]); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row[1]); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row[2]); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($row[3]); ?></td>
                            <td class="px-6 py-4">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                    <input type="hidden" name="delete" value="<?php echo $index; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <nav class="fixed bottom-0 left-0 right-0 bg-white shadow-lg">
        <ul class="flex justify-around py-2">
            <?php
            $navItems = [
                ['icon' => 'directions_bus', 'label' => 'Buses', 'href' => '../index.php'],
                ['icon' => 'announcement', 'label' => 'Announcements', 'href' => 'display_announcement.php'],
                ['icon' => 'notifications', 'label' => 'Notifications', 'href' => 'display_notification.php'],
                ['icon' => 'admin_panel_settings', 'label' => 'Admin', 'href' => '../back-end/admin_login.php'],
                ['icon' => 'more_horiz', 'label' => 'More', 'href' => 'contact.php']
            ];
            foreach ($navItems as $item):
            ?>
                <li>
                    <a href="<?php echo htmlspecialchars($item['href']); ?>" class="flex flex-col items-center text-gray-600 hover:text-indigo-600 transition duration-300">
                        <i class="material-icons"><?php echo htmlspecialchars($item['icon']); ?></i>
                        <span class="text-xs"><?php echo htmlspecialchars($item['label']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <script>
        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('nav a');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('text-indigo-600');
                }
            });
        });
    </script>
</body>
</html>