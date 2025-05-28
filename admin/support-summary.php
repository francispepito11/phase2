<?php
// Start session for authentication
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Set page title
$page_title = "Support Summary";

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - DICT Client Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.5rem;
        }
        
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        
        .sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8f9fa;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Page Content -->
    <div class="container-fluid py-4">
        <!-- Period and Province Selection -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="card-title h4 mb-1">Support Requests Summary</h2>
                        <p class="text-muted small mb-0">View statistics for technical support requests by period and province.</p>
                    </div>
                    <div class="col-md-6">
                        <form id="filterForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3">
                            <div class="col-md-3">
                                <label for="view_type" class="form-label">View Type</label>
                                <select name="view_type" id="view_type" class="form-select">
                                    <option value="monthly" <?php echo $view_type === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="semester" <?php echo $view_type === 'semester' ? 'selected' : ''; ?>>Semester</option>
                                    <option value="yearly" <?php echo $view_type === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="month" class="form-label">Month</label>
                                <select name="month" id="month" class="form-select" <?php echo $view_type !== 'monthly' ? 'disabled' : ''; ?>>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === $current_month ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="year" class="form-label">Year</label>
                                <select name="year" id="year" class="form-select">
                                    <?php for ($i = intval(date('Y')); $i >= 2020; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === $current_year ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="province" class="form-label">Province</label>
                                <select name="province" id="province" class="form-select">
                                    <option value="all">All Provinces</option>
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?php echo $province['id']; ?>" <?php echo $selected_province == $province['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($province['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter me-1"></i> View
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle mb-1 text-muted">Total Requests</h6>
                            <h2 class="card-title mb-0"><?php echo isset($total_requests) ? $total_requests : 0; ?></h2>
                            <p class="card-text small text-muted">For <?php echo $period_label; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle mb-1 text-muted">Most Requested</h6>
                            <h5 class="card-title mb-0">
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
                            </h5>
                            <p class="card-text small text-muted">
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
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle mb-1 text-muted">Provinces Served</h6>
                            <h2 class="card-title mb-0">
                                <?php echo count($services_by_province); ?>
                            </h2>
                            <p class="card-text small text-muted">With service requests</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Support Types Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="supportTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Provinces by Service Usage</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="provinceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Provided Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Services Provided - <?php echo $period_label; ?></h5>
                <p class="text-muted small mb-0">
                    <?php echo $view_type === 'monthly' ? 'Monthly' : ($view_type === 'semester' ? 'Semester' : 'Yearly'); ?> breakdown of DICT services usage.
                </p>
            </div>
            
            <div class="table-container">
                <table class="table table-striped table-hover mb-0">
                    <thead class="sticky-header">
                        <tr>
                            <th>Services Provided</th>
                            <?php 
                            if (!empty($services_by_month) && !empty($services_by_month[0]['monthly_counts'])) {
                                foreach (array_keys($services_by_month[0]['monthly_counts']) as $month) {
                                    echo '<th class="text-center">' . $month . '</th>';
                                }
                            }
                            ?>
                            <th class="text-center bg-light">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services_by_month)): ?>
                        <tr>
                            <td colspan="<?php echo count($services_by_month[0]['monthly_counts'] ?? []) + 2; ?>" class="text-center py-4">
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
                            
                            foreach ($filtered_services as $service): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($service['name']); ?></td>
                                <?php foreach ($service['monthly_counts'] as $count): ?>
                                    <td class="text-center"><?php echo $count; ?></td>
                                <?php endforeach; ?>
                                <td class="text-center fw-bold bg-light"><?php echo $service['total']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Total Row -->
                            <tr class="table-primary">
                                <td class="fw-bold">TOTAL</td>
                                <?php 
                                if (!empty($services_by_month) && !empty($services_by_month[0]['monthly_counts'])) {
                                    $months = array_keys($services_by_month[0]['monthly_counts']);
                                    foreach ($months as $month) {
                                        $month_total = 0;
                                        foreach ($services_by_month as $service) {
                                            $month_total += $service['monthly_counts'][$month];
                                        }
                                        echo '<td class="text-center fw-bold">' . $month_total . '</td>';
                                    }
                                }
                                
                                $grand_total = 0;
                                foreach ($services_by_month as $service) {
                                    $grand_total += $service['total'];
                                }
                                ?>
                                <td class="text-center fw-bold bg-primary bg-opacity-25"><?php echo $grand_total; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Services by Province Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Services by Province - <?php echo $period_label; ?></h5>
                <p class="text-muted small mb-0">Breakdown of DICT services usage by province.</p>
            </div>
            
            <div class="table-container">
                <table class="table table-striped table-hover mb-0">
                    <thead class="sticky-header">
                        <tr>
                            <th>Province</th>
                            <?php foreach ($service_categories as $category): ?>
                            <th class="text-center"><?php echo htmlspecialchars($category); ?></th>
                            <?php endforeach; ?>
                            <th class="text-center bg-light">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services_by_province)): ?>
                        <tr>
                            <td colspan="<?php echo count($service_categories) + 2; ?>" class="text-center py-4">
                                No data available for this period.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($services_by_province as $province): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($province['name']); ?></td>
                                <?php foreach ($service_categories as $category): ?>
                                <td class="text-center"><?php echo isset($province['services'][$category]) ? $province['services'][$category] : 0; ?></td>
                                <?php endforeach; ?>
                                <td class="text-center fw-bold bg-light"><?php echo $province['grand_total']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Total Row -->
                            <tr class="table-primary">
                                <td class="fw-bold">TOTAL</td>
                                <?php 
                                foreach ($service_categories as $category) {
                                    $total = 0;
                                    foreach ($services_by_province as $province) {
                                        $total += isset($province['services'][$category]) ? $province['services'][$category] : 0;
                                    }
                                    echo '<td class="text-center fw-bold">' . $total . '</td>';
                                }
                                
                                $actual_grand_total = 0;
                                foreach ($services_by_province as $province) {
                                    $actual_grand_total += $province['grand_total'];
                                }
                                ?>
                                <td class="text-center fw-bold bg-primary bg-opacity-25"><?php echo $actual_grand_total; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-auto border-top">
        <div class="container-fluid">
            <div class="text-center small">
                <div class="text-muted">© 2025 DICT Client Management System. All rights reserved.</div>
            </div>
        </div>
    </footer>
    
    <!-- Include Page Wrapper End -->


    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Sidebar Dropdown -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all Bootstrap dropdowns and collapses
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        var collapseElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="collapse"]'));
        var collapseList = collapseElementList.map(function(collapseToggleEl) {
            return new bootstrap.Collapse(collapseToggleEl.getAttribute('data-bs-target') || collapseToggleEl.getAttribute('href'), {
                toggle: false
            });
        });
        
        // Manually handle Tech Supports dropdown
        var techSupportsToggle = document.querySelector('[data-bs-target="#techSupportsSubmenu"]');
        if (techSupportsToggle) {
            techSupportsToggle.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('data-bs-target'));
                if (target) {
                    if (target.classList.contains('show')) {
                        bootstrap.Collapse.getInstance(target).hide();
                    } else {
                        bootstrap.Collapse.getInstance(target).show();
                    }
                }
            });
        }
        
        // Form handling
        const filterForm = document.getElementById('filterForm');
        const viewTypeSelect = document.getElementById('view_type');
        const monthSelect = document.getElementById('month');
        
        viewTypeSelect.addEventListener('change', function() {
            if (this.value === 'monthly') {
                monthSelect.disabled = false;
            } else {
                monthSelect.disabled = true;
            }
        });
        
        // Charts
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
                            '#4361ee', '#3a0ca3', '#7209b7', '#f72585', '#4cc9f0',
                            '#4895ef', '#560bad', '#f15bb5', '#fee440', '#00bbf9'
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
                '<div class="d-flex align-items-center justify-content-center h-100 text-muted">No data available for this period</div>';
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
                        backgroundColor: '#4cc9f0',
                        borderColor: '#4895ef',
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
                '<div class="d-flex align-items-center justify-content-center h-100 text-muted">No data available for this period</div>';
        }
    });
    </script>
</body>
</html>
