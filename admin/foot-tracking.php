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

// Get current month and year
//$current_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
//$current_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month and year
//if ($current_month < 1 || $current_month > 12) {
//    $current_month = intval(date('m'));
//}
//if ($current_year < 2020 || $current_year > intval(date('Y'))) {
//    $current_year = intval(date('Y'));
//}

// Format date for SQL query
//$start_date = sprintf('%04d-%02d-01', $current_year, $current_month);
//$end_date = date('Y-m-t', strtotime($start_date)); // Last day of the month

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
    
    // Get foot traffic by age group and gender per region
    $age_gender_data = [];
    try {
        $sql = "SELECT 
                r.region_name, 
                SUM(CASE WHEN age < 18 AND gender = 'Male' THEN 1 ELSE 0 END) as youth_male,
                SUM(CASE WHEN age < 18 AND gender = 'Female' THEN 1 ELSE 0 END) as youth_female,
                SUM(CASE WHEN age BETWEEN 18 AND 59 AND gender = 'Male' THEN 1 ELSE 0 END) as adult_male,
                SUM(CASE WHEN age BETWEEN 18 AND 59 AND gender = 'Female' THEN 1 ELSE 0 END) as adult_female,
                SUM(CASE WHEN age > 60 AND gender = 'Male' THEN 1 ELSE 0 END) as senior_male,
                SUM(CASE WHEN age > 60 AND gender = 'Female' THEN 1 ELSE 0 END) as senior_female
            FROM tech_support_requests tsr
            LEFT JOIN regions r ON tsr.region_id = r.id
            WHERE tsr.date_requested BETWEEN ? AND ?
            GROUP BY r.region_name 
            ORDER BY r.region_name";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $age_gender_data[] = $row;
        }
        
        $stmt->close();
        
        // Get monthly counts of male and female clients per region (without support type)
        $monthly_gender_data = [];
        $sql = "SELECT 
            r.region_name, 
            SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male_count,
            SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female_count
        FROM tech_support_requests tsr
        LEFT JOIN regions r ON tsr.region_id = r.id
        WHERE tsr.date_requested BETWEEN ? AND ? 
        GROUP BY r.region_name
        ORDER BY r.region_name";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $monthly_gender_data[] = $row;
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
    
    // Get comparison with previous month
    //$prev_month = $current_month - 1;
    //$prev_year = $current_year;
    //if ($prev_month < 1) {
    //    $prev_month = 12;
    //    $prev_year--;
    //}
    
    //$prev_start_date = sprintf('%04d-%02d-01', $prev_year, $prev_month);
    //$prev_end_date = date('Y-m-t', strtotime($prev_start_date));
    
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
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Get month name
//$month_name = date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year));
//$prev_month_name = date('F Y', mktime(0, 0, 0, $prev_month, 1, $prev_year));
// Get semester name
$semester_name = $current_semester === 1 ? "First Semester (January-June) " . $current_year : "Second Semester (July-December) " . $current_year;
$prev_semester = $current_semester === 1 ? 2 : 1;
$prev_year = $current_semester === 1 ? $current_year - 1 : $current_year;
$prev_semester_name = $prev_semester === 1 ? "First Semester (January-June) " . $prev_year : "Second Semester (July-December) " . $prev_year;
$month_name = $semester_name;
$prev_month_name = $prev_semester_name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foot Traffic Tracking - DICT Client Management System</title>
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
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 main-content">
        <!-- Top Navigation -->
        <div class="bg-white shadow-md">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-800">Foot Traffic Tracking</h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="ml-4 flex items-center md:ml-6">
                            <div class="relative">
                                <div class="flex items-center">
                                    <span class="text-gray-700 text-sm mr-2">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
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
                <!-- Month Selection -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Foot Traffic Analysis</h2>
                            <p class="mt-1 text-sm text-gray-500">Track usage of ICT equipment and facilities by month.</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <!-- Replace the month selection form with semester selection -->
                            <form id="semesterForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="flex items-center space-x-2">
                                <select name="semester" id="semester" onchange="document.getElementById('semesterForm').submit();" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option value="1" <?php echo $current_semester === 1 ? 'selected' : ''; ?>>First Semester (Jan-Jun)</option>
                                    <option value="2" <?php echo $current_semester === 2 ? 'selected' : ''; ?>>Second Semester (Jul-Dec)</option>
                                </select>
                                <select name="year" id="year" onchange="document.getElementById('semesterForm').submit();" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
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

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Total Foot Traffic</h3>
                                <p class="text-3xl font-bold text-gray-700"><?php echo isset($total_foot_traffic) ? $total_foot_traffic : 0; ?></p>
                                <p class="text-sm text-gray-500">For <?php echo $semester_name; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Monthly Change</h3>
                                <p class="text-3xl font-bold <?php echo $percent_change >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $percent_change >= 0 ? '+' : ''; ?><?php echo number_format($percent_change, 1); ?>%
                                </p>
                                <p class="text-sm text-gray-500">vs <?php echo $prev_semester_name; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Daily Average</h3>
                                <p class="text-3xl font-bold text-gray-700">
                                    <?php 
                                    $days_in_month = date('t', mktime(0, 0, 0, $current_semester == 1 ? 1 : 7, 1, $current_year));
                                    echo isset($total_foot_traffic) ? number_format($total_foot_traffic / $days_in_month, 1) : 0; 
                                    ?>
                                </p>
                                <p class="text-sm text-gray-500">Visitors per day</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                                        
                <!-- Age Group and Gender Table -->
                <div class="bg-white rounded-lg shadow-md mt-6">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Foot Traffic by Age Group and Gender per Region for <?php echo $semester_name; ?>
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Breakdown of visitors by age group (Youth, Adults, Seniors) and gender.
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                    <th scope="col" colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Youth (<18)</th>
                                    <th scope="col" colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Adults (18-59)</th>
                                    <th scope="col" colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Seniors (>60)</th>
                                </tr>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Male</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Female</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Male</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Female</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Male</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Female</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($age_gender_data)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        No data available for this semester.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($age_gender_data as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['region_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['youth_male']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['youth_female']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['adult_male']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['adult_female']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['senior_male']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['senior_female']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Monthly Gender Count by Support Type Table -->
                
                <div class="bg-white rounded-lg shadow-md mt-6">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Client Count by Gender per Region for <?php echo $semester_name; ?>
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Breakdown of male and female clients using DICT support services by region.
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Male</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Female</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($monthly_gender_data)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        No data available for this semester.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($monthly_gender_data as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['region_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['male_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <?php echo $item['female_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 font-medium">
                                            <?php echo $item['male_count'] + $item['female_count']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

    <!-- JavaScript for Charts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Service Types Chart
            const serviceTypeCtx = document.getElementById('serviceTypeChart').getContext('2d');
            const serviceTypeChart = new Chart(serviceTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($foot_traffic_data, 'support_type')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($foot_traffic_data, 'count')); ?>,
                        backgroundColor: [
                            '#10B981', '#F59E0B', '#4F46E5', '#EF4444'
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
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgba(16, 185, 129, 1)',
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
