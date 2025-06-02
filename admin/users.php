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

// Show error message if exists
$error_message = '';
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Set page title
$page_title = "Client Management";

// Initialize variables
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$gender_filter = isset($_GET['gender']) ? sanitize_input($_GET['gender']) : '';
$records_per_page = 10;
$offset = ((int)$current_page - 1) * (int)$records_per_page;

// Get clients from database
try {
    $base_query = "SELECT c.*, 
                   r.region_name, r.region_code,
                   p.province_name,
                   d.district_name,
                   m.municipality_name,
                   TIMESTAMPDIFF(YEAR, c.birthdate, CURDATE()) as age
                   FROM clients c 
                   LEFT JOIN regions r ON c.region_id = r.id
                   LEFT JOIN provinces p ON c.province_id = p.id
                   LEFT JOIN districts d ON c.district_id = d.id
                   LEFT JOIN municipalities m ON c.municipality_id = m.id";
    
    $count_query = "SELECT COUNT(*) as total FROM clients c 
                    LEFT JOIN regions r ON c.region_id = r.id";
    
    $where_conditions = array();
    $params = array();
    
    if (!empty($search_term)) {
        $where_conditions[] = "(c.firstname LIKE ? OR c.surname LIKE ? OR c.client_name LIKE ? OR 
                              c.email LIKE ? OR c.phone LIKE ? OR c.agency LIKE ?)";
        $search_param = "%$search_term%";
        $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param, $search_param));
    }
    
    if (!empty($gender_filter)) {
        $where_conditions[] = "c.gender = ?";
        $params[] = $gender_filter;
    }
    
    if (!empty($where_conditions)) {
        $where_clause = " WHERE " . implode(" AND ", $where_conditions);
        $base_query .= $where_clause;
        $count_query .= $where_clause;
    }
    
    $base_query .= " ORDER BY c.id DESC LIMIT ? OFFSET ?";
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
    $clients = $result->fetch_all(MYSQLI_ASSOC);
    
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
    $clients = [];
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
        
        /* Action buttons styling */
        .btn-group .btn,
        .d-flex .btn {
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
            font-size: 0.75rem;
        }
        
        .d-flex .btn:hover,
        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-outline-info:hover {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: white;
        }
        
        .btn-outline-warning:hover {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        
        /* Actions column width */
        .table th:last-child,
        .table td:last-child {
            min-width: 120px;
            width: 120px;
        }
        
        /* Responsive design for action buttons */
        @media (max-width: 768px) {
            .d-flex.gap-1 {
                flex-direction: column;
                gap: 0.25rem !important;
            }
            
            .btn-group {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-group .btn {
                margin-bottom: 2px;
                margin-right: 0;
                width: 100%;
            }
            
            .table th:last-child,
            .table td:last-child {
                min-width: 100px;
                width: 100px;
            }
        }
        
        /* Icon-only view for very small screens */
        @media (max-width: 576px) {
            .d-flex.gap-1 {
                flex-direction: row;
                justify-content: center;
            }
            
            .btn-sm {
                padding: 0.25rem 0.4rem;
            }
            
            .table th:last-child,
            .table td:last-child {
                min-width: 100px;
                width: 100px;
            }
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
                        <h2 class="card-title h4 mb-1">Client Management</h2>
                        <p class="text-muted small mb-0">View and manage registered clients in the system.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="../index.php" target="_blank" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> New Client
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
                        <input type="text" name="search" id="search" class="form-control" placeholder="Name, Email, Phone, Agency..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <?php if (!empty($search_term) || !empty($gender_filter)): ?>
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
                <h5 class="card-title mb-0">Registered Clients</h5>
                <span class="text-muted small">
                    Showing <?php echo min((int)$total_records, 1 + ((int)$current_page - 1) * (int)$records_per_page); ?> to <?php echo min((int)$total_records, (int)$current_page * (int)$records_per_page); ?> of <?php echo (int)$total_records; ?> entries
                </span>
            </div>
            
            <div class="table-container">
                <table class="table table-hover table-striped compact-table mb-0">
                    <thead>                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Client</th>
                            <th scope="col">Gender</th>
                            <th scope="col">Age</th>
                            <th scope="col">Agency</th>
                            <th scope="col">Region</th>
                            <th scope="col">Location</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>                        <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No clients found.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $count = max(1, ((int)$current_page - 1) * (int)$records_per_page + 1);
                            foreach ($clients as $client): 
                                // Construct full name
                                $fullname = trim($client['firstname'] . ' ' . 
                                    ($client['middle_initial'] ? $client['middle_initial'] . ' ' : '') . 
                                    $client['surname']);
                            ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td>
                                    <div class="fw-medium"><?php echo htmlspecialchars($fullname); ?></div>
                                    <div class="small text-muted">
                                        Born: <?php echo date('M d, Y', strtotime($client['birthdate'])); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($client['gender']); ?></td>
                                <td><?php echo !empty($client['age']) ? (int)$client['age'] : ''; ?></td>
                                <td><?php echo htmlspecialchars($client['agency']); ?></td>                                <td>
                                    <?php 
                                    echo htmlspecialchars($client['region_name'] ?? $client['region']); 
                                    if (!empty($client['region_code'])) {
                                        echo ' (' . htmlspecialchars($client['region_code']) . ')';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <?php if ($client['municipality_name']): ?>
                                            <?php echo htmlspecialchars($client['municipality_name']); ?>
                                        <?php endif; ?>
                                        <?php if ($client['province_name']): ?>
                                            <br><?php echo htmlspecialchars($client['province_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-start">
                                        <button type="button" class="btn btn-sm btn-outline-info flex-fill" 
                                               onclick="viewClient(<?php echo $client['id']; ?>)"
                                               title="View client details"
                                               data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning flex-fill" 
                                               onclick="editClient(<?php echo $client['id']; ?>)"
                                               title="Edit client"
                                               data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" 
                                               onclick="deleteClient(<?php echo $client['id']; ?>)"
                                               title="Delete client"
                                               data-bs-toggle="tooltip">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <!-- Previous Button -->
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo max(1, $current_page - 1); ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($gender_filter) ? '&gender=' . urlencode($gender_filter) : ''; ?>">
                            Previous
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($gender_filter) ? '&gender=' . urlencode($gender_filter) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <!-- Next Button -->
                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo min($total_pages, $current_page + 1); ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?><?php echo !empty($gender_filter) ? '&gender=' . urlencode($gender_filter) : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function viewClient(clientId) {
            // Implement view client functionality
            alert('View client details for ID: ' + clientId);
            // You can redirect to a view page or open a modal
            // window.location.href = 'view-client.php?id=' + clientId;
        }
        
        function editClient(clientId) {
            // Implement edit client functionality
            alert('Edit client for ID: ' + clientId);
            // You can redirect to an edit page or open a modal
            // window.location.href = 'edit-client.php?id=' + clientId;
        }
        
        function deleteClient(clientId) {
            if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
                // Implement delete functionality
                alert('Delete client ID: ' + clientId);
                // You can make an AJAX call or redirect to a delete handler
                // window.location.href = 'delete-client.php?id=' + clientId;
            }
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });    </script>
</body>
</html>