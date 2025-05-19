<?php
// Start session for authentication
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database connection and CRUD operations
require_once '../includes/db_connect.php';
require_once '../includes/crud_operations.php';

// Debug function to log to console
function debug_to_console($data) {
    if (is_array($data) || is_object($data)) {
        echo "<script>console.log(" . json_encode($data) . ");</script>";
    } else {
        echo "<script>console.log('" . addslashes($data) . "');</script>";
    }
}

// Get current month and year
$current_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$current_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$view_type = isset($_GET['view_type']) ? $_GET['view_type'] : 'monthly';
$selected_province = isset($_GET['province']) ? $_GET['province'] : 'all';

// Validate month and year
if ($current_month < 1 || $current_month > 12) {
    $current_month = intval(date('m'));
}
if ($current_year < 2020 || $current_year > intval(date('Y'))) {
    $current_year = intval(date('Y'));
}

// Set period label based on view type
$period_label = '';
if ($view_type === 'monthly') {
    $period_label = date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year));
} elseif ($view_type === 'semester') {
    $semester = ($current_month <= 6) ? 1 : 2;
    $period_label = "Semester " . $semester . ", " . $current_year;
} else {
    $period_label = $current_year;
}

// Get date range based on view type
$start_date = '';
$end_date = '';

if ($view_type === 'monthly') {
    $start_date = sprintf('%04d-%02d-01', $current_year, $current_month);
    $end_date = date('Y-m-t', strtotime($start_date)); // Last day of the month
} elseif ($view_type === 'semester') {
    if ($current_month <= 6) {
        $start_date = sprintf('%04d-01-01', $current_year);
        $end_date = sprintf('%04d-06-30', $current_year);
    } else {
        $start_date = sprintf('%04d-07-01', $current_year);
        $end_date = sprintf('%04d-12-31', $current_year);
    }
} else {
    $start_date = sprintf('%04d-01-01', $current_year);
    $end_date = sprintf('%04d-12-31', $current_year);
}

// Debug date range
debug_to_console("Date range: $start_date to $end_date");

// Get provinces for dropdown
$provinces = [];
try {
    $sql = "SELECT DISTINCT p.id, p.province_name as name 
            FROM provinces p
            LEFT JOIN tech_support_requests tsr ON tsr.province_id = p.id
            ORDER BY p.province_name";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $provinces[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Error fetching provinces: " . $e->getMessage();
    debug_to_console($error_message);
}

// Define normalized service types to prevent duplicates
$normalized_service_types = [
    'sim card registration' => 'Sim Card Registration',
    'pnpki technical support' => 'PNPKI Technical Support',
    'pnpki tech support' => 'PNPKI Technical Support',
    'govnet technical support' => 'GovNet Technical Support',
    'govnet installation/maintenance' => 'GovNet Installation/Maintenance',
    'use of space, ict equipment & internet connectivity' => 'Use of Space, ICT Equipment & Internet Connectivity',
    'use of space, ict equipment and internet connectivity' => 'Use of Space, ICT Equipment & Internet Connectivity',
    'igovphil support' => 'iGovPhil Support',
    'wifi installation/configuration' => 'WiFi Installation/Configuration',
    'lending of ict equipment' => 'Lending of ICT Equipment',
    'use of ict equipment' => 'Use of ICT Equipment',
    'use of office facility' => 'Use of Office Facility',
    'cybersecurity/data privacy related concern' => 'Cybersecurity/Data Privacy Related Concern',
    'provision of technical personnel resource person' => 'Provision of Technical Personnel Resource Person',
    'provision of technical personnel/ resoure person' => 'Provision of Technical Personnel Resource Person'
];

// Get all support types from the database
$all_support_types = [];
$unique_support_types = [];
try {
    $sql = "SELECT DISTINCT support_type FROM tech_support_requests ORDER BY support_type";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $support_type = trim($row['support_type']);
            $support_type_lower = strtolower($support_type);
            
            // Normalize the support type if it exists in our mapping
            if (isset($normalized_service_types[$support_type_lower])) {
                $support_type = $normalized_service_types[$support_type_lower];
            }
            
            // Only add if not already in the array (case-insensitive check)
            if (!in_array(strtolower($support_type), $unique_support_types)) {
                $all_support_types[] = $support_type;
                $unique_support_types[] = strtolower($support_type);
            }
        }
    }
} catch (Exception $e) {
    $error_message = "Error fetching support types: " . $e->getMessage();
    debug_to_console($error_message);
}

