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

// Get current month and year
$current_month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$current_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month and year
if ($current_month < 1 || $current_month > 12) {
    $current_month = intval(date('m'));
}
if ($current_year < 2020 || $current_year > intval(date('Y'))) {
    $current_year = intval(date('Y'));
}

// Format date for SQL query
$start_date = sprintf('%04d-%02d-01', $current_year, $current_month);
$end_date = date('Y-m-t', strtotime($start_date)); // Last day of the month

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
    
    // Get comparison with previous month
    $prev_month = $current_month - 1;
    $prev_year = $current_year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }
    
    $prev_start_date = sprintf('%04d-%02d-01', $prev_year, $prev_month);
    $prev_end_date = date('Y-m-t', strtotime($prev_start_date));
    
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
$month_name = date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year));
$prev_month_name = date('F Y', mktime(0, 0, 0, $prev_month, 1, $prev_year));
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
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="flex items-center space-x-2">
                                <select name="month" id="month" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === $current_month ? 'selected' : ''; ?>>
                                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <select name="year" id="year" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <?php for ($i = intval(date('Y')); $i >= 2020; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i === $current_year ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    View
                                </button>
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
                                <p class="text-sm text-gray-500">For <?php echo $month_name; ?></p>
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
                                <p class="text-sm text-gray-500">vs <?php echo $prev_month_name; ?></p>
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
                                    $days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
                                    echo isset($total_foot_traffic) ? number_format($total_foot_traffic / $days_in_month, 1) : 0; 
                                    ?>
                                </p>
                                <p class="text-sm text-gray-500">Visitors per day</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Service Type Distribution</h3>
                        <div class="h-80">
                            <canvas id="serviceTypeChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Foot Traffic</h3>
                        <div class="h-80">
                            <canvas id="dailyTrafficChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detailed Table -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Foot Traffic Breakdown for <?php echo $month_name; ?>
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Detailed breakdown of facility and equipment usage.
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($foot_traffic_data)): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        No data available for this month.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($foot_traffic_data as $item): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['support_type']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $item['count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo number_format(($item['count'] / $total_foot_traffic) * 100, 1); ?>%
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