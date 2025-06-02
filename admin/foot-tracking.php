<?php
// Start session for authentication
session_start();

// Add semester selection functionality at the top of the file after session_start()

// Get current semester and year
$current_semester = isset($_GET['semester']) ? intval($_GET['semester']) : (intval(date('m')) <= 6 ? 1 : 2);
$current_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate semester
if ($current_semester < 1 || $current_semester > 2) {
    $current_semester = (intval(date('m')) <= 6 ? 1 : 2);
}

// Format date for SQL query based on semester
if ($current_semester == 1) {
    $start_date = sprintf('%04d-01-01', $current_year);
    $end_date = sprintf('%04d-06-30', $current_year);
    $semester_name = "First Semester (January-June) " . $current_year;
} else {
    $start_date = sprintf('%04d-07-01', $current_year);
    $end_date = sprintf('%04d-12-31', $current_year);
    $semester_name = "Second Semester (July-December) " . $current_year;
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database connection and CRUD operations
require_once '../includes/db_connect.php';
require_once '../includes/crud_operations.php';

// Get foot traffic data (focusing on "Use of ICT Equipment" and similar services)
$foot_traffic_data = [];
try {
    $sql = "SELECT support_type, COUNT(*) as count 
            FROM tech_support_requests 
            WHERE date_requested BETWEEN ? AND ? 
            AND support_type IN ('Use of ICT Equipment', 'Lending of ICT Equipment', 'Use of Office Facility', 'Use of Space, ICT Equipment & Internet Connectivity')
            GROUP BY support_type 
            ORDER BY count DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $foot_traffic_data[] = $row;
    }
    
    $stmt->close();
    
    // Get total foot traffic for the month
    $sql = "SELECT COUNT(*) as total 
            FROM tech_support_requests 
            WHERE date_requested BETWEEN ? AND ? 
            AND support_type IN ('Use of ICT Equipment', 'Lending of ICT Equipment', 'Use of Office Facility', 'Use of Space, ICT Equipment & Internet Connectivity')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_foot_traffic = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Get daily foot traffic
    $sql = "SELECT DATE(date_requested) as request_date, COUNT(*) as count 
            FROM tech_support_requests 
            WHERE date_requested BETWEEN ? AND ? 
            AND support_type IN ('Use of ICT Equipment', 'Lending of ICT Equipment', 'Use of Office Facility', 'Use of Space, ICT Equipment & Internet Connectivity')
            GROUP BY DATE(date_requested) 
            ORDER BY request_date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $daily_foot_traffic = [];
    while ($row = $result->fetch_assoc()) {
        $daily_foot_traffic[] = $row;
    }
    
    $stmt->close();
      // Get foot traffic by age group and gender per province
    $age_gender_data = [];
    try {
        $sql = "SELECT 
                p.province_name,
                c.gender,
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) < 15 THEN 'Under 15'
                    WHEN TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) BETWEEN 15 AND 24 THEN '15-24'
                    WHEN TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) BETWEEN 25 AND 59 THEN '25-59'
                    ELSE '60 and above'
                END as age_group,
                COUNT(*) as count
            FROM clients c
            JOIN tech_support_requests tsr ON c.id = tsr.client_id
            JOIN provinces p ON c.province_id = p.id
            WHERE tsr.date_requested BETWEEN ? AND ?
            AND tsr.support_type IN ('Use of ICT Equipment', 'Lending of ICT Equipment', 'Use of Office Facility', 'Use of Space, ICT Equipment & Internet Connectivity')
            GROUP BY p.province_name, c.gender, age_group
            ORDER BY p.province_name, c.gender, age_group";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $age_gender_data[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error getting age and gender data: " . $e->getMessage());
        // Handle the error appropriately
    }
      // Get monthly counts of male and female clients per province (without support type)
    $monthly_gender_data = [];
    $sql = "SELECT 
        p.province_name, 
        SUM(CASE WHEN c.gender = 'Male' THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN c.gender = 'Female' THEN 1 ELSE 0 END) as female_count
    FROM tech_support_requests tsr
    JOIN clients c ON tsr.client_id = c.id
    LEFT JOIN provinces p ON c.province_id = p.id
    WHERE tsr.date_requested BETWEEN ? AND ? 
    GROUP BY p.province_name
    ORDER BY p.province_name";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $monthly_gender_data[] = $row;
    }
    
    $stmt->close();    
    // Get monthly data by province, age group, and gender
    $monthly_age_gender_data = [];
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    foreach ($months as $month_index => $month_name) {
        $month_num = $month_index + 1;
        $month_start = sprintf('%04d-%02d-01', $current_year, $month_num);
        $month_end = date('Y-m-t', strtotime($month_start));
        
        // Skip months not in the current semester
        if (($current_semester == 1 && $month_num > 6) || ($current_semester == 2 && $month_num < 7)) {
            continue;
        }
        
        $sql = "SELECT 
            p.province_name,
            c.gender,
            CASE
                WHEN TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) < 15 THEN 'Under 15'
                WHEN TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) BETWEEN 15 AND 24 THEN '15-24'
                WHEN TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) BETWEEN 25 AND 59 THEN '25-59'
                ELSE '60 and above'
            END as age_group,
            COUNT(*) as count
        FROM tech_support_requests tsr
        JOIN clients c ON tsr.client_id = c.id
        LEFT JOIN provinces p ON c.province_id = p.id
        WHERE tsr.date_requested BETWEEN ? AND ?
        AND tsr.support_type IN ('Use of ICT Equipment', 'Lending of ICT Equipment', 'Use of Office Facility', 'Use of Space, ICT Equipment & Internet Connectivity')
        GROUP BY p.province_name, c.gender, age_group
        ORDER BY p.province_name, c.gender, age_group";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $month_start, $month_end);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $monthly_age_gender_data[$month_name] = [];
        while ($row = $result->fetch_assoc()) {
            if (!isset($monthly_age_gender_data[$month_name][$row['province_name']])) {
                $monthly_age_gender_data[$month_name][$row['province_name']] = [
                    'Male' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0],
                    'Female' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0]
                ];
            }
            
            $monthly_age_gender_data[$month_name][$row['province_name']][$row['gender']][$row['age_group']] = $row['count'];
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Get comparison with previous semester
$prev_semester = $current_semester == 1 ? 2 : 1;
$prev_year = $current_year;
if ($current_semester == 1) {
    $prev_year--;
}

