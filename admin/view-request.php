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
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .status-badge {
            border-radius: 20px;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-progress {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-resolved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .timeline-item {
            border-left: 2px solid #e5e7eb;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #6b7280;
            margin-left: -7px;
            margin-top: 0.25rem;
        }
        
        .timeline-dot.active {
            background-color: #3b82f6;
        }
    </style>
</head>
<body>
    <?php include '../admin/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="container-fluid p-4">        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Service Request Details</h1>
            <div class="text-muted">
                <small><?php echo $_SESSION['username']; ?> | Admin</small>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="service-requests.php" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Service Requests
            </a>
        </div>

        <!-- Request Details Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Support Request #<?php echo $id; ?></h5>
                    <small class="text-muted">
                        Submitted: <?php echo date('F j, Y, g:i a', strtotime($request['date_requested'])); ?>
                    </small>
                </div>
                <div>
                    <?php
                    $status_class = 'status-pending';
                    if ($request['status'] === 'In Progress') {
                        $status_class = 'status-progress';
                    } elseif ($request['status'] === 'Resolved') {
                        $status_class = 'status-resolved';
                    } elseif ($request['status'] === 'Cancelled') {
                        $status_class = 'status-cancelled';
                    }
                    ?>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $request['status']; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Client Information -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3">Client Information</h6>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Client Name</label>
                        <div class="form-control-plaintext">
                            <?php echo htmlspecialchars($fullname); ?>
                            <small class="text-muted d-block">
                                <?php echo htmlspecialchars($request['gender']); ?> | Age: <?php echo $request['age']; ?>
                            </small>
                        </div>
                    </div>                    <div class="col-md-6">
                        <label class="form-label fw-medium">Agency/Organization</label>
                        <div class="form-control-plaintext"><?php echo htmlspecialchars($request['agency']); ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Location</label>
                        <div class="form-control-plaintext">
                            <?php echo !empty($location_parts) ? implode(', ', $location_parts) : 'Not specified'; ?>
                        </div>
                    </div>

                    <!-- Support Request Details -->
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-primary mb-3">Support Request Details</h6>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Support Type</label>
                        <div class="form-control-plaintext">
                            <div class="fw-medium"><?php echo htmlspecialchars($request['support_type']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($request['subject']); ?></small>
                        </div>
                    </div>

                    <?php if (!empty($request['message'])): ?>
                    <div class="col-12">
                        <label class="form-label fw-medium">Message</label>
                        <div class="form-control-plaintext">
                            <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($request['issue_description'])): ?>
                    <div class="col-12">
                        <label class="form-label fw-medium">Issue Description</label>
                        <div class="form-control-plaintext">
                            <?php echo nl2br(htmlspecialchars($request['issue_description'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($request['attachment'])): ?>
                    <div class="col-12">
                        <label class="form-label fw-medium">Attachment</label>
                        <div class="form-control-plaintext">
                            <a href="<?php echo '../' . $request['attachment']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-paperclip me-1"></i>
                                View Attachment
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Status Timeline -->
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-primary mb-3">Status Timeline</h6>
                        <div class="timeline">
                            <div class="timeline-item d-flex">
                                <div class="timeline-dot active flex-shrink-0"></div>
                                <div class="ms-3">
                                    <div class="fw-medium">Request Submitted</div>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y, g:i a', strtotime($request['date_requested'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <?php if (!empty($request['date_assisted'])): ?>
                            <div class="timeline-item d-flex">
                                <div class="timeline-dot active flex-shrink-0"></div>
                                <div class="ms-3">
                                    <div class="fw-medium">Request Assistance Started</div>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y, g:i a', strtotime($request['date_assisted'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($request['date_resolved'])): ?>
                            <div class="timeline-item d-flex">
                                <div class="timeline-dot active flex-shrink-0"></div>
                                <div class="ms-3">
                                    <div class="fw-medium">Request Resolved</div>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y, g:i a', strtotime($request['date_resolved'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($request['remarks'])): ?>
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-primary mb-3">Remarks</h6>
                        <div class="form-control-plaintext">
                            <?php echo nl2br(htmlspecialchars($request['remarks'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>