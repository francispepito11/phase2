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

// Get request details with client information
$sql = "SELECT tsr.*, 
        c.firstname, c.surname, c.middle_initial, c.email, c.phone,
        c.agency, c.gender, c.birthdate, 
        r.region_name, r.region_code,
        p.province_name, p.province_code,
        d.district_name, d.district_code,
        m.municipality_name, m.municipality_code,
        TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as age
        FROM tech_support_requests tsr 
        LEFT JOIN clients c ON tsr.client_id = c.id
        LEFT JOIN regions r ON c.region_id = r.id
        LEFT JOIN provinces p ON c.province_id = p.id
        LEFT JOIN districts d ON c.district_id = d.id
        LEFT JOIN municipalities m ON c.municipality_id = m.id
        WHERE tsr.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

// If request not found, redirect to service requests
if (!$request) {
    header('Location: service-requests.php');
    exit;
}

// Construct full name
$fullname = trim($request['firstname'] . ' ' . 
    ($request['middle_initial'] ? $request['middle_initial'] . ' ' : '') . 
    $request['surname']);

// Format location information
$location_parts = array_filter([
    $request['region_name'] . (!empty($request['region_code']) ? ' (' . $request['region_code'] . ')' : ''),
    $request['province_name'],
    $request['district_name'],
    $request['municipality_name']
]);

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
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo htmlspecialchars($fullname); ?>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($request['gender']); ?> | 
                                        Age: <?php echo $request['age']; ?>
                                    </div>
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Contact Info</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div>Email: <?php echo htmlspecialchars($request['email']); ?></div>
                                    <div>Phone: <?php echo htmlspecialchars($request['phone']); ?></div>
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Agency/Organization</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($request['agency']); ?></dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo !empty($location_parts) ? implode(', ', $location_parts) : 'Not specified'; ?>
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Support Request</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="font-medium"><?php echo htmlspecialchars($request['support_type']); ?></div>
                                    <div class="text-gray-500"><?php echo htmlspecialchars($request['subject']); ?></div>
                                </dd>
                            </div>
                            <?php if (!empty($request['message'])): ?>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Message</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($request['issue_description'])): ?>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Issue Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo nl2br(htmlspecialchars($request['issue_description'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>
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
                                <dt class="text-sm font-medium text-gray-500">Remarks</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <?php echo nl2br(htmlspecialchars($request['remarks'])); ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>