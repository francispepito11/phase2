<?php
// filepath: c:\xampp\htdocs\phase2-1\update_name_fields.php
// Script to update the tech_support_requests table by adding first_name, last_name, and middle_initial columns

// Include database connection
require_once 'includes/db_connect.php';

// Check if connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Update Script - Add Name Fields</h2>";

// Check if the table exists
$table_check = $conn->query("SHOW TABLES LIKE 'tech_support_requests'");
if ($table_check->num_rows == 0) {
    echo "<p>Table 'tech_support_requests' doesn't exist. No update needed.</p>";
} else {
    echo "<p>Table 'tech_support_requests' exists. Proceeding with updates...</p>";
    
    // Check if first_name column exists
    $first_name_check = $conn->query("SHOW COLUMNS FROM `tech_support_requests` LIKE 'first_name'");
    if ($first_name_check->num_rows == 0) {
        $sql = "ALTER TABLE `tech_support_requests` ADD COLUMN `first_name` varchar(50) NOT NULL AFTER `client_name`";
        if ($conn->query($sql) === TRUE) {
            echo "<p>First name column added successfully.</p>";
        } else {
            echo "<p>Error adding first name column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>First name column already exists.</p>";
    }
    
    // Check if last_name column exists
    $last_name_check = $conn->query("SHOW COLUMNS FROM `tech_support_requests` LIKE 'last_name'");
    if ($last_name_check->num_rows == 0) {
        $sql = "ALTER TABLE `tech_support_requests` ADD COLUMN `last_name` varchar(50) NOT NULL AFTER `first_name`";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Last name column added successfully.</p>";
        } else {
            echo "<p>Error adding last name column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Last name column already exists.</p>";
    }
    
    // Check if middle_initial column exists
    $middle_initial_check = $conn->query("SHOW COLUMNS FROM `tech_support_requests` LIKE 'middle_initial'");
    if ($middle_initial_check->num_rows == 0) {
        $sql = "ALTER TABLE `tech_support_requests` ADD COLUMN `middle_initial` varchar(1) DEFAULT NULL AFTER `last_name`";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Middle initial column added successfully.</p>";
        } else {
            echo "<p>Error adding middle initial column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Middle initial column already exists.</p>";
    }
    
    // Populate new columns based on existing client_name values (if there are any records)
    $result = $conn->query("SELECT id, client_name FROM tech_support_requests WHERE first_name = '' OR first_name IS NULL");
    if ($result->num_rows > 0) {
        echo "<p>Updating existing records with split name values...</p>";
        
        $updated = 0;
        $errors = 0;
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $full_name = trim($row['client_name']);
            
            // Basic name parsing - this is a simplified approach
            $name_parts = explode(' ', $full_name);
            
            if (count($name_parts) >= 2) {
                $first_name = $name_parts[0];
                $last_name = end($name_parts);
                
                // Check for middle initial (assumes format: "First M. Last")
                $middle_initial = '';
                if (count($name_parts) > 2) {
                    // Look for a part that might be a middle initial (typically has a period)
                    foreach ($name_parts as $i => $part) {
                        if ($i > 0 && $i < count($name_parts) - 1) {
                            if (strlen($part) <= 2 || strpos($part, '.') !== false) {
                                $middle_initial = str_replace('.', '', $part);
                                break;
                            }
                        }
                    }
                }
                
                // Update the record
                $update_sql = "UPDATE tech_support_requests SET 
                               first_name = ?, 
                               last_name = ?, 
                               middle_initial = ? 
                               WHERE id = ?";
                               
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("sssi", $first_name, $last_name, $middle_initial, $id);
                
                if ($stmt->execute()) {
                    $updated++;
                } else {
                    $errors++;
                    echo "<p>Error updating record ID {$id}: " . $stmt->error . "</p>";
                }
                
                $stmt->close();
            }
        }
        
        echo "<p>Records updated: $updated. Errors: $errors.</p>";
    } else {
        echo "<p>No existing records need to be updated.</p>";
    }
}

echo "<p>Name fields update process completed.</p>";
echo "<p><a href='tech-support.php'>Return to Tech Support page</a></p>";

// Close connection
$conn->close();
?>
