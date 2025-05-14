<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dict";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to handle database errors
function handle_db_error($error_message) {
    global $conn;
    error_log("Database Error: " . $error_message . " - " . $conn->error);
    return "An error occurred. Please try again later.";
}
?>