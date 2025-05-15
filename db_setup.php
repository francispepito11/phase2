<?php
// Database setup and population script
// This script will create the necessary tables for location data if they don't exist
// and populate them with sample data

// Include database connection
require_once 'includes/db_connect.php';

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Array to store results
$results = [];

// Function to log results
function logResult($operation, $success, $message = '') {
    global $results;
    $results[] = [
        'operation' => $operation,
        'success' => $success,
        'message' => $message
    ];
}

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Create regions table if it doesn't exist
try {
    if (!tableExists($conn, 'regions')) {
        $sql = "CREATE TABLE regions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            region_code VARCHAR(50) NOT NULL,
            region_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($sql)) {
            logResult('Create regions table', true, 'Table created successfully');
        } else {
            logResult('Create regions table', false, 'Error creating table: ' . $conn->error);
        }
    } else {
        logResult('Check regions table', true, 'Table already exists');
    }
} catch (Exception $e) {
    logResult('Create regions table', false, 'Exception: ' . $e->getMessage());
}

// Create provinces table if it doesn't exist
try {
    if (!tableExists($conn, 'provinces')) {
        $sql = "CREATE TABLE provinces (
            id INT PRIMARY KEY AUTO_INCREMENT,
            region_id INT NOT NULL,
            province_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
        )";
        
        if ($conn->query($sql)) {
            logResult('Create provinces table', true, 'Table created successfully');
        } else {
            logResult('Create provinces table', false, 'Error creating table: ' . $conn->error);
        }
    } else {
        logResult('Check provinces table', true, 'Table already exists');
    }
} catch (Exception $e) {
    logResult('Create provinces table', false, 'Exception: ' . $e->getMessage());
}

// Create districts table if it doesn't exist
try {
    if (!tableExists($conn, 'districts')) {
        $sql = "CREATE TABLE districts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            province_id INT NOT NULL,
            district_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE
        )";
        
        if ($conn->query($sql)) {
            logResult('Create districts table', true, 'Table created successfully');
        } else {
            logResult('Create districts table', false, 'Error creating table: ' . $conn->error);
        }
    } else {
        logResult('Check districts table', true, 'Table already exists');
    }
} catch (Exception $e) {
    logResult('Create districts table', false, 'Exception: ' . $e->getMessage());
}

// Create municipalities table if it doesn't exist
try {
    if (!tableExists($conn, 'municipalities')) {
        $sql = "CREATE TABLE municipalities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            province_id INT NOT NULL,
            district_id INT,
            municipality_name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
            FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL
        )";
        
        if ($conn->query($sql)) {
            logResult('Create municipalities table', true, 'Table created successfully');
        } else {
            logResult('Create municipalities table', false, 'Error creating table: ' . $conn->error);
        }
    } else {
        logResult('Check municipalities table', true, 'Table already exists');
    }
} catch (Exception $e) {
    logResult('Create municipalities table', false, 'Exception: ' . $e->getMessage());
}

