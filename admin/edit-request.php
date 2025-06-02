<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once '../includes/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: service-requests.php');
    exit();
}

$id = (int)$_GET['id'];

// Get request and client details with proper joins for location
$stmt = $conn->prepare("
    SELECT tsr.*, 
           c.firstname, c.surname, c.middle_initial, c.email, c.phone, c.agency, c.gender, c.birthdate,
           c.region, c.region_id, c.province_id, c.district_id, c.municipality_id,
           r.region_name, r.region_code,
           p.province_name, p.province_code,
           d.district_name, d.district_code,
           m.municipality_name, m.municipality_code,
           TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as age
    FROM tech_support_requests tsr
    JOIN clients c ON tsr.client_id = c.id
    LEFT JOIN regions r ON c.region_id = r.id
    LEFT JOIN provinces p ON c.province_id = p.id
    LEFT JOIN districts d ON c.district_id = d.id
    LEFT JOIN municipalities m ON c.municipality_id = m.id
    WHERE tsr.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

// If request not found, redirect
if (!$request) {
    header('Location: service-requests.php');
    exit();
}

// Get support types from service_types table
$supportTypes = [];
$stmt = $conn->query("SELECT service_name FROM service_types WHERE is_active = 1 ORDER BY service_name");
while ($row = $stmt->fetch_assoc()) {
    $supportTypes[] = $row['service_name'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status']);
    $remarks = trim($_POST['remarks']);
    $date_assisted = null;
    $date_resolved = null;
    
    // Automatically set timestamps based on status changes
    if ($status == 'In Progress' && empty($request['date_assisted'])) {
        $date_assisted = date('Y-m-d H:i:s');
    } elseif ($status == 'Resolved') {
        if (empty($request['date_resolved'])) {
            $date_resolved = date('Y-m-d H:i:s');
        }
        if (empty($request['date_assisted'])) {
            $date_assisted = date('Y-m-d H:i:s');
        }
    }
    
    // Update the request
    $stmt = $conn->prepare("
        UPDATE tech_support_requests 
        SET status = ?,
            remarks = ?,
            date_assisted = COALESCE(?, date_assisted),
            date_resolved = COALESCE(?, date_resolved),
            updated_at = CURRENT_TIMESTAMP,
            assisted_by_id = ?
        WHERE id = ?
    ");
    
    $assisted_by_id = $_SESSION['user_id'] ?? $_SESSION['username'] ?? null;
    $stmt->bind_param("sssssi", $status, $remarks, $date_assisted, $date_resolved, $assisted_by_id, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Support request updated successfully.';
        header('Location: service-requests.php?updated=1');
        exit();
    } else {
        $_SESSION['error_message'] = 'Failed to update support request. Please try again.';
    }
}

// Get location details - no need for additional query as we already have the data
$location = [
    'region_name' => $request['region_name'] ?? '',
    'province_name' => $request['province_name'] ?? '',
    'district_name' => $request['district_name'] ?? '',
    'municipality_name' => $request['municipality_name'] ?? ''
];

// Format full client name
$fullname = trim($request['firstname'] . ' ' . 
    ($request['middle_initial'] ? $request['middle_initial'] . ' ' : '') . 
    $request['surname']);

// Get success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Support Request - DICT Client Management System</title>
    
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
        
        .form-control, .form-select {
            border-radius: 6px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }
          .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .badge {
            font-size: 0.875rem;
        }
        
        .form-control:read-only {
            background-color: #f8f9fa;
            border-color: #e9ecef;
        }
        
        .text-muted {
            font-size: 0.825rem;
        }
    </style>
</head>
<body>
    <?php include '../admin/includes/sidebar.php'; ?>
    
    <!-- Main Content -->    <div class="container-fluid p-4">
        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Service Request #<?php echo $id; ?></h1>
            <div class="text-muted">
                <small>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | Admin</small>
            </div>        </div>

        <!-- Edit Form Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Request Details</h5>
                    <small class="text-muted">
                        Submitted: <?php echo date('F j, Y, g:i a', strtotime($request['date_requested'])); ?>
                        <?php if (!empty($request['date_assisted'])): ?>
                            | Assisted: <?php echo date('F j, Y, g:i a', strtotime($request['date_assisted'])); ?>
                        <?php endif; ?>
                        <?php if (!empty($request['date_resolved'])): ?>
                            | Resolved: <?php echo date('F j, Y, g:i a', strtotime($request['date_resolved'])); ?>
                        <?php endif; ?>
                    </small>
                </div>
                <div>
                    <?php
                    $status_class = 'bg-warning text-dark';
                    if ($request['status'] === 'In Progress') {
                        $status_class = 'bg-primary';
                    } elseif ($request['status'] === 'Resolved') {
                        $status_class = 'bg-success';
                    } elseif ($request['status'] === 'Cancelled') {
                        $status_class = 'bg-danger';
                    }
                    ?>
                    <span class="badge <?php echo $status_class; ?> px-3 py-2">
                        <?php echo htmlspecialchars($request['status']); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>">
                    <div class="row g-3">
                        <!-- Client Information Section -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-3">Client Information</h6>
                        </div>
                          <div class="col-md-6">
                            <label class="form-label">Client Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($request['gender']); ?> | Age: <?php echo $request['age']; ?> | 
                                Born: <?php echo date('F j, Y', strtotime($request['birthdate'])); ?>
                            </small>
                        </div>                        <div class="col-md-6">
                            <label class="form-label">Agency</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($request['agency']); ?>" readonly>
                        </div>

                        <!-- Location Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary mb-3">Location Information</h6>
                        </div>                        <div class="col-md-6">
                            <label class="form-label">Region</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($location['region_name'] ?: $request['region']); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Province</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($location['province_name'] ?: 'Not specified'); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">District</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($location['district_name'] ?: 'Not specified'); ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Municipality/City</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($location['municipality_name'] ?: 'Not specified'); ?>" readonly>
                        </div>

                        <!-- Support Request Details Section -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary mb-3">Support Request Details</h6>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Support Type</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($request['support_type']); ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($request['subject']); ?>" readonly>
                        </div>                        <div class="col-12">
                            <label class="form-label">Issue Description/Message</label>
                            <textarea class="form-control" rows="4" readonly><?php echo htmlspecialchars($request['message'] ?: $request['issue_description'] ?: 'No description provided'); ?></textarea>
                        </div>

                        <!-- Status Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary mb-3">Status Information</h6>
                        </div>                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="Pending" <?php echo $request['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo $request['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Resolved" <?php echo $request['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="Cancelled" <?php echo $request['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <small class="text-muted">Select the current status of this request</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Assisted By</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($request['assisted_by_id'] ?: 'Not assigned'); ?>" readonly>
                            <small class="text-muted">Will be updated when status changes</small>
                        </div>                        <div class="col-12">
                            <label for="remarks" class="form-label">Remarks/Notes</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="4" placeholder="Add any remarks, notes, or resolution details..."><?php echo htmlspecialchars($request['remarks'] ?: ''); ?></textarea>
                            <small class="text-muted">Include any important notes, actions taken, or resolution details</small>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="service-requests.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Cancel & Return
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Update Request
                        </button>
                    </div>
                </form>
            </div>        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const remarksContainer = document.getElementById('remarks').parentElement;
            
            // Show appropriate help text based on status
            function updateStatusHelp() {
                const statusValue = statusSelect.value;
                const helpText = statusSelect.parentElement.querySelector('.text-muted');
                
                switch(statusValue) {
                    case 'Pending':
                        helpText.textContent = 'Request is waiting to be processed';
                        break;
                    case 'In Progress':
                        helpText.textContent = 'Request is currently being worked on';
                        break;
                    case 'Resolved':
                        helpText.textContent = 'Request has been completed successfully';
                        break;
                    case 'Cancelled':
                        helpText.textContent = 'Request has been cancelled';
                        break;
                }
            }
            
            statusSelect.addEventListener('change', updateStatusHelp);
            updateStatusHelp(); // Initial state
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert && !alert.classList.contains('d-none')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
    </script>
</body>
</html>
