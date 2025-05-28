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

// Set up available years (2021 to current year)
$currentYear = date('Y');
$availableYears = range($currentYear, 2021);

// Get years that have data
$yearsWithDataQuery = "SELECT DISTINCT YEAR(date_requested) as year 
                      FROM tech_support_requests 
                      ORDER BY year DESC";
$yearsWithDataResult = $conn->query($yearsWithDataQuery);
$yearsWithData = [];
if ($yearsWithDataResult && $yearsWithDataResult->num_rows > 0) {
    while ($row = $yearsWithDataResult->fetch_assoc()) {
        $yearsWithData[] = (int)$row['year'];
    }
}

// Set selected year (default to current year if not specified)
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
if (!in_array($selectedYear, $availableYears) && !empty($availableYears)) {
    $selectedYear = $availableYears[0];
}

// Fetch unique service types from tech_support_requests table
$serviceTypesQuery = "SELECT DISTINCT support_type FROM tech_support_requests ORDER BY support_type";
$serviceTypesResult = $conn->query($serviceTypesQuery);
$serviceTypes = [];
if ($serviceTypesResult && $serviceTypesResult->num_rows > 0) {
    while ($row = $serviceTypesResult->fetch_assoc()) {
        $serviceTypes[] = $row['support_type'];
    }
}

// Fetch first semester (January to June) service summary
$firstSemesterQuery = "SELECT 
    support_type,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
FROM tech_support_requests 
WHERE YEAR(date_requested) = ?
AND MONTH(date_requested) BETWEEN 1 AND 6
GROUP BY support_type
ORDER BY total_requests DESC";

