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
require_once '../vendor/autoload.php'; // Add PhpSpreadsheet autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Show error message if exists
$error_message = '';
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Handle Excel Export
if (isset($_POST['export_excel'])) {
    $start_date = isset($_POST['export_start_date']) ? sanitize_input($_POST['export_start_date']) : '';
    $end_date = isset($_POST['export_end_date']) ? sanitize_input($_POST['export_end_date']) : '';
    
    // Validate dates
    if (empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "Please select both start and end dates for export.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if (!strtotime($start_date) || !strtotime($end_date)) {
        $_SESSION['error'] = "Please provide valid dates for export.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Add one day to end_date to make it inclusive
    $end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));
    
    if (strtotime($start_date) > strtotime($end_date)) {
        $_SESSION['error'] = "Start date cannot be after end date.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set report title and date range
    $sheet->setCellValue('A1', 'Service Requests Report');
    $sheet->setCellValue('A2', 'Period: ' . date('F d, Y', strtotime($start_date)) . ' to ' . date('F d, Y', strtotime($end_date . ' -1 day')));
    
    // Style the title and date range
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A2')->getFont()->setItalic(true);
    
    // Set headers starting from row 4
    $headers = ['Request ID', 'Date Requested', 'Client Name', 'Region', 'Sector/Agency', 
                'Support Type', 'Subject', 'Status', 'Issue Description'];
    $col = 'A';
    $headerRow = 4;
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headerRow, $header);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }

    // Style the headers
    $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFont()->setBold(true);

    // Fetch data within date range
    $export_query = "SELECT tsr.*, c.firstname, c.surname, c.middle_initial, c.gender, c.agency, 
                     r.region_name, r.region_code
                     FROM tech_support_requests tsr 
                     LEFT JOIN clients c ON tsr.client_id = c.id
                     LEFT JOIN regions r ON c.region_id = r.id
                     WHERE tsr.date_requested >= ? AND tsr.date_requested < ?
                     ORDER BY tsr.date_requested DESC";
      $stmt = $conn->prepare($export_query);
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $row = $headerRow + 1; // Start from row 5 (after title, date range, and headers)
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['id']);
        $sheet->setCellValue('B' . $row, $data['date_requested']);
        $sheet->setCellValue('C' . $row, $data['firstname'] . ' ' . $data['middle_initial'] . ' ' . $data['surname']);
        $sheet->setCellValue('D' . $row, $data['region_name']);
        $sheet->setCellValue('E' . $row, $data['agency']);
        $sheet->setCellValue('F' . $row, $data['support_type']);
        $sheet->setCellValue('G' . $row, $data['subject']);
        $sheet->setCellValue('H' . $row, $data['status']);
        $sheet->setCellValue('I' . $row, $data['issue_description']);
        $row++;
    }

    // Style the header row
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    
    // Create Excel file
    $writer = new Xlsx($spreadsheet);
    
    // Set headers for download    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="service_requests_' . $start_date . '_to_' . date('Y-m-d', strtotime($end_date . ' -1 day')) . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Save file to PHP output
    $writer->save('php://output');
    exit;
}

// Set page title
$page_title = "Service Requests";

// Initialize variables
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$records_per_page = 10;
$offset = ((int)$current_page - 1) * (int)$records_per_page;

// Get service requests from database
// First, prepare the WHERE conditions
$where = [];
if (!empty($status_filter)) {
    $where['status'] = $status_filter;
}

// Get tech support requests
try {
    $base_query = "SELECT tsr.*, c.firstname, c.surname, c.middle_initial, c.gender, c.agency, c.region, 
                   TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as age,
                   r.region_name, r.region_code
                   FROM tech_support_requests tsr 
                   LEFT JOIN clients c ON tsr.client_id = c.id
                   LEFT JOIN regions r ON c.region_id = r.id";
    
    $count_query = "SELECT COUNT(*) as total FROM tech_support_requests tsr 
                    LEFT JOIN clients c ON tsr.client_id = c.id";
    
    $where_conditions = array();
    $params = array();
    
    if (!empty($search_term)) {
        $where_conditions[] = "(c.firstname LIKE ? OR c.surname LIKE ? OR c.agency LIKE ? OR 
                              tsr.support_type LIKE ? OR tsr.subject LIKE ? OR tsr.issue_description LIKE ?)";
        $search_param = "%$search_term%";
        $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param, $search_param));
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "tsr.status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($where_conditions)) {
        $where_clause = " WHERE " . implode(" AND ", $where_conditions);
        $base_query .= $where_clause;
        $count_query .= $where_clause;
    }
    
    $base_query .= " ORDER BY tsr.date_requested DESC LIMIT ? OFFSET ?";
    $params[] = $records_per_page;
    $params[] = $offset;
    
    // Prepare and execute the main query
    $stmt = $conn->prepare($base_query);
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $serviceRequests = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get total count for pagination
    $stmt = $conn->prepare($count_query);
    if ($stmt === false) {
        throw new Exception($conn->error);
    }
    if (!empty($params)) {
        // Remove the last two parameters (LIMIT and OFFSET) for the count query
        array_pop($params);
        array_pop($params);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat("s", count($params)), ...$params);
        }
    }
    $stmt->execute();
    $count_result = $stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    $serviceRequests = [];
    $total_records = 0;
}

