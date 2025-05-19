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

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: service-requests.php');
    exit;
}

$id = (int)$_GET['id'];

// Get request details
$request = get_record_by_id('tech_support_requests', $id);

// If request not found, redirect to service requests
if (!$request) {
    header('Location: service-requests.php');
    exit;
}

// Get region, province, and municipality names
$region_name = '';
$province_name = '';
$municipality_name = '';

if (!empty($request['region_id'])) {
    $region = get_record_by_id('regions', $request['region_id']);
    if ($region) {
        $region_name = $region['region_name'];
    } else {
        error_log("Region not found for ID: " . $request['region_id']);
    }
}

if (!empty($request['province_id'])) {
    $province = get_record_by_id('provinces', $request['province_id']);
    if ($province) {
        $province_name = $province['province_name']; // Fixed key from 'name' to 'province_name'
    }
}

if (!empty($request['municipality_id'])) {
    $municipality = get_record_by_id('municipalities', $request['municipality_id']);
    if ($municipality) {
        $municipality_name = $municipality['municipality_name']; // Fixed key from 'name' to 'municipality_name'
    }
}

// Determine status class for badge
$status_class = 'bg-yellow-100 text-yellow-800';
if ($request['status'] === 'In Progress') {
    $status_class = 'bg-blue-100 text-blue-800';
} elseif ($request['status'] === 'Resolved') {
    $status_class = 'bg-green-100 text-green-800';
} elseif ($request['status'] === 'Cancelled') {
    $status_class = 'bg-red-100 text-red-800';
}

// Process status update if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = sanitize_input($_POST['status']);
    $resolution_notes = isset($_POST['resolution_notes']) ? sanitize_input($_POST['resolution_notes']) : '';
    
    $update_data = [
        'status' => $new_status,
    ];
    
    // Add resolution date if status is set to Resolved
    if ($new_status === 'Resolved') {
        $update_data['date_resolved'] = date('Y-m-d H:i:s');
        $update_data['remarks'] = $resolution_notes; // Using 'remarks' field instead of 'resolution_notes'
    } elseif ($new_status === 'In Progress' && empty($request['date_assisted'])) {
        $update_data['date_assisted'] = date('Y-m-d H:i:s');
    }
    
    // Debug info
    $debug_message = "Updating record ID: $id with data: " . print_r($update_data, true);
    error_log($debug_message);
    
    // Update the record
    $update_success = update_record('tech_support_requests', $id, $update_data);
    error_log("Update success: " . ($update_success ? 'Yes' : 'No'));
    
    if ($update_success) {
        // Refresh the page to reflect changes
        header('Location: view-request.php?id=' . $id . '&updated=1');
        exit;
    } else {
        // Debug message for update failure
        error_log("Failed to update tech support request status.");
    }
}

// Get success message if exists
$success_message = isset($_GET['updated']) && $_GET['updated'] == 1 ? 'Support request status updated successfully.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Support Request - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
<?php include '../admin/includes/sidebar.php'; ?>
<body class="bg-gray-100">
    
        <!-- Main content -->
        <div class="flex-1 main-content">
            <!-- Top bar -->
            <div class="bg-white shadow-sm">
                <div class="px-4 py-2 flex justify-between items-center">
                    <h1 class="text-xl font-semibold">Service Request Details</h1>
                    <div>
                        <p class="text-sm text-gray-500">
                            <?php echo $_SESSION['username']; ?> | Admin
                        </p>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <div class="p-6">
                <!-- Success Message -->
                <?php if (!empty($success_message)): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
                <?php endif; ?>

                <!-- Back button -->
                <div class="mb-6">
                    <a href="service-requests.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Back to Service Requests
                    </a>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Support Request #<?php echo $id; ?></h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Submitted: <?php echo date('F j, Y, g:i a', strtotime($request['date_requested'])); ?>
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_class; ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Client Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($request['client_name']); ?></dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($request['email']); ?></dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($request['phone']); ?></dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Agency/Organization</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($request['agency']); ?></dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php 
                                    $location_parts = array_filter([$region_name, $province_name, $municipality_name]);
                                    echo !empty($location_parts) ? implode(', ', $location_parts) : 'Not specified';
                                    ?>
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Support Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($request['support_type']); ?></dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Issue Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo nl2br(htmlspecialchars($request['issue_description'])); ?>
                                </dd>
                            </div>
                            <?php if (!empty($request['attachment'])): ?>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Attachment</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <a href="<?php echo '../' . $request['attachment']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                        View Attachment
                                    </a>
                                </dd>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Status Tracking -->
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Status Timeline</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                        <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                            <div class="w-0 flex-1 flex items-center">
                                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="ml-2 flex-1 w-0 truncate">
                                                    Request Submitted
                                                </span>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <span class="font-medium text-blue-600 hover:text-blue-500">
                                                    <?php echo date('F j, Y, g:i a', strtotime($request['date_requested'])); ?>
                                                </span>
                                            </div>
                                        </li>
                                        <?php if (!empty($request['date_assisted'])): ?>
                                        <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                            <div class="w-0 flex-1 flex items-center">
                                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="ml-2 flex-1 w-0 truncate">
                                                    Request Assistance Started
                                                </span>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <span class="font-medium text-blue-600 hover:text-blue-500">
                                                    <?php echo date('F j, Y, g:i a', strtotime($request['date_assisted'])); ?>
                                                </span>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (!empty($request['date_resolved'])): ?>
                                        <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                            <div class="w-0 flex-1 flex items-center">
                                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="ml-2 flex-1 w-0 truncate">
                                                    Request Resolved
                                                </span>
                                            </div>
                                            <div class="ml-4 flex-shrink-0">
                                                <span class="font-medium text-blue-600 hover:text-blue-500">
                                                    <?php echo date('F j, Y, g:i a', strtotime($request['date_resolved'])); ?>
                                                </span>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </dd>
                            </div>
                              <?php if (!empty($request['remarks'])): ?>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Resolution Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo nl2br(htmlspecialchars($request['remarks'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
                
                <!-- Update Status Form -->
                <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Update Request Status</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <form action="view-request.php?id=<?php echo $id; ?>" method="post">
                            <input type="hidden" name="action" value="update_status">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="Pending" <?php echo $request['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $request['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Resolved" <?php echo $request['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="Cancelled" <?php echo $request['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="resolution-notes-container" id="resolution-notes-container" style="display: none;">
                                    <label for="resolution_notes" class="block text-sm font-medium text-gray-700">Resolution Notes</label>
                                    <textarea id="resolution_notes" name="resolution_notes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500 border border-gray-300 rounded-md"><?php echo isset($request['remarks']) ? htmlspecialchars($request['remarks']) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide resolution notes based on status
        const statusSelect = document.getElementById('status');
        const resolutionNotesContainer = document.getElementById('resolution-notes-container');
        
        function toggleResolutionNotes() {
            if (statusSelect.value === 'Resolved') {
                resolutionNotesContainer.style.display = 'block';
            } else {
                resolutionNotesContainer.style.display = 'none';
            }
        }
        
        // Initialize on page load
        toggleResolutionNotes();
        
        // Add event listener for status changes
        statusSelect.addEventListener('change', toggleResolutionNotes);
    </script>
</body>
</html>