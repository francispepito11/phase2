<?php
// Script to populate regions table
require_once 'includes/db_connect.php';

// Check if regions table already has data
$check_query = "SELECT COUNT(*) as count FROM regions";
$result = $conn->query($check_query);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "<h2>Populating regions table...</h2>";
    
    // Define regions
    $regions = [
        ['id' => 1, 'region_code' => 'NCR', 'region_name' => 'National Capital Region (NCR)'],
        ['id' => 2, 'region_code' => 'CAR', 'region_name' => 'Cordillera Administrative Region (CAR)'],
        ['id' => 3, 'region_code' => 'Region I', 'region_name' => 'Region I (Ilocos Region)'],
        ['id' => 4, 'region_code' => 'Region II', 'region_name' => 'Region II (Cagayan Valley)'],
        ['id' => 5, 'region_code' => 'Region III', 'region_name' => 'Region III (Central Luzon)'],
        ['id' => 6, 'region_code' => 'Region IV-A', 'region_name' => 'Region IV-A (CALABARZON)'],
        ['id' => 7, 'region_code' => 'Region IV-B', 'region_name' => 'Region IV-B (MIMAROPA)'],
        ['id' => 8, 'region_code' => 'Region V', 'region_name' => 'Region V (Bicol Region)'],
        ['id' => 9, 'region_code' => 'Region VI', 'region_name' => 'Region VI (Western Visayas)'],
        ['id' => 10, 'region_code' => 'Region VII', 'region_name' => 'Region VII (Central Visayas)'],
        ['id' => 11, 'region_code' => 'Region VIII', 'region_name' => 'Region VIII (Eastern Visayas)'],
        ['id' => 12, 'region_code' => 'Region IX', 'region_name' => 'Region IX (Zamboanga Peninsula)'],
        ['id' => 13, 'region_code' => 'Region X', 'region_name' => 'Region X (Northern Mindanao)'],
        ['id' => 14, 'region_code' => 'Region XI', 'region_name' => 'Region XI (Davao Region)'],
        ['id' => 15, 'region_code' => 'Region XII', 'region_name' => 'Region XII (SOCCSKSARGEN)'],
        ['id' => 16, 'region_code' => 'Region XIII', 'region_name' => 'Region XIII (Caraga)'],
        ['id' => 17, 'region_code' => 'BARMM', 'region_name' => 'Bangsamoro Autonomous Region in Muslim Mindanao']
    ];
    
    // Prepare the insert statement
    $insert_query = "INSERT INTO regions (id, region_code, region_name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insert_query);
    
    if ($stmt) {
        $stmt->bind_param("iss", $id, $region_code, $region_name);
        
        // Insert each region
        foreach ($regions as $region) {
            $id = $region['id'];
            $region_code = $region['region_code'];
            $region_name = $region['region_name'];
            
            if ($stmt->execute()) {
                echo "<p>Added region: {$region_name}</p>";
            } else {
                echo "<p>Error adding region {$region_name}: {$stmt->error}</p>";
            }
        }
        
        $stmt->close();
    } else {
        echo "<p>Error preparing SQL statement: {$conn->error}</p>";
    }
    
    echo "<h2>Region population complete!</h2>";
} else {
    echo "<h2>Regions table already has data. No action needed.</h2>";
}

echo "<p><a href='tech-support.php'>Return to Technical Support Form</a></p>";
?>