// Insert regions if the table is empty
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM regions");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
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
        
        // Insert regions
        $stmt = $conn->prepare("INSERT INTO regions (id, region_code, region_name) VALUES (?, ?, ?)");
        $insertCount = 0;
        
        foreach ($regions as $region) {
            $stmt->bind_param("iss", $region['id'], $region['region_code'], $region['region_name']);
            
            if ($stmt->execute()) {
                $insertCount++;
            }
        }
        
        logResult('Insert regions', true, "Inserted $insertCount regions");
        
        // Insert some sample provinces
        if ($insertCount > 0) {
            $provinces = [
                // NCR
                ['region_id' => 1, 'province_name' => 'Metro Manila'],
                
                // CAR
                ['region_id' => 2, 'province_name' => 'Benguet'],
                ['region_id' => 2, 'province_name' => 'Ifugao'],
                
                // Region I
                ['region_id' => 3, 'province_name' => 'Ilocos Norte'],
                ['region_id' => 3, 'province_name' => 'Ilocos Sur'],
                ['region_id' => 3, 'province_name' => 'La Union'],
                
                // CALABARZON
                ['region_id' => 6, 'province_name' => 'Batangas'],
                ['region_id' => 6, 'province_name' => 'Cavite'],
                ['region_id' => 6, 'province_name' => 'Laguna'],
                ['region_id' => 6, 'province_name' => 'Quezon'],
                ['region_id' => 6, 'province_name' => 'Rizal']
            ];
            
            // Insert provinces
            $stmt = $conn->prepare("INSERT INTO provinces (region_id, province_name) VALUES (?, ?)");
            $insertCount = 0;
            
            foreach ($provinces as $province) {
                $stmt->bind_param("is", $province['region_id'], $province['province_name']);
                
                if ($stmt->execute()) {
                    $insertCount++;
                }
            }
            
            logResult('Insert provinces', true, "Inserted $insertCount provinces");
            
            // Add some districts to Metro Manila
            $stmt = $conn->prepare("SELECT id FROM provinces WHERE province_name = 'Metro Manila' LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $metroManilaId = $row['id'];
                
                $districts = [
                    ['province_id' => $metroManilaId, 'district_name' => 'District 1'],
                    ['province_id' => $metroManilaId, 'district_name' => 'District 2'],
                    ['province_id' => $metroManilaId, 'district_name' => 'District 3'],
                    ['province_id' => $metroManilaId, 'district_name' => 'District 4']
                ];
                
                // Insert districts
                $stmt = $conn->prepare("INSERT INTO districts (province_id, district_name) VALUES (?, ?)");
                $insertCount = 0;
                
                foreach ($districts as $district) {
                    $stmt->bind_param("is", $district['province_id'], $district['district_name']);
                    
                    if ($stmt->execute()) {
                        $insertCount++;
                    }
                }
                
                logResult('Insert districts', true, "Inserted $insertCount districts");
                
                // Add municipalities to Metro Manila
                $municipalities = [
                    ['province_id' => $metroManilaId, 'district_id' => null, 'municipality_name' => 'Manila'],
                    ['province_id' => $metroManilaId, 'district_id' => null, 'municipality_name' => 'Quezon City'],
                    ['province_id' => $metroManilaId, 'district_id' => null, 'municipality_name' => 'Makati'],
                    ['province_id' => $metroManilaId, 'district_id' => null, 'municipality_name' => 'Taguig'],
                    ['province_id' => $metroManilaId, 'district_id' => null, 'municipality_name' => 'Pasig']
                ];
                
                // Get district IDs
                $stmt = $conn->prepare("SELECT id FROM districts WHERE province_id = ? ORDER BY id");
                $stmt->bind_param("i", $metroManilaId);
                $stmt->execute();
                $result = $stmt->get_result();
                $districtIds = [];
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $districtIds[] = $row['id'];
                    }
                }
                
                // Update municipalities with district IDs
                if (count($districtIds) > 0) {
                    $municipalities[0]['district_id'] = $districtIds[0]; // Manila to District 1
                    $municipalities[1]['district_id'] = $districtIds[1]; // Quezon City to District 2
                    $municipalities[2]['district_id'] = $districtIds[2]; // Makati to District 3
                    $municipalities[3]['district_id'] = $districtIds[3]; // Taguig to District 4
                    $municipalities[4]['district_id'] = $districtIds[0]; // Pasig to District 1
                }
                
                // Insert municipalities
                $stmt = $conn->prepare("INSERT INTO municipalities (province_id, district_id, municipality_name) VALUES (?, ?, ?)");
                $insertCount = 0;
                
                foreach ($municipalities as $municipality) {
                    $stmt->bind_param("iis", $municipality['province_id'], $municipality['district_id'], $municipality['municipality_name']);
                    
                    if ($stmt->execute()) {
                        $insertCount++;
                    }
                }
                
                logResult('Insert municipalities', true, "Inserted $insertCount municipalities");
            }
        }
    } else {
        $count = $row['count'];
        logResult('Check regions', true, "Regions table already has $count records");
    }
} catch (Exception $e) {
    logResult('Insert data', false, 'Exception: ' . $e->getMessage());
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Database Setup Results</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Operation Results</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left">Operation</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left">Status</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo $result['operation']; ?></td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?php if ($result['success']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Success
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Error
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo $result['message']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="flex space-x-4">
            <a href="tech-support.php" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                Go to Tech Support Form
            </a>
            <a href="test_location_api.php" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded hover:bg-gray-700">
                Test API Endpoints
            </a>
            <a href="debug_location.php" class="px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700">
                View Database Debug
            </a>
        </div>
    </div>
</body>
</html>
