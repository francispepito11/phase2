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

// Initialize variables
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$records_per_page = 10;
$offset = ($current_page - 1) * $records_per_page;

// Get service requests from database
// First, prepare the WHERE conditions
$where = [];
if (!empty($status_filter)) {
    $where['status'] = $status_filter;
}

// Get tech support requests
try {
    if (!empty($search_term)) {
        $search_columns = ['client_name', 'agency', 'email', 'phone', 'support_type', 'issue_description'];
        $serviceRequests = search_records('tech_support_requests', $search_columns, $search_term, ['*'], 'date_requested DESC', $records_per_page, $offset);
        $total_records = count_records('tech_support_requests');
    } else {
        if (!empty($where)) {
            $serviceRequests = read_records('tech_support_requests', ['*'], $where, 'date_requested DESC', $records_per_page, $offset);
            $total_records = count_records('tech_support_requests', $where);
        } else {
            $serviceRequests = read_records('tech_support_requests', ['*'], [], 'date_requested DESC', $records_per_page, $offset);
            $total_records = count_records('tech_support_requests');
        }
    }
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    $serviceRequests = [];
    $total_records = 0;
}

// Calculate pagination
$total_pages = ceil($total_records / $records_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>        body {
            font-family: 'Inter', sans-serif;
        }
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        /* Make table cells more compact */
        .compact-table th, .compact-table td {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
        }
        /* Fix main content to prevent overlap */
        .main-content {
            height: 100vh;
            overflow-y: auto;
            max-width: 100%;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 flex-shrink-0">
            <div class="p-4">
                <h2 class="text-xl font-bold">DICT CMS</h2>
                <p class="text-sm text-blue-200">Admin Dashboard</p>
            </div>
            <nav class="mt-5">
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    Dashboard
                </a>
                <a href="service-requests.php" class="flex items-center px-4 py-3 bg-blue-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                    Service Requests
                </a>
                <a href="reports.php" class="flex items-center px-4 py-3 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Reports
                </a>
                <a href="users.php" class="flex items-center px-4 py-3 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                    </svg>
                    Users
                </a>
                <a href="settings.php" class="flex items-center px-4 py-3 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    Settings
                </a>
                <div class="border-t border-blue-700 my-4"></div>
                <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1H3zm1 2h10v10H4V5zm4.293 2.293a1 1 0 011.414 0l2 2a1 1 0 010 1.414l-2 2a1 1 0 01-1.414-1.414L9.586 10 8.293 8.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Logout
                </a>
            </nav>        </div>

        <!-- Main Content -->
        <div class="flex-1 main-content">
            <!-- Top Navigation -->
            <div class="bg-white shadow-md">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <h1 class="text-xl font-bold text-gray-800">Service Requests</h1>
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
                    <!-- Content Header -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Service Requests Management</h2>
                                <p class="mt-1 text-sm text-gray-500">View and manage technical support requests from clients.</p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <a href="../tech-support.php" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    New Request
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" name="search" id="search" class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Client, Agency, Email..." value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">All Statuses</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Resolved" <?php echo $status_filter === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                    Filter
                                </button>
                                <?php if (!empty($search_term) || !empty($status_filter)): ?>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Clear
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>                    <!-- Results Table -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Service Requests
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Showing <?php echo min($total_records, 1 + (($current_page - 1) * $records_per_page)); ?> to <?php echo min($total_records, $current_page * $records_per_page); ?> of <?php echo $total_records; ?> entries
                            </p>                        </div>
                        
                        <div class="table-container">
                            <table class="min-w-full divide-y divide-gray-200 compact-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agency</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Support Type</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (empty($serviceRequests)): ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            No service requests found.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php 
                                        $count = ($current_page - 1) * $records_per_page + 1;
                                        foreach ($serviceRequests as $request): 
                                            // Get region name
                                            $region_name = "";
                                            if (!empty($request['region_id'])) {
                                                $region = get_record_by_id('regions', $request['region_id']);
                                                if ($region) {
                                                    $region_name = $region['region_name'];
                                                }
                                            }
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $count++; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['client_name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['email']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['agency']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($request['phone']); ?></div>
                                            </td>                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($region_name); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($request['support_type']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($request['date_requested'])); ?>
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
                                                <?php elseif ($request['status'] === 'Cancelled'): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Cancelled
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="view-request.php?id=<?php echo $request['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                                <a href="edit-request.php?id=<?php echo $request['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            </td>
                                        </tr>                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing <span class="font-medium"><?php echo min($total_records, 1 + (($current_page - 1) * $records_per_page)); ?></span> to <span class="font-medium"><?php echo min($total_records, $current_page * $records_per_page); ?></span> of <span class="font-medium"><?php echo $total_records; ?></span> results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <?php if ($current_page > 1): ?>
                                        <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $current_page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($current_page < $total_pages): ?>
                                        <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Next</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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
</body>
</html>
