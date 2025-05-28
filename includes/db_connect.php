<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dict";

// Create PDO connection
function get_db_connection() {
    global $servername, $username, $password, $dbname;
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        throw $e;
    }
}

// Create mysqli connection for backward compatibility
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