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

// Set page title
$page_title = "Dashboard";

// Fetch service requests from the database
$serviceRequests = [];
$query = "SELECT tsr.*, 
          r.region_name, p.province_name, d.district_name, m.municipality_name
          FROM tech_support_requests tsr
          LEFT JOIN regions r ON tsr.region_id = r.id
          LEFT JOIN provinces p ON tsr.province_id = p.id
          LEFT JOIN districts d ON tsr.district_id = d.id
          LEFT JOIN municipalities m ON tsr.municipality_id = m.id
          ORDER BY tsr.date_requested DESC
          LIMIT 10"; // Limit to 10 most recent requests

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
    $totalRequests = count($serviceRequests);
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
        
        /* Stats Card Styles */
        .stats-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.5rem;
        }
        
        .stats-title {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .stats-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0;
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .table-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0;
        }
        
        .table-link {
            font-size: 0.875rem;
            color: #0d6efd;
            text-decoration: none;
        }
        
        .table-link:hover {
            text-decoration: underline;
        }
        
        /* Status Badge Styles */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 9999px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-resolved {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Page Content -->
    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <h6 class="stats-title">Total Requests</h6>
                            <p class="stats-value"><?php echo $totalRequests; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <h6 class="stats-title">Pending</h6>
                            <p class="stats-value"><?php echo $pendingRequests; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div>
                            <h6 class="stats-title">In Progress</h6>
                            <p class="stats-value"><?php echo $inProgressRequests; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="stats-title">Resolved</h6>
                            <p class="stats-value"><?php echo $resolvedRequests; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Service Requests -->
        <div class="table-container">
            <div class="table-header">
                <h5 class="table-title">Recent Service Requests</h5>
                <a href="service-requests.php" class="table-link">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Agency</th>
                            <th>Service Type</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($serviceRequests)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No service requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($serviceRequests as $request): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?php echo htmlspecialchars($request['client_name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($request['region']); ?>, <?php echo htmlspecialchars($request['province']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['agency']); ?></td>
                                    <td><?php echo htmlspecialchars($request['service_type']); ?></td>
                                    <td><?php echo htmlspecialchars($request['date_requested']); ?></td>
                                    <td>
                                        <?php if ($request['status'] === 'Resolved'): ?>
                                            <span class="status-badge status-resolved">Resolved</span>
                                        <?php elseif ($request['status'] === 'In Progress'): ?>
                                            <span class="status-badge status-progress">In Progress</span>
                                        <?php else: ?>
                                            <span class="status-badge status-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary me-1">View</a>
                                        <a href="edit_service.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Service Type Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;" class="d-flex align-items-center justify-content-center">
                            <p class="text-muted mb-0">Chart visualization would be displayed here</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Regional Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;" class="d-flex align-items-center justify-content-center">
                            <p class="text-muted mb-0">Chart visualization would be displayed here</p>
                        </div>
                    </div>
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
    
    <!-- Include Page Wrapper End -->
    <?php include 'includes/page-wrapper.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Sidebar Dropdown -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all Bootstrap dropdowns and collapses
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        var collapseElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="collapse"]'));
        var collapseList = collapseElementList.map(function(collapseToggleEl) {
            return new bootstrap.Collapse(collapseToggleEl.getAttribute('data-bs-target') || collapseToggleEl.getAttribute('href'), {
                toggle: false
            });
        });
        
        // Manually handle Tech Supports dropdown
        var techSupportsToggle = document.querySelector('[data-bs-target="#techSupportsSubmenu"]');
        if (techSupportsToggle) {
            techSupportsToggle.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('data-bs-target') || this.getAttribute('href'));
                if (target) {
                    if (target.classList.contains('show')) {
                        bootstrap.Collapse.getInstance(target).hide();
                    } else {
                        bootstrap.Collapse.getInstance(target).show();
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
