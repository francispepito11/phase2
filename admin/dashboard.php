<?php
// Start session for authentication
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database connection
include_once '../includes/db_connect.php';

// Fetch service requests from the database
$serviceRequests = [];
$query = "SELECT tsr.*, 
          r.region_name, p.province_name, d.district_name, m.municipality_name
          FROM tech_support_requests tsr
          LEFT JOIN regions r ON tsr.region_id = r.id
          LEFT JOIN provinces p ON tsr.province_id = p.id
          LEFT JOIN districts d ON tsr.district_id = d.id
          LEFT JOIN municipalities m ON tsr.municipality_id = m.id
          ORDER BY tsr.date_requested DESC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $serviceRequests[] = [
            'id' => $row['id'],
            'client_name' => $row['client_name'],
            'agency' => $row['agency'],
            'region' => $row['region_name'] ?? 'Unknown',
            'province' => $row['province_name'] ?? 'Unknown',
            'district' => $row['district_name'] ?? 'Unknown',
            'municipality' => $row['municipality_name'] ?? 'Unknown',
            'service_type' => $row['support_type'],
            'description' => $row['issue_description'],
            'date_requested' => date('Y-m-d', strtotime($row['date_requested'])),
            'date_assisted' => $row['date_assisted'] ? date('Y-m-d', strtotime($row['date_assisted'])) : '',
            'date_resolved' => $row['date_resolved'] ? date('Y-m-d', strtotime($row['date_resolved'])) : '',
            'assisted_by' => 'Staff', // This would ideally come from users table based on assisted_by_id
            'status' => $row['status'],
            'remarks' => $row['remarks']
        ];
    }
}

// Get statistics
$totalRequests = count($serviceRequests);

// Use SQL COUNT for more efficient statistics
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
FROM tech_support_requests";

$statsResult = $conn->query($statsQuery);
if ($statsResult && $statsResult->num_rows > 0) {
    $stats = $statsResult->fetch_assoc();
    $totalRequests = $stats['total'];
    $pendingRequests = $stats['pending'];
    $inProgressRequests = $stats['in_progress'];
    $resolvedRequests = $stats['resolved'];
} else {
    // Fallback to PHP counting if SQL query fails
    $pendingRequests = count(array_filter($serviceRequests, function($req) {
        return $req['status'] === 'Pending';
    }));
    $inProgressRequests = count(array_filter($serviceRequests, function($req) {
        return $req['status'] === 'In Progress';
    }));
    $resolvedRequests = count(array_filter($serviceRequests, function($req) {
        return $req['status'] === 'Resolved';
    }));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<?php include '../admin/includes/sidebar.php'; ?>
<body class="bg-gray-100">
    

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <div class="bg-white shadow-sm">
                <div class="flex justify-between items-center py-4 px-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                    <div class="flex items-center">
                        <span class="mr-2 text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                        <button class="relative p-1 rounded-full text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-medium">Total Requests</h2>
                                <p class="text-3xl font-semibold text-gray-800"><?php echo $totalRequests; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-medium">Pending</h2>
                                <p class="text-3xl font-semibold text-gray-800"><?php echo $pendingRequests; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-medium">In Progress</h2>
                                <p class="text-3xl font-semibold text-gray-800"><?php echo $inProgressRequests; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-gray-600 text-sm font-medium">Resolved</h2>
                                <p class="text-3xl font-semibold text-gray-800"><?php echo $resolvedRequests; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Service Requests -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium text-gray-900">Recent Service Requests</h2>
                            <a href="service-requests.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Client
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Agency
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Service Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date Requested
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($serviceRequests as $request): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['client_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['region']); ?>, <?php echo htmlspecialchars($request['province']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['agency']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['service_type']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['date_requested']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($request['status'] === 'Resolved'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Resolved
                                            </span>
                                        <?php elseif ($request['status'] === 'In Progress'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                In Progress
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="view-request.php?id=<?php echo $request['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="edit_service.php?id=<?php echo $request['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Service Type Distribution -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Service Type Distribution</h2>
                        </div>
                        <div class="p-6">
                            <div class="h-64 flex items-center justify-center">
                                <p class="text-gray-500 text-sm">Chart visualization would be displayed here</p>
                                <!-- In a real application, you would use a charting library like Chart.js -->
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Regional Distribution</h2>
                        </div>
                        <div class="p-6">
                            <div class="h-64 flex items-center justify-center">
                                <p class="text-gray-500 text-sm">Chart visualization would be displayed here</p>
                                <!-- In a real application, you would use a charting library like Chart.js -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>