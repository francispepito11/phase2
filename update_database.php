<?php
// filepath: c:\xampp\htdocs\phase2-1\update_database.php
// Script to update the tech_support_requests table by removing email and phone columns

// Include database connection
require_once 'includes/db_connect.php';

// Check if connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Update Script</h2>";

// Check if the table exists
$table_check = $conn->query("SHOW TABLES LIKE 'tech_support_requests'");
if ($table_check->num_rows == 0) {
    echo "<p>Table 'tech_support_requests' doesn't exist. No update needed.</p>";
} else {
    // Check if email column exists
    $email_check = $conn->query("SHOW COLUMNS FROM `tech_support_requests` LIKE 'email'");
    if ($email_check->num_rows > 0) {
        $sql = "ALTER TABLE `tech_support_requests` DROP COLUMN `email`";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Email column removed successfully.</p>";
        } else {
            echo "<p>Error removing email column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Email column doesn't exist. No update needed.</p>";
    }
    
    // Check if phone column exists
    $phone_check = $conn->query("SHOW COLUMNS FROM `tech_support_requests` LIKE 'phone'");
    if ($phone_check->num_rows > 0) {
        $sql = "ALTER TABLE `tech_support_requests` DROP COLUMN `phone`";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Phone column removed successfully.</p>";
        } else {
            echo "<p>Error removing phone column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Phone column doesn't exist. No update needed.</p>";
    }
}

echo "<p>Database update process completed.</p>";
echo "<p><a href='tech-support.php'>Return to Tech Support page</a></p>";

// Close connection
$conn->close();
?>