$stmt = $conn->prepare($firstSemesterQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$firstSemesterResult = $stmt->get_result();
$firstSemesterSummary = [];
if ($firstSemesterResult && $firstSemesterResult->num_rows > 0) {
    while ($row = $firstSemesterResult->fetch_assoc()) {
        $firstSemesterSummary[$row['support_type']] = $row;
    }
}

// Fetch second semester (July to December) service summary
$secondSemesterQuery = "SELECT 
    support_type,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
FROM tech_support_requests 
WHERE YEAR(date_requested) = ?
AND MONTH(date_requested) BETWEEN 7 AND 12
GROUP BY support_type
ORDER BY total_requests DESC";

$stmt = $conn->prepare($secondSemesterQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$secondSemesterResult = $stmt->get_result();
$secondSemesterSummary = [];
if ($secondSemesterResult && $secondSemesterResult->num_rows > 0) {
    while ($row = $secondSemesterResult->fetch_assoc()) {
        $secondSemesterSummary[$row['support_type']] = $row;
    }
}

// Get total requests for each semester
$semesterTotalsQuery = "SELECT 
    CASE 
        WHEN MONTH(date_requested) BETWEEN 1 AND 6 THEN '1st Semester'
        ELSE '2nd Semester'
    END as semester,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
FROM tech_support_requests
WHERE YEAR(date_requested) = $selectedYear
GROUP BY 
    CASE 
        WHEN MONTH(date_requested) BETWEEN 1 AND 6 THEN '1st Semester'
        ELSE '2nd Semester'
    END";

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
FROM tech_support_requests
WHERE YEAR(date_requested) = ?";

$stmt = $conn->prepare($statsQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$statsResult = $stmt->get_result();
if ($statsResult && $statsResult->num_rows > 0) {
    $stats = $statsResult->fetch_assoc();
    $totalRequests = $stats['total'];
    $pendingRequests = $stats['pending'];
    $inProgressRequests = $stats['in_progress'];
    $resolvedRequests = $stats['resolved'];
} else {
    $totalRequests = 0;
    $pendingRequests = 0;
    $inProgressRequests = 0;
    $resolvedRequests = 0;
}

// Get monthly trends data for line chart
$monthlyTrendsQuery = "SELECT 
    MONTH(date_requested) as month,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
FROM tech_support_requests
WHERE YEAR(date_requested) = ?
GROUP BY MONTH(date_requested)
ORDER BY MONTH(date_requested)";

$stmt = $conn->prepare($monthlyTrendsQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$monthlyTrendsResult = $stmt->get_result();
$monthlyData = array_fill(1, 12, ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0]);

if ($monthlyTrendsResult && $monthlyTrendsResult->num_rows > 0) {
    while ($row = $monthlyTrendsResult->fetch_assoc()) {
        $monthlyData[$row['month']] = [
            'total' => (int)$row['total_requests'],
            'pending' => (int)$row['pending'],
            'in_progress' => (int)$row['in_progress'],
            'resolved' => (int)$row['resolved']
        ];
    }
}

// Get service type distribution data for chart
$serviceDistributionQuery = "SELECT 
    support_type, 
    COUNT(*) as request_count,
    ROUND((COUNT(*) * 100.0) / (SELECT COUNT(*) FROM tech_support_requests WHERE YEAR(date_requested) = $selectedYear), 1) as percentage
FROM tech_support_requests
WHERE YEAR(date_requested) = $selectedYear
GROUP BY support_type
ORDER BY request_count DESC";

$serviceDistribution = $conn->query($serviceDistributionQuery);

// Get regional distribution data
$regionalDistributionQuery = "SELECT 
    c.region,
    COUNT(*) as request_count,
    ROUND((COUNT(*) * 100.0) / (SELECT COUNT(*) FROM tech_support_requests WHERE YEAR(date_requested) = $selectedYear), 1) as percentage
FROM tech_support_requests tsr
JOIN clients c ON tsr.client_id = c.id
WHERE YEAR(tsr.date_requested) = $selectedYear
GROUP BY c.region
ORDER BY request_count DESC";

$regionalDistribution = $conn->query($regionalDistributionQuery);
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
      <!-- Chart.js for line chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <!-- Year Selector -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="get" class="d-flex align-items-center">
                            <label for="yearSelect" class="me-2">Select Year:</label>                            <select name="year" id="yearSelect" class="form-select me-2" style="width: auto;" onchange="this.form.submit()">
                                <?php foreach ($availableYears as $year): ?>                                    <option value="<?php echo $year; ?>" 
                                            <?php echo $year == $selectedYear ? 'selected' : ''; ?>
                                            <?php echo !in_array($year, $yearsWithData) ? 'class="text-muted"' : ''; ?>>
                                        <?php echo $year; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Yearly Trends Line Chart -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Monthly Request Trends - <?php echo $selectedYear; ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="yearlyTrendsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- First Semester Summary -->
        <div class="table-container mb-4">
            <div class="table-header">
                <h5 class="table-title">First Semester Summary (January - June <?php echo $selectedYear; ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Total Requests</th>
                            <th>Resolved</th>
                            <th>In Progress</th>
                            <th>Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($serviceTypes)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No services available</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $firstSemTotal = 0;
                            foreach ($serviceTypes as $service): 
                                $serviceData = $firstSemesterSummary[$service] ?? [
                                    'total_requests' => 0,
                                    'resolved' => 0,
                                    'in_progress' => 0,
                                    'pending' => 0
                                ];
                                $firstSemTotal += $serviceData['total_requests'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service); ?></td>
                                    <td><?php echo htmlspecialchars($serviceData['total_requests']); ?></td>
                                    <td>
                                        <span class="status-badge status-resolved">
                                            <?php echo htmlspecialchars($serviceData['resolved']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-progress">
                                            <?php echo htmlspecialchars($serviceData['in_progress']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-pending">
                                            <?php echo htmlspecialchars($serviceData['pending']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-dark">
                                <td><strong>Total</strong></td>
                                <td><strong><?php echo $firstSemTotal; ?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Second Semester Summary -->
        <div class="table-container mb-4">
            <div class="table-header">
                <h5 class="table-title">Second Semester Summary (July - December <?php echo $selectedYear; ?>)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Total Requests</th>
                            <th>Resolved</th>
                            <th>In Progress</th>
                            <th>Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($serviceTypes)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No services available</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $secondSemTotal = 0;
                            foreach ($serviceTypes as $service): 
                                $serviceData = $secondSemesterSummary[$service] ?? [
                                    'total_requests' => 0,
                                    'resolved' => 0,
                                    'in_progress' => 0,
                                    'pending' => 0
                                ];
                                $secondSemTotal += $serviceData['total_requests'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service); ?></td>
                                    <td><?php echo htmlspecialchars($serviceData['total_requests']); ?></td>
                                    <td>
                                        <span class="status-badge status-resolved">
                                            <?php echo htmlspecialchars($serviceData['resolved']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-progress">
                                            <?php echo htmlspecialchars($serviceData['in_progress']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-pending">
                                            <?php echo htmlspecialchars($serviceData['pending']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-dark">
                                <td><strong>Total</strong></td>
                                <td><strong><?php echo $secondSemTotal; ?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Year Total Summary -->
        <div class="table-container mb-4">
            <div class="table-header">
                <h5 class="table-title">Year <?php echo $selectedYear; ?> Total Summary</h5>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <h3>First Semester Total</h3>
                            <h2 class="text-primary"><?php echo $firstSemTotal; ?></h2>
                            <p class="text-muted">January - June</p>
                        </div>
                        <div class="col-md-6">
                            <h3>Second Semester Total</h3>
                            <h2 class="text-primary"><?php echo $secondSemTotal; ?></h2>
                            <p class="text-muted">July - December</p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <h3>Total Requests for Year <?php echo $selectedYear; ?></h3>
                            <h1 class="text-success"><?php echo $firstSemTotal + $secondSemTotal; ?></h1>
                        </div>
                    </div>
                </div>
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service Type</th>
                                        <th>Requests</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($serviceDistribution && $serviceDistribution->num_rows > 0): ?>
                                        <?php while ($row = $serviceDistribution->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['support_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['request_count']); ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: <?php echo $row['percentage']; ?>%;"
                                                             aria-valuenow="<?php echo $row['percentage']; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo $row['percentage']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Region</th>
                                        <th>Requests</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($regionalDistribution && $regionalDistribution->num_rows > 0): ?>
                                        <?php while ($row = $regionalDistribution->fetch_assoc()): ?>
                                            <tr>
                                                <td>Region <?php echo htmlspecialchars($row['region']); ?></td>
                                                <td><?php echo htmlspecialchars($row['request_count']); ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: <?php echo $row['percentage']; ?>%;"
                                                             aria-valuenow="<?php echo $row['percentage']; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo $row['percentage']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
    

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Pie Chart -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {        // Initialize line chart data

        // Yearly trends line chart
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
        
        const monthlyData = <?php echo json_encode(array_values($monthlyData)); ?>;
        
        const trendsData = {
            labels: monthNames,
            datasets: [
                {
                    label: 'Total Requests',
                    data: monthlyData.map(m => m.total),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Pending',
                    data: monthlyData.map(m => m.pending),
                    borderColor: 'rgba(255, 193, 7, 1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'In Progress',
                    data: monthlyData.map(m => m.in_progress),
                    borderColor: 'rgba(13, 202, 240, 1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Resolved',
                    data: monthlyData.map(m => m.resolved),
                    borderColor: 'rgba(25, 135, 84, 1)',
                    tension: 0.4,
                    fill: false
                }
            ]
        };

        const trendsConfig = {
            type: 'line',
            data: trendsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        };

        // Create the line chart
        const trendsChart = new Chart(
            document.getElementById('yearlyTrendsChart'),
            trendsConfig
        );
    });
    </script>

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