// If no support types found in the database, use a predefined list
if (empty($all_support_types)) {
    $predefined_types = array_values($normalized_service_types);
    
    if (!in_array('Others', $predefined_types)) {
        $predefined_types[] = 'Others';
    }
    
    $all_support_types = [];
    $unique_support_types = [];
    
    foreach ($predefined_types as $type) {
        if (!in_array(strtolower($type), $unique_support_types)) {
            $all_support_types[] = $type;
            $unique_support_types[] = strtolower($type);
        }
    }
}

// Define service categories for the province table
$service_categories = array_values($normalized_service_types);
if (!in_array('Others', $service_categories)) {
    $service_categories[] = 'Others';
}

// Get support type summary for the selected period
$support_summary = [];
try {
    $province_condition = ($selected_province !== 'all') ? "AND province_id = '$selected_province'" : "";
    
    $sql = "SELECT support_type, COUNT(*) as count 
            FROM tech_support_requests 
            WHERE date_requested BETWEEN '$start_date' AND '$end_date' 
            $province_condition
            GROUP BY support_type 
            ORDER BY count DESC";
    
    debug_to_console("Support summary SQL: $sql");
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    $normalized_summary = [];
    $normalized_types = [];
    
    while ($row = $result->fetch_assoc()) {
        $support_type = $row['support_type'];
        $support_type_lower = strtolower($support_type);
        $count = $row['count'];
        
        if (isset($normalized_service_types[$support_type_lower])) {
            $support_type = $normalized_service_types[$support_type_lower];
        }
        
        if (in_array(strtolower($support_type), $normalized_types)) {
            foreach ($normalized_summary as &$item) {
                if (strtolower($item['support_type']) === strtolower($support_type)) {
                    $item['count'] += $count;
                    break;
                }
            }
        } else {
            $normalized_summary[] = [
                'support_type' => $support_type,
                'count' => $count
            ];
            $normalized_types[] = strtolower($support_type);
        }
    }
    
    $support_summary = $normalized_summary;
    
    $sql = "SELECT COUNT(*) as total FROM tech_support_requests 
            WHERE date_requested BETWEEN '$start_date' AND '$end_date' $province_condition";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    $total_requests = $result->fetch_assoc()['total'];
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    debug_to_console($error_message);
}

// Get services by month (fixed to prevent duplicates)
$services_by_month = [];
try {
    // Get months for the period
    $months = [];
    if ($view_type === 'monthly') {
        $months = [date('F', mktime(0, 0, 0, $current_month, 1))];
    } elseif ($view_type === 'semester') {
        if ($current_month <= 6) {
            $months = ['January', 'February', 'March', 'April', 'May', 'June'];
        } else {
            $months = ['July', 'August', 'September', 'October', 'November', 'December'];
        }
    } else {
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('F', mktime(0, 0, 0, $i, 1));
        }
    }
    
    // Initialize services array with unique service types
    $temp_services = [];
    foreach ($all_support_types as $service) {
        $service_lower = strtolower($service);
        if (!isset($temp_services[$service_lower])) {
            $service_data = [
                'name' => $service,
                'monthly_counts' => [],
                'total' => 0
            ];
            
            foreach ($months as $month) {
                $service_data['monthly_counts'][$month] = 0;
            }
            
            $temp_services[$service_lower] = $service_data;
        }
    }
    
    // Get data from database
    $province_condition = ($selected_province !== 'all') ? "AND province_id = '$selected_province'" : "";
    
    if ($view_type === 'monthly') {
        $sql = "SELECT support_type, COUNT(*) as count 
                FROM tech_support_requests 
                WHERE date_requested BETWEEN '$start_date' AND '$end_date' 
                $province_condition
                GROUP BY support_type";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error executing query: " . $conn->error);
        }
        
        $month_name = date('F', mktime(0, 0, 0, $current_month, 1));
        
        while ($row = $result->fetch_assoc()) {
            $support_type = $row['support_type'];
            $support_type_lower = strtolower($support_type);
            $count = $row['count'];
            
            if (isset($normalized_service_types[$support_type_lower])) {
                $support_type = $normalized_service_types[$support_type_lower];
            }
            
            $support_type_lower = strtolower($support_type);
            
            if (isset($temp_services[$support_type_lower])) {
                $temp_services[$support_type_lower]['monthly_counts'][$month_name] += $count;
                $temp_services[$support_type_lower]['total'] += $count;
            } else {
                $service_data = [
                    'name' => $support_type,
                    'monthly_counts' => [],
                    'total' => $count
                ];
                
                foreach ($months as $m) {
                    $service_data['monthly_counts'][$m] = ($m === $month_name) ? $count : 0;
                }
                
                $temp_services[$support_type_lower] = $service_data;
            }
        }
    } else {
        $sql = "SELECT support_type, MONTH(date_requested) as month, COUNT(*) as count 
                FROM tech_support_requests 
                WHERE date_requested BETWEEN '$start_date' AND '$end_date' 
                $province_condition
                GROUP BY support_type, MONTH(date_requested)";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error executing query: " . $conn->error);
        }
        
        while ($row = $result->fetch_assoc()) {
            $month_name = date('F', mktime(0, 0, 0, $row['month'], 1));
            $support_type = $row['support_type'];
            $support_type_lower = strtolower($support_type);
            $count = $row['count'];
            
            if (isset($normalized_service_types[$support_type_lower])) {
                $support_type = $normalized_service_types[$support_type_lower];
            }
            
            $support_type_lower = strtolower($support_type);
            
            if (isset($temp_services[$support_type_lower])) {
                if (isset($temp_services[$support_type_lower]['monthly_counts'][$month_name])) {
                    $temp_services[$support_type_lower]['monthly_counts'][$month_name] += $count;
                    $temp_services[$support_type_lower]['total'] += $count;
                }
            } else {
                $service_data = [
                    'name' => $support_type,
                    'monthly_counts' => [],
                    'total' => $count
                ];
                
                foreach ($months as $m) {
                    $service_data['monthly_counts'][$m] = ($m === $month_name) ? $count : 0;
                }
                
                $temp_services[$support_type_lower] = $service_data;
            }
        }
    }
    
    // Convert temp_services to indexed array
    $services_by_month = array_values($temp_services);
    
} catch (Exception $e) {
    $error_message = "Error fetching services by month: " . $e->getMessage();
    debug_to_console($error_message);
}

