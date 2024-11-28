<?php
$servername = "localhost";
$username = "root"; // Adjust as necessary
$password = "";
$dbname = "bus_db"; // The name of your bus timing database

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
