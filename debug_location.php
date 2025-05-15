<?php
// Temporary debug file to check database content
require_once 'includes/db_connect.php';
require_once 'includes/location_data.php';

echo "<h1>Database Debug Information</h1>";

// Check regions
$regions = get_all_regions();
echo "<h2>Regions (" . count($regions) . ")</h2>";
echo "<pre>";
print_r($regions);
echo "</pre>";

// Check provinces if a region exists
if (!empty($regions)) {
    $first_region_id = $regions[0]['id'];
    $provinces = get_provinces_by_region($first_region_id);
    echo "<h2>Provinces for Region ID $first_region_id (" . count($provinces) . ")</h2>";
    echo "<pre>";
    print_r($provinces);
    echo "</pre>";
    
    // Check districts if a province exists
    if (!empty($provinces)) {
        $first_province_id = $provinces[0]['id'];
        $districts = get_districts_by_province($first_province_id);
        echo "<h2>Districts for Province ID $first_province_id (" . count($districts) . ")</h2>";
        echo "<pre>";
        print_r($districts);
        echo "</pre>";
        
        // Check municipalities
        $municipalities = get_municipalities_by_location($first_province_id);
        echo "<h2>Municipalities for Province ID $first_province_id (" . count($municipalities) . ")</h2>";
        echo "<pre>";
        print_r($municipalities);
        echo "</pre>";
    }
}

// Display raw database tables
echo "<h2>Raw Database Tables</h2>";

$tables = ['regions', 'provinces', 'districts', 'municipalities'];
foreach ($tables as $table) {
    $query = "SELECT * FROM $table LIMIT 10";
    $result = $conn->query($query);
    
    echo "<h3>$table Table</h3>";
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        
        // Table header
        $row = $result->fetch_assoc();
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        // Reset pointer
        $result->data_seek(0);
        
        // Table data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No data in $table table or table does not exist</p>";
    }
}
?>
