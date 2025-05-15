<?php
// filepath: c:\xampp\htdocs\phase2-1\includes\location_data.php
// Include database connection
require_once 'db_connect.php';

/**
 * Get all regions from the database
 * 
 * @return array Array of regions with id, region_code, and region_name
 */
function get_all_regions() {
    global $conn;
    $regions = [];
    
    try {
        $query = "SELECT id, region_code, region_name FROM regions ORDER BY region_name";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $regions[] = $row;
            }
        } else {
            // If no regions found, provide default regions based on the Philippines
            $default_regions = [
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
            
            // Optionally insert these into the database
            foreach ($default_regions as $region) {
                $insert_query = "INSERT IGNORE INTO regions (id, region_code, region_name, created_at, updated_at) 
                                VALUES (?, ?, ?, NOW(), NOW())";
                $stmt = $conn->prepare($insert_query);
                if ($stmt) {
                    $stmt->bind_param("iss", $region['id'], $region['region_code'], $region['region_name']);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            $regions = $default_regions;
        }
    } catch (Exception $e) {
        error_log("Error fetching regions: " . $e->getMessage());
    }
    
    return $regions;
}

/**
 * Get provinces by region ID
 * 
 * @param int $region_id The region ID
 * @return array Array of provinces with id and province_name
 */
function get_provinces_by_region($region_id) {
    global $conn;
    $provinces = [];
    
    try {
        error_log("Fetching provinces for region ID: $region_id");
        
        $query = "SELECT id, province_name FROM provinces WHERE region_id = ? ORDER BY province_name";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $region_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $provinces[] = $row;
                }
                error_log("Found " . count($provinces) . " provinces for region ID: $region_id");
            } else {
                error_log("No provinces found for region ID: $region_id");
                
                // For testing/demo purposes, provide some default provinces for certain regions
                if ($region_id == 1) { // NCR
                    $provinces = [
                        ['id' => 1, 'province_name' => 'Metro Manila']
                    ];
                } else if ($region_id == 6) { // CALABARZON
                    $provinces = [
                        ['id' => 7, 'province_name' => 'Batangas'],
                        ['id' => 8, 'province_name' => 'Cavite'],
                        ['id' => 9, 'province_name' => 'Laguna'],
                        ['id' => 10, 'province_name' => 'Quezon'],
                        ['id' => 11, 'province_name' => 'Rizal']
                    ];
                }
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error fetching provinces: " . $e->getMessage());
    }
    
    return $provinces;
}

/**
 * Get districts by province ID
 * 
 * @param int $province_id The province ID
 * @return array Array of districts with id and district_name
 */
function get_districts_by_province($province_id) {
    global $conn;
    $districts = [];
    
    try {
        error_log("Fetching districts for province ID: $province_id");
        
        $query = "SELECT id, district_name FROM districts WHERE province_id = ? ORDER BY district_name";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $province_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $districts[] = $row;
                }
                error_log("Found " . count($districts) . " districts for province ID: $province_id");
            } else {
                error_log("No districts found for province ID: $province_id");
                
                // For testing/demo purposes, provide some default districts for certain provinces
                if ($province_id == 1) { // Metro Manila
                    $districts = [
                        ['id' => 5, 'district_name' => 'District 1'],
                        ['id' => 6, 'district_name' => 'District 2'],
                        ['id' => 7, 'district_name' => 'District 3'],
                        ['id' => 8, 'district_name' => 'District 4']
                    ];
                }
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error fetching districts: " . $e->getMessage());
    }
    
    return $districts;
}

/**
 * Get municipalities by province ID and optionally district ID
 * 
 * @param int $province_id The province ID
 * @param int|null $district_id The district ID (optional)
 * @return array Array of municipalities with id and municipality_name
 */
function get_municipalities_by_location($province_id, $district_id = null) {
    global $conn;
    $municipalities = [];
    
    try {
        error_log("Fetching municipalities for province ID: $province_id" . ($district_id ? ", district ID: $district_id" : ""));
        
        if ($district_id) {
            $query = "SELECT id, municipality_name FROM municipalities WHERE province_id = ? AND district_id = ? ORDER BY municipality_name";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $province_id, $district_id);
        } else {
            $query = "SELECT id, municipality_name FROM municipalities WHERE province_id = ? ORDER BY municipality_name";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $province_id);
        }
        
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $municipalities[] = $row;
                }
                error_log("Found " . count($municipalities) . " municipalities for province ID: $province_id" . 
                          ($district_id ? ", district ID: $district_id" : ""));
            } else {
                error_log("No municipalities found for province ID: $province_id" . 
                          ($district_id ? ", district ID: $district_id" : ""));
                
                // For testing/demo purposes, provide some default municipalities for certain provinces
                if ($province_id == 1) { // Metro Manila
                    if (!$district_id || $district_id == 5) {
                        $municipalities[] = ['id' => 1, 'municipality_name' => 'Quezon City'];
                    }
                    if (!$district_id || $district_id == 6) {
                        $municipalities[] = ['id' => 2, 'municipality_name' => 'Manila'];
                    }
                    if (!$district_id || $district_id == 7) {
                        $municipalities[] = ['id' => 3, 'municipality_name' => 'Makati'];
                    }
                    if (!$district_id || $district_id == 8) {
                        $municipalities[] = ['id' => 4, 'municipality_name' => 'Taguig'];
                    }
                }
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error fetching municipalities: " . $e->getMessage());
    }
    
    return $municipalities;
}

/**
 * Process AJAX requests to get location data
 */
if (isset($_GET['action'])) {
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_regions':
            // Add case to handle get_regions action
            error_log("Fetching all regions");
            $regions = get_all_regions();
            error_log("Found " . count($regions) . " regions");
            echo json_encode($regions);
            break;
            
        case 'get_provinces':
            if (isset($_GET['region_id']) && is_numeric($_GET['region_id'])) {
                $region_id = $_GET['region_id'];
                // Debug log
                error_log("Fetching provinces for region ID: $region_id");
                
                $provinces = get_provinces_by_region($region_id);
                
                // Debug log
                error_log("Found " . count($provinces) . " provinces for region ID: $region_id");
                
                echo json_encode($provinces);
            } else {
                error_log("Invalid or missing region_id parameter");
                echo json_encode([]);
            }
            break;
            
        case 'get_districts':
            if (isset($_GET['province_id']) && is_numeric($_GET['province_id'])) {
                $districts = get_districts_by_province($_GET['province_id']);
                echo json_encode($districts);
            } else {
                echo json_encode([]);
            }
            break;
            
        case 'get_municipalities':
            if (isset($_GET['province_id']) && is_numeric($_GET['province_id'])) {
                $district_id = isset($_GET['district_id']) && is_numeric($_GET['district_id']) ? $_GET['district_id'] : null;
                $municipalities = get_municipalities_by_location($_GET['province_id'], $district_id);
                echo json_encode($municipalities);
            } else {
                echo json_encode([]);
            }
            break;
            
        default:
            error_log("Invalid action: " . $_GET['action']);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit;
}
?>
