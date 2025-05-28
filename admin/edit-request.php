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

// Get request and client details
$stmt = $conn->prepare("
    SELECT tsr.*, c.firstname, c.surname, c.middle_initial, c.email, c.phone, c.agency, c.region, c.province_id, c.district_id, c.municipality_id
    FROM tech_support_requests tsr
    JOIN clients c ON tsr.client_id = c.id
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {    $status = $_POST['status'];
    $remarks = $_POST['remarks'];
    $date_assisted = null;
    $date_resolved = null;
    
    if ($status == 'In Progress' && empty($request['date_assisted'])) {
        $date_assisted = date('Y-m-d H:i:s');
    } elseif ($status == 'Resolved') {
        if (empty($request['date_resolved'])) {
            $date_resolved = date('Y-m-d H:i:s');
        }
        if (empty($request['date_assisted'])) {
            $date_assisted = date('Y-m-d H:i:s');
        }
    }    // Update the request
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
    
    $assisted_by_id = $_SESSION['username'] ?? null;
    $stmt->bind_param("sssssi", $status, $remarks, $date_assisted, $date_resolved, $assisted_by_id, $id);
    
    if ($stmt->execute()) {
        header('Location: service-requests.php?updated=1');
        exit();
    }
}

// Get location details
$stmt = $conn->prepare("
    SELECT r.region_name, p.province_name, d.district_name, m.municipality_name
    FROM regions r 
    LEFT JOIN provinces p ON p.id = ?
    LEFT JOIN districts d ON d.id = ?
    LEFT JOIN municipalities m ON m.id = ?
    WHERE r.region_code = ?
");
$stmt->bind_param("iiis", $request['province_id'], $request['district_id'], $request['municipality_id'], $request['region']);
$stmt->execute();
$location = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Support Request - DICT Client Management System</title>
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
                <h1 class="text-xl font-semibold">Edit Service Request</h1>
                <div>
                    <p class="text-sm text-gray-500">
                        Welcome, <?php echo $_SESSION['username']; ?> | Admin
                    </p>
                </div>
            </div>
        </div>

        <!-- Page content -->
        <div class="p-6">
            <!-- Back button -->
            <div class="mb-6">
                <a href="service-requests.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Service Requests
                </a>
            </div>

            <!-- Edit Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="grid grid-cols-6 gap-6">
                            <!-- Client Information -->
                            <div class="col-span-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                            </div>                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Client Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($request['firstname'] . ' ' . $request['middle_initial'] . ' ' . $request['surname']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Agency</label>
                                <input type="text" value="<?php echo htmlspecialchars($request['agency']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Contact Details</label>
                                <input type="text" value="<?php echo htmlspecialchars($request['email'] . ' / ' . $request['phone']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <!-- Location Information -->
                            <div class="col-span-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location Information</h3>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Region</label>
                                <input type="text" value="<?php echo htmlspecialchars($location['region_name'] ?? $request['region']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Province</label>
                                <input type="text" value="<?php echo htmlspecialchars($location['province_name'] ?? ''); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">District</label>
                                <input type="text" value="<?php echo htmlspecialchars($location['district_name'] ?? ''); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Municipality</label>
                                <input type="text" value="<?php echo htmlspecialchars($location['municipality_name'] ?? ''); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <!-- Support Request Details -->
                            <div class="col-span-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Support Request Details</h3>
                            </div>

                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Support Type</label>
                                <input type="text" value="<?php echo htmlspecialchars($request['support_type']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Subject</label>
                                <input type="text" value="<?php echo htmlspecialchars($request['subject']); ?>" readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm">
                            </div>

                            <div class="col-span-6">
                                <label class="block text-sm font-medium text-gray-700">Issue Description</label>
                                <textarea readonly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-50 sm:text-sm"><?php echo htmlspecialchars($request['message'] ?? $request['issue_description']); ?></textarea>
                            </div>

                            <!-- Status Information -->
                            <div class="col-span-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Status Information</h3>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="Pending" <?php echo $request['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $request['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Resolved" <?php echo $request['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="Cancelled" <?php echo $request['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="col-span-6">
                                <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                <textarea name="remarks" id="remarks" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars($request['remarks'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add JavaScript for dynamic dropdowns -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const regionSelect = document.getElementById('region_id');
            const provinceSelect = document.getElementById('province_id');
            const districtSelect = document.getElementById('district_id');
            const municipalitySelect = document.getElementById('municipality_id');

            // Function to update provinces when region changes
            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                fetch(`../includes/location_data.php?action=get_provinces&region_id=${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        provinceSelect.innerHTML = '<option value="">Select Province</option>';
                        data.forEach(province => {
                            provinceSelect.innerHTML += `<option value="${province.id}">${province.province_name}</option>`;
                        });
                    });
            });

            // Function to update districts when province changes
            provinceSelect.addEventListener('change', function() {
                const provinceId = this.value;
                fetch(`../includes/location_data.php?action=get_districts&province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        data.forEach(district => {
                            districtSelect.innerHTML += `<option value="${district.id}">${district.district_name}</option>`;
                        });
                    });
            });

            // Function to update municipalities when district changes
            districtSelect.addEventListener('change', function() {
                const districtId = this.value;
                fetch(`../includes/location_data.php?action=get_municipalities&district_id=${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
                        data.forEach(municipality => {
                            municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.municipality_name}</option>`;
                        });
                    });
            });

            // Show remarks field when status is changed to Resolved
            const statusSelect = document.getElementById('status');
            const remarksContainer = document.getElementById('remarks').parentElement;
            
            function toggleRemarksVisibility() {
                remarksContainer.style.display = statusSelect.value === 'Resolved' ? 'block' : 'none';
            }
            
            statusSelect.addEventListener('change', toggleRemarksVisibility);
            toggleRemarksVisibility(); // Initial state
        });
    </script>
</body>
</html>