if ($prev_semester == 1) {
    $prev_start_date = sprintf('%04d-01-01', $prev_year);
    $prev_end_date = sprintf('%04d-06-30', $prev_year);
    $prev_semester_name = "First Semester (January-June) " . $prev_year;
} else {
    $prev_start_date = sprintf('%04d-07-01', $prev_year);
    $prev_end_date = sprintf('%04d-12-31', $prev_year);
    $prev_semester_name = "Second Semester (July-December) " . $prev_year;
}

$sql = "SELECT COUNT(*) as total 
        FROM tech_support_requests 
        WHERE date_requested BETWEEN ? AND ? 
        AND support_type IN ('Use of ICT Equipment', 'Lending of ICT Equipment', 'Use of Office Facility', 'Use of Space, ICT Equipment & Internet Connectivity')";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $prev_start_date, $prev_end_date);
$stmt->execute();
$result = $stmt->get_result();
$prev_month_total = $result->fetch_assoc()['total'];
$stmt->close();

// Calculate percentage change
if ($prev_month_total > 0) {
    $percent_change = (($total_foot_traffic - $prev_month_total) / $prev_month_total) * 100;
} else {
    $percent_change = $total_foot_traffic > 0 ? 100 : 0;
}

// Get semester name
$semester_name = $current_semester === 1 ? "First Semester (January-June) " . $current_year : "Second Semester (July-December) " . $current_year;
$prev_semester = $current_semester === 1 ? 2 : 1;
$prev_year = $current_semester === 1 ? $current_year - 1 : $current_year;
$prev_semester_name = $prev_semester === 1 ? "First Semester (January-June) " . $prev_year : "Second Semester (July-December) " . $prev_year;
$month_name = $semester_name;
$prev_month_name = $prev_semester_name;