// Get services by province
$services_by_province = [];
try {
    $sql = "SELECT id, province_name FROM provinces ORDER BY province_name";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $province_id = $row['id'];
        $province_name = $row['province_name'];
        
        $services_by_province[$province_id] = [
            'id' => $province_id,
            'name' => $province_name,
            'services' => [],
            'grand_total' => 0
        ];
        
        foreach ($service_categories as $category) {
            $services_by_province[$province_id]['services'][$category] = 0;
        }
    }
    
    $sql = "SELECT p.id, p.province_name as name, tsr.support_type, COUNT(*) as count
            FROM tech_support_requests tsr
            JOIN provinces p ON tsr.province_id = p.id
            WHERE tsr.date_requested BETWEEN '$start_date' AND '$end_date'
            GROUP BY p.id, p.province_name, tsr.support_type";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $province_id = $row['id'];
        $support_type = $row['support_type'];
        $support_type_lower = strtolower($support_type);
        $count = $row['count'];
        
        if (isset($normalized_service_types[$support_type_lower])) {
            $support_type = $normalized_service_types[$support_type_lower];
        }
        
        $matched = false;
        foreach ($service_categories as $category) {
            if (strtolower($category) === strtolower($support_type)) {
                $services_by_province[$province_id]['services'][$category] += $count;
                $services_by_province[$province_id]['grand_total'] += $count;
                $matched = true;
                break;
            }
        }
        
        if (!$matched) {
            $services_by_province[$province_id]['services']['Others'] += $count;
            $services_by_province[$province_id]['grand_total'] += $count;
        }
    }
    
    $services_by_province = array_filter($services_by_province, function($province) {
        return $province['grand_total'] > 0;
    });
    
    $services_by_province = array_values($services_by_province);
    
} catch (Exception $e) {
    $error_message = "Error fetching services by province: " . $e->getMessage();
    debug_to_console($error_message);
}