// Calculate pagination
$total_pages = ceil((int)$total_records / (int)$records_per_page);
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
        
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        
        /* Make table cells more compact */
        .compact-table th, .compact-table td {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Page Content -->
    <div class="container-fluid py-4">
        <!-- Content Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="card-title h4 mb-1">Service Requests Management</h2>
                        <p class="text-muted small mb-0">View and manage technical support requests from clients.</p>
                    </div>                    <div class="d-flex align-items-center gap-2">
                        <form method="post" class="d-flex align-items-center gap-2">
                            <div class="form-group">
                                <input type="date" name="export_start_date" class="form-control form-control-sm" required>
                            </div>
                            <div class="form-group">
                                <input type="date" name="export_end_date" class="form-control form-control-sm" required>
                            </div>
                            <button type="submit" name="export_excel" class="btn btn-primary btn-sm">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export to Excel
                            </button>
                        </form>
                        <a href="../tech-support.php" target="_blank" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> New Request
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Client, Agency, Email..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Resolved" <?php echo $status_filter === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <?php if (!empty($search_term) || !empty($status_filter)): ?>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-outline-secondary">
                            Clear
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Service Requests</h5>
                <span class="text-muted small">
                    Showing <?php echo min((int)$total_records, 1 + ((int)$current_page - 1) * (int)$records_per_page); ?> to <?php echo min((int)$total_records, (int)$current_page * (int)$records_per_page); ?> of <?php echo (int)$total_records; ?> entries
                </span>
            </div>
            
            <div class="table-container">
                <table class="table table-hover table-striped compact-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Client</th>
                            <th scope="col">Gender</th>
                            <th scope="col">Age</th>
                            <th scope="col">Sector</th>
                            <th scope="col">Region</th>
                            <th scope="col">Support Type</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($serviceRequests)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                No service requests found.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $count = max(1, ((int)$current_page - 1) * (int)$records_per_page + 1);
                            foreach ($serviceRequests as $request): 
                                // Construct full name
                                $fullname = trim($request['firstname'] . ' ' . 
                                    ($request['middle_initial'] ? $request['middle_initial'] . ' ' : '') . 
                                    $request['surname']);
                            ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <div class="fw-medium"><?php echo htmlspecialchars($fullname); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($request['subject']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($request['gender']); ?></td>
                                <td><?php echo !empty($request['age']) ? (int)$request['age'] : ''; ?></td>
                                <td><?php echo htmlspecialchars($request['agency']); ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($request['region_name'] ?? $request['region']); 
                                    if (!empty($request['region_code'])) {
                                        echo ' (' . htmlspecialchars($request['region_code']) . ')';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($request['support_type']); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($request['date_requested'])); ?>
                                    <?php if ($request['date_assisted']): ?>
                                        <div class="small text-muted">
                                            Assisted: <?php echo date('M d, Y', strtotime($request['date_assisted'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($request['status'] === 'Resolved'): ?>
                                        <span class="badge bg-success">Resolved</span>
                                    <?php elseif ($request['status'] === 'In Progress'): ?>
                                        <span class="badge bg-primary">In Progress</span>
                                    <?php elseif ($request['status'] === 'Cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary me-1">View</a>
                                    <a href="edit-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
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
   

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Sidebar Dropdown -->
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap JS
    if (typeof bootstrap !== 'undefined') {
        // Initialize all dropdowns
        var dropdownElementList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        if (dropdownElementList.length > 0) {
            dropdownElementList.forEach(function(element) {
                new bootstrap.Dropdown(element);
            });
        }
        
        // Initialize Tech Supports dropdown in sidebar
        var techSupportsToggle = document.querySelector('[data-bs-target="#techSupportsSubmenu"]');
        if (techSupportsToggle) {
            var techSupportsSubmenu = document.querySelector('#techSupportsSubmenu');
            if (techSupportsSubmenu) {
                // Create collapse instance if it doesn't exist
                var bsCollapse;
                try {
                    bsCollapse = bootstrap.Collapse.getInstance(techSupportsSubmenu);
                    if (!bsCollapse) {
                        bsCollapse = new bootstrap.Collapse(techSupportsSubmenu, {
                            toggle: false
                        });
                    }
                } catch (e) {
                    bsCollapse = new bootstrap.Collapse(techSupportsSubmenu, {
                        toggle: false
                    });
                }
                
                // Check if the current page is under Tech Supports
                var currentPage = '<?php echo basename($_SERVER["PHP_SELF"]); ?>';
                var techSupportPages = ['service-requests.php', 'support-summary.php', 'foot-tracking.php', 'view-request.php', 'edit-request.php'];
                
                if (techSupportPages.includes(currentPage)) {
                    // Show the submenu
                    techSupportsSubmenu.classList.add('show');
                }
                
                // Add click event listener
                techSupportsToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (techSupportsSubmenu.classList.contains('show')) {
                        techSupportsSubmenu.classList.remove('show');
                    } else {
                        techSupportsSubmenu.classList.add('show');
                    }
                });
            }
        }
    } else {
        console.error('Bootstrap JS not loaded');
    }
});
</script>
</body>
</html>