// Set page title for the template
$page_title = "Foot Traffic Tracking";
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
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Card Styles */
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
          /* Table Styles */
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        
        .table-responsive {
            overflow-x: auto;
            max-width: 100%;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px; /* Ensure minimum width for readability */
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        
        .table th, .table td {
            padding: 0.75rem;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        /* Age and Gender Table Styles */
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        
        .table thead th {
            vertical-align: middle;
            background-color: #f8f9fa;
        }
        
        .region-column {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
            min-width: 200px;
        }
          .age-group-header {
            background-color: #f1f5f9 !important;
            font-size: 0.875rem;
            padding: 0.5rem;
            text-align: center;
            font-weight: 600;
        }
        
        .table-responsive {
            max-height: 600px;
            overflow: auto;
        }
        
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }
        
        /* Hover effect */
        .table tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        
        /* Sticky header */
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
        }
        
        /* Fixed layout for better performance */
        .table {
            table-layout: fixed;
        }
          /* Responsive cell widths */
        .table th:not(.region-column),
        .table td:not(.region-column) {
            min-width: 50px;
            text-align: center;
        }
        
        .region-column {
            min-width: 150px;
            max-width: 200px;
        }
        
        /* Chart Container */
        .chart-container {
            height: 300px;
            position: relative;
        }
    </style>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include '../admin/includes/sidebar.php'; ?>
    
    <!-- Page Content -->
    <div class="container-fluid py-4">
        <!-- Semester Selection -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-1">Foot Traffic Analysis</h5>
                        <p class="text-muted small">Track usage of ICT equipment and facilities by semester.</p>
                    </div>
                    <div class="col-md-6">
                        <form id="semesterForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="d-flex justify-content-md-end mt-3 mt-md-0">
                            <select name="semester" id="semester" onchange="document.getElementById('semesterForm').submit();" class="form-select me-2" style="width: auto;">
                                <option value="1" <?php echo $current_semester === 1 ? 'selected' : ''; ?>>First Semester (Jan-Jun)</option>
                                <option value="2" <?php echo $current_semester === 2 ? 'selected' : ''; ?>>Second Semester (Jul-Dec)</option>
                            </select>
                            <select name="year" id="year" onchange="document.getElementById('semesterForm').submit();" class="form-select" style="width: auto;">
                                <?php for ($i = intval(date('Y')); $i >= 2020; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i === $current_year ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
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
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle mb-1 text-muted">Total Foot Traffic</h6>
                            <h2 class="card-title mb-0"><?php echo isset($total_foot_traffic) ? $total_foot_traffic : 0; ?></h2>
                            <p class="card-text small text-muted">For <?php echo $semester_name; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle mb-1 text-muted">Monthly Change</h6>
                            <h2 class="card-title mb-0 <?php echo $percent_change >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $percent_change >= 0 ? '+' : ''; ?><?php echo number_format($percent_change, 1); ?>%
                            </h2>
                            <p class="card-text small text-muted">vs <?php echo $prev_semester_name; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                        <div>
                            <h6 class="card-subtitle mb-1 text-muted">Daily Average</h6>
                            <h2 class="card-title mb-0">
                                <?php 
                                $days_in_month = date('t', mktime(0, 0, 0, $current_semester == 1 ? 1 : 7, 1, $current_year));
                                echo isset($total_foot_traffic) ? number_format($total_foot_traffic / $days_in_month, 1) : 0; 
                                ?>
                            </h2>
                            <p class="card-text small text-muted">Visitors per day</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Service Type Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="serviceTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Daily Foot Traffic</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="dailyTrafficChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Foot Traffic Breakdown for <?php echo $semester_name; ?></h5>
                <p class="card-text small text-muted mt-1">Detailed breakdown of facility and equipment usage.</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($foot_traffic_data)): ?>
                            <tr>
                                <td colspan="3" class="text-center">No data available for this semester.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($foot_traffic_data as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['support_type']); ?></td>
                                    <td><?php echo $item['count']; ?></td>
                                    <td><?php echo number_format(($item['count'] / $total_foot_traffic) * 100, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        <!-- Age Group and Gender Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Foot Traffic by Age Group and Gender per Province for <?php echo $semester_name; ?></h5>
                <p class="card-text small text-muted mt-1">Breakdown of visitors by age group (Youth, Adults, Seniors) and gender.</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-striped table-hover">                        <thead>
                            <tr>
                                <th rowspan="2" class="region-column">Province</th>
                                <th colspan="4" class="text-center">Male</th>
                                <th colspan="4" class="text-center">Female</th>
                            </tr>
                            <tr>
                                <th class="age-group-header">Under 15</th>
                                <th class="age-group-header">15-24</th>
                                <th class="age-group-header">25-59</th>
                                <th class="age-group-header">60+</th>
                                <th class="age-group-header">Under 15</th>
                                <th class="age-group-header">15-24</th>
                                <th class="age-group-header">25-59</th>
                                <th class="age-group-header">60+</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($age_gender_data)): ?>                            <tr>
                                <td colspan="9" class="text-center">No data available for this semester.</td>
                            </tr>
                            <?php else: ?>
                            <?php
                            $provinces = [];
                            foreach ($age_gender_data as $data) {
                                if (!isset($provinces[$data['province_name']])) {
                                    $provinces[$data['province_name']] = [
                                        'Male' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0],
                                        'Female' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0]
                                    ];
                                }
                                $provinces[$data['province_name']][$data['gender']][$data['age_group']] = $data['count'];
                            }

                            foreach ($provinces as $province => $data): ?>
                                <tr>
                                    <td class="region-column"><?php echo htmlspecialchars($province); ?></td>
                                    <!-- Male age groups -->
                                    <td><?php echo $data['Male']['Under 15'] ?? 0; ?></td>
                                    <td><?php echo $data['Male']['15-24'] ?? 0; ?></td>
                                    <td><?php echo $data['Male']['25-59'] ?? 0; ?></td>
                                    <td><?php echo $data['Male']['60 and above'] ?? 0; ?></td>
                                    <!-- Female age groups -->
                                    <td><?php echo $data['Female']['Under 15'] ?? 0; ?></td>
                                    <td><?php echo $data['Female']['15-24'] ?? 0; ?></td>
                                    <td><?php echo $data['Female']['25-59'] ?? 0; ?></td>
                                    <td><?php echo $data['Female']['60 and above'] ?? 0; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        <!-- Monthly Age Group and Gender Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Foot Traffic by Gender per Province for <?php echo $semester_name; ?></h5>
                <p class="card-text small text-muted mt-1">Total monthly visitors by gender per province (all age groups combined for readability).</p>
                <small class="text-info">
                    <i class="bi bi-info-circle"></i> 
                    This table shows total monthly counts by gender. For detailed age group breakdowns, see the table above.
                </small>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-striped table-hover">                        <thead>
                            <tr>
                                <th rowspan="2" class="region-column">Province</th>
                                <?php 
                                $semester_months = $current_semester == 1 
                                    ? ['January', 'February', 'March', 'April', 'May', 'June'] 
                                    : ['July', 'August', 'September', 'October', 'November', 'December'];
                                
                                foreach ($semester_months as $month) {
                                    echo '<th colspan="2" class="age-group-header">' . $month . '</th>';
                                }
                                ?>
                            </tr>
                            <tr>
                                <?php 
                                foreach ($semester_months as $month) {
                                    echo '<th>M</th>';
                                    echo '<th>F</th>';
                                }
                                ?>
                            </tr>
                        </thead>                        <tbody>
                            <?php if (empty($age_gender_data)): ?>
                            <tr>
                                <td colspan="<?php echo 1 + (count($semester_months) * 2); ?>" class="text-center">No data available for this semester.</td>
                            </tr>
                            <?php else: ?>
                            <?php
                            $provinces = [];
                            foreach ($age_gender_data as $data) {
                                if (!isset($provinces[$data['province_name']])) {
                                    $provinces[$data['province_name']] = [
                                        'Male' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0],
                                        'Female' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0]
                                    ];
                                }
                                $provinces[$data['province_name']][$data['gender']][$data['age_group']] = $data['count'];
                            }

                            foreach ($provinces as $province => $data): ?>
                                <tr>
                                    <td class="region-column"><?php echo htmlspecialchars($province); ?></td>
                                    <?php foreach ($semester_months as $month): ?>
                                        <?php 
                                        $month_data = isset($monthly_age_gender_data[$month][$province]) 
                                            ? $monthly_age_gender_data[$month][$province] 
                                            : [
                                                'Male' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0],
                                                'Female' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0]
                                            ];
                                        
                                        // Calculate total for the month
                                        $male_total = $month_data['Male']['Under 15'] + $month_data['Male']['15-24'] + 
                                                     $month_data['Male']['25-59'] + $month_data['Male']['60 and above'];
                                        $female_total = $month_data['Female']['Under 15'] + $month_data['Female']['15-24'] + 
                                                      $month_data['Female']['25-59'] + $month_data['Female']['60 and above'];
                                        ?>
                                        <td><?php echo $male_total; ?></td>
                                        <td><?php echo $female_total; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        <!-- Monthly Gender Count by Province Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Client Count by Gender per Province for <?php echo $semester_name; ?></h5>
                <p class="card-text small text-muted mt-1">Breakdown of male and female clients using DICT support services by province.</p>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Province</th>
                                <th>Male</th>
                                <th>Female</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthly_gender_data)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No data available for this semester.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($monthly_gender_data as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['province_name']); ?></td>
                                    <td><?php echo $item['male_count']; ?></td>
                                    <td><?php echo $item['female_count']; ?></td>
                                    <td><strong><?php echo $item['male_count'] + $item['female_count']; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        <!-- Foot Traffic by Age Group and Gender per Province -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Foot Traffic by Age Group and Gender per Province</h5>
                <p class="text-muted small mb-0"><?php echo $semester_name; ?></p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th rowspan="2" class="region-column">Province</th>
                                <th colspan="4" class="text-center">Male</th>
                                <th colspan="4" class="text-center">Female</th>
                            </tr>
                            <tr>
                                <th class="age-group-header">Under 15</th>
                                <th class="age-group-header">15-24</th>
                                <th class="age-group-header">25-59</th>
                                <th class="age-group-header">60+</th>
                                <th class="age-group-header">Under 15</th>
                                <th class="age-group-header">15-24</th>
                                <th class="age-group-header">25-59</th>
                                <th class="age-group-header">60+</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $regions = [];                            foreach ($age_gender_data as $data) {
                                if (!isset($regions[$data['province_name']])) {
                                    $regions[$data['province_name']] = [
                                        'Male' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0],
                                        'Female' => ['Under 15' => 0, '15-24' => 0, '25-59' => 0, '60 and above' => 0]
                                    ];
                                }
                                $regions[$data['province_name']][$data['gender']][$data['age_group']] = $data['count'];
                            }

                            foreach ($regions as $region => $data): ?>
                                <tr>
                                    <td class="region-column"><?php echo htmlspecialchars($region); ?></td>
                                    <!-- Male age groups -->
                                    <td><?php echo $data['Male']['Under 15']; ?></td>
                                    <td><?php echo $data['Male']['15-24']; ?></td>
                                    <td><?php echo $data['Male']['25-59']; ?></td>
                                    <td><?php echo $data['Male']['60 and above']; ?></td>
                                    <!-- Female age groups -->
                                    <td><?php echo $data['Female']['Under 15']; ?></td>
                                    <td><?php echo $data['Female']['15-24']; ?></td>
                                    <td><?php echo $data['Female']['25-59']; ?></td>
                                    <td><?php echo $data['Female']['60 and above']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Charts
            // Service Types Chart
            const serviceTypeCtx = document.getElementById('serviceTypeChart').getContext('2d');
            const serviceTypeChart = new Chart(serviceTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($foot_traffic_data, 'support_type')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($foot_traffic_data, 'count')); ?>,
                        backgroundColor: [
                            'rgba(255, 234, 49, 0.91)',
                            'rgba(248, 34, 81, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
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
            
            // Daily Foot Traffic Chart
            const dailyTrafficCtx = document.getElementById('dailyTrafficChart').getContext('2d');
            const dailyTrafficChart = new Chart(dailyTrafficCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(function($item) { 
                        return date('j', strtotime($item['request_date'])); 
                    }, $daily_foot_traffic)); ?>,
                    datasets: [{
                        label: 'Daily Foot Traffic',
                        data: <?php echo json_encode(array_column($daily_foot_traffic, 'count')); ?>,
                        backgroundColor: '#0d6efd',
                        borderColor: '#0d6efd',
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
        });
    </script>
</body>
</html>