// Direct database query to get raw data for debugging
try {
    $raw_sql = "SELECT * FROM tech_support_requests 
                WHERE date_requested BETWEEN '$start_date' AND '$end_date'
                ORDER BY date_requested DESC";
    $raw_result = $conn->query($raw_sql);
    $raw_data = [];
    
    if ($raw_result) {
        while ($row = $raw_result->fetch_assoc()) {
            $raw_data[] = $row;
        }
    }
    
    debug_to_console("Raw data from database:");
    debug_to_console($raw_data);
} catch (Exception $e) {
    debug_to_console("Error fetching raw data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Summary - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .main-content {
            height: 100vh;
            overflow-y: auto;
            max-width: 100%;
        }
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        .sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-100">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 main-content">
            <!-- Top Navigation -->
            <div class="bg-white shadow-md">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <h1 class="text-xl font-bold text-gray-800">Support Summary</h1>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="ml-4 flex items-center md:ml-6">
                                <div class="relative">
                                    <div class="flex items-center">
                                        <span class="text-gray-700 text-sm mr-2">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <main class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Period and Province Selection -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Support Requests Summary</h2>
                                <p class="mt-1 text-sm text-gray-500">View statistics for technical support requests by period and province.</p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <form id="filterForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="grid grid-cols-1 sm:grid-cols-2 md:flex md:items-center gap-2">
                                    <div>
                                        <label for="view_type" class="block text-sm font-medium text-gray-700 mb-1">View Type</label>
                                        <select name="view_type" id="view_type" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full">
                                            <option value="monthly" <?php echo $view_type === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                            <option value="semester" <?php echo $view_type === 'semester' ? 'selected' : ''; ?>>Semester</option>
                                            <option value="yearly" <?php echo $view_type === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                                        <select name="month" id="month" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full" <?php echo $view_type !== 'monthly' ? 'disabled' : ''; ?>>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i === $current_month ? 'selected' : ''; ?>>
                                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                                        <select name="year" id="year" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full">
                                            <?php for ($i = intval(date('Y')); $i >= 2020; $i--): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i === $current_year ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                        <select name="province" id="province" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full">
                                            <option value="all">All Provinces</option>
                                            <?php foreach ($provinces as $province): ?>
                                                <option value="<?php echo $province['id']; ?>" <?php echo $selected_province == $province['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($province['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mt-6">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            View
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Total Requests</h3>
                                    <p class="text-3xl font-bold text-gray-700"><?php echo isset($total_requests) ? $total_requests : 0; ?></p>
                                    <p class="text-sm text-gray-500">For <?php echo $period_label; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Most Requested</h3>
                                    <p class="text-xl font-bold text-gray-700">
                                        <?php 
                                        if (!empty($support_summary)) {
                                            usort($support_summary, function($a, $b) {
                                                return $b['count'] - $a['count'];
                                            });
                                            echo htmlspecialchars($support_summary[0]['support_type']);
                                        } else {
                                            echo "No data";
                                        }
                                        ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php 
                                        if (!empty($support_summary)) {
                                            echo $support_summary[0]['count'] . " requests";
                                        } else {
                                            echo "&nbsp;";
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Provinces Served</h3>
                                    <p class="text-3xl font-bold text-gray-700">
                                        <?php echo count($services_by_province); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">With service requests</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services Provided Table -->
                    <div class="bg-white rounded-lg shadow-md mb-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Services Provided - <?php echo $period_label; ?>
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                <?php echo $view_type === 'monthly' ? 'Monthly' : ($view_type === 'semester' ? 'Semester' : 'Yearly'); ?> breakdown of DICT services usage.
                            </p>
                        </div>
                        
                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky-header">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Services Provided
                                        </th>
                                        <?php 
                                        if (!empty($services_by_month) && !empty($services_by_month[0]['monthly_counts'])) {
                                            foreach (array_keys($services_by_month[0]['monthly_counts']) as $month) {
                                                echo '<th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">' . $month . '</th>';
                                            }
                                        }
                                        ?>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($services_by_month)): ?>
                                    <tr>
                                        <td colspan="<?php echo count($services_by_month[0]['monthly_counts'] ?? []) + 2; ?>" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            No data available for this period.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php 
                                        $filtered_services = array_filter($services_by_month, function($service) {
                                            return $service['total'] > 0;
                                        });
                                        
                                        if (empty($filtered_services)) {
                                            $filtered_services = $services_by_month;
                                        }
                                        
                                        foreach ($filtered_services as $index => $service): 
                                        ?>
                                        <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($service['name']); ?>
                                            </td>
                                            <?php foreach ($service['monthly_counts'] as $count): ?>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                    <?php echo $count; ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900 bg-gray-100">
                                                <?php echo $service['total']; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <!-- Total Row -->
                                        <tr class="bg-blue-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                TOTAL
                                            </td>
                                            <?php 
                                            if (!empty($services_by_month) && !empty($services_by_month[0]['monthly_counts'])) {
                                                $months = array_keys($services_by_month[0]['monthly_counts']);
                                                foreach ($months as $month) {
                                                    $month_total = 0;
                                                    foreach ($services_by_month as $service) {
                                                        $month_total += $service['monthly_counts'][$month];
                                                    }
                                                    echo '<td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">' . $month_total . '</td>';
                                                }
                                            }
                                            
                                            $grand_total = 0;
                                            foreach ($services_by_month as $service) {
                                                $grand_total += $service['total'];
                                            }
                                            ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 bg-blue-100">
                                                <?php echo $grand_total; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Services by Province Table -->
                    <div class="bg-white rounded-lg shadow-md mb-6">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Services by Province - <?php echo $period_label; ?>
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Breakdown of DICT services usage by province.
                            </p>
                        </div>
                        
                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky-header">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Province
                                        </th>
                                        <?php foreach ($service_categories as $category): ?>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?php echo htmlspecialchars($category); ?>
                                        </th>
                                        <?php endforeach; ?>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">
                                            Grand Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($services_by_province)): ?>
                                    <tr>
                                        <td colspan="<?php echo count($service_categories) + 2; ?>" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            No data available for this period.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($services_by_province as $index => $province): ?>
                                        <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($province['name']); ?>
                                            </td>
                                            <?php foreach ($service_categories as $category): ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                <?php echo isset($province['services'][$category]) ? $province['services'][$category] : 0; ?>
                                            </td>
                                            <?php endforeach; ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900 bg-gray-100">
                                                <?php echo $province['grand_total']; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <!-- Total Row -->
                                        <tr class="bg-blue-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                TOTAL
                                            </td>
                                            <?php 
                                            foreach ($service_categories as $category) {
                                                $total = 0;
                                                foreach ($services_by_province as $province) {
                                                    $total += isset($province['services'][$category]) ? $province['services'][$category] : 0;
                                                }
                                                echo '<td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">' . $total . '</td>';
                                            }
                                            
                                            $actual_grand_total = 0;
                                            foreach ($services_by_province as $province) {
                                                $actual_grand_total += $province['grand_total'];
                                            }
                                            ?>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 bg-blue-100">
                                                <?php echo $actual_grand_total; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Support Types Distribution</h3>
                            <div class="h-80">
                                <canvas id="supportTypeChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Provinces by Service Usage</h3>
                            <div class="h-80">
                                <canvas id="provinceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <p class="text-center text-sm text-gray-500">© 2025 DICT Client Management System. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- JavaScript for Charts and Interactivity -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const viewTypeSelect = document.getElementById('view_type');
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const provinceSelect = document.getElementById('province');
            
            function submitForm() {
                filterForm.submit();
            }
            
            viewTypeSelect.addEventListener('change', function() {
                if (this.value === 'monthly') {
                    monthSelect.disabled = false;
                } else {
                    monthSelect.disabled = true;
                }
                submitForm();
            });
            
            monthSelect.addEventListener('change', submitForm);
            yearSelect.addEventListener('change', submitForm);
            provinceSelect.addEventListener('change', submitForm);
            
            const supportTypeData = <?php 
                $chart_data = array_filter($support_summary, function($item) {
                    return $item['count'] > 0;
                });
                echo json_encode($chart_data); 
            ?>;
            
            if (supportTypeData.length > 0) {
                const supportTypeCtx = document.getElementById('supportTypeChart').getContext('2d');
                const supportTypeChart = new Chart(supportTypeCtx, {
                    type: 'pie',
                    data: {
                        labels: supportTypeData.map(item => item.support_type),
                        datasets: [{
                            data: supportTypeData.map(item => item.count),
                            backgroundColor: [
                                '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                                '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            } else {
                document.getElementById('supportTypeChart').parentNode.innerHTML = 
                    '<div class="flex items-center justify-center h-full text-gray-500">No data available for this period</div>';
            }
            
            const provinceData = <?php 
                $top_provinces = $services_by_province;
                usort($top_provinces, function($a, $b) {
                    return $b['grand_total'] - $a['grand_total'];
                });
                $top_provinces = array_slice($top_provinces, 0, 5);
                echo json_encode($top_provinces); 
            ?>;
            
            if (provinceData.length > 0) {
                const provinceLabels = provinceData.map(p => p.name);
                const provinceCounts = provinceData.map(p => p.grand_total);
                
                const provinceCtx = document.getElementById('provinceChart').getContext('2d');
                const provinceChart = new Chart(provinceCtx, {
                    type: 'bar',
                    data: {
                        labels: provinceLabels,
                        datasets: [{
                            label: 'Service Requests',
                            data: provinceCounts,
                            backgroundColor: '#10B981',
                            borderColor: '#059669',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            } else {
                document.getElementById('provinceChart').parentNode.innerHTML = 
                    '<div class="flex items-center justify-center h-full text-gray-500">No data available for this period</div>';
            }
        });
    </script>
</body>
</html>