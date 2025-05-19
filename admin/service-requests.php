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
    <?php include '../admin/includes/sidebar.php'; ?>
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
