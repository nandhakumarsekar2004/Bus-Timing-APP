<?php
// Include the database connection
include 'db_connect.php';

// Handle deletion of a notification
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Check if the ID is valid
    if (is_numeric($delete_id)) {
        $delete_sql = "DELETE FROM notifications WHERE id = '$delete_id'";

        if (mysqli_query($conn, $delete_sql)) {
            echo "<p>Notification deleted successfully.</p>";
        } else {
            echo "<p>Error deleting notification: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p>Invalid notification ID.</p>";
    }
}

// Fetch all notifications
$sql = "SELECT * FROM notifications";
$result = mysqli_query($conn, $sql);
?>
<?php include 'admin-navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notifications</title>
</head>
<body>
    <h2>Manage Notifications</h2>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Message</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['message']); ?></td>
            <td>
                <a href="manage_notification.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this notification?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
