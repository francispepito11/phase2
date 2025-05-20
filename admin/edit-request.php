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
require_once '../includes/location_data.php';

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

// Define support types
$supportTypes = [
    'wifi' => 'WiFi Installation/Configuration',
    'govnet' => 'GovNet Installation/Maintenance',
    'ibpls' => 'iBPLS Virtual Assistance',
    'pnpki' => 'PNPKI Tech Support',
    'equipment-lending' => 'ICT Equipment Lending',
    'cybersecurity' => 'Cybersecurity Support',
    'office-facility' => 'Use of Office Facility',
    'sim-card' => 'Sim Card Registration',
    'comms' => 'Comms-related concern',
    'technical-personnel' => 'Provision of Technical Personnel',
    'other' => 'Other Services'
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $update_data = [
        'client_name' => sanitize_input($_POST['client_name']),
        'agency' => sanitize_input($_POST['agency']),
        'gender' => sanitize_input($_POST['gender']),
        'age' => (int)$_POST['age'],
        'region_id' => (int)$_POST['region_id'],
        'province_id' => (int)$_POST['province_id'],
        'district_id' => !empty($_POST['district_id']) ? (int)$_POST['district_id'] : null,
        'municipality_id' => (int)$_POST['municipality_id'],
        'support_type' => sanitize_input($_POST['support_type']),
        'issue_description' => sanitize_input($_POST['issue_description']),
        'status' => sanitize_input($_POST['status']),
        'remarks' => sanitize_input($_POST['remarks'])
    ];

    // Update status-related fields
    if ($update_data['status'] === 'In Progress' && empty($request['date_assisted'])) {
        $update_data['date_assisted'] = date('Y-m-d H:i:s');
    } elseif ($update_data['status'] === 'Resolved' && empty($request['date_resolved'])) {
        $update_data['date_resolved'] = date('Y-m-d H:i:s');
    }

    // Attempt to update the record
    if (update_record('tech_support_requests', $id, $update_data)) {
        header('Location: view-request.php?id=' . $id . '&updated=1');
        exit;
    }
}

// Get region, province, and municipality names
$region_name = '';
$province_name = '';
$municipality_name = '';

if (!empty($request['region_id'])) {
    $region = get_record_by_id('regions', $request['region_id']);
    if ($region) {
        $region_name = $region['region_name'];
    }
}

if (!empty($request['province_id'])) {
    $province = get_record_by_id('provinces', $request['province_id']);
    if ($province) {
        $province_name = $province['province_name'];
    }
}

if (!empty($request['municipality_id'])) {
    $municipality = get_record_by_id('municipalities', $request['municipality_id']);
    if ($municipality) {
        $municipality_name = $municipality['municipality_name'];
    }
}

// Get all regions for dropdown
$regions = read_records('regions', ['*'], [], 'region_name ASC');
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
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name</label>
                                <input type="text" name="client_name" id="client_name" value="<?php echo htmlspecialchars($request['client_name']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="agency" class="block text-sm font-medium text-gray-700">Agency</label>
                                <input type="text" name="agency" id="agency" value="<?php echo htmlspecialchars($request['agency']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                <select name="gender" id="gender" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="Male" <?php echo $request['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $request['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $request['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    <option value="Prefer not to say" <?php echo $request['gender'] === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                                </select>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                                <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($request['age']); ?>" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <!-- Location Information -->
                            <div class="col-span-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location Information</h3>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="region_id" class="block text-sm font-medium text-gray-700">Region</label>
                                <select name="region_id" id="region_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php foreach ($regions as $region): ?>
                                        <option value="<?php echo $region['id']; ?>" <?php echo $request['region_id'] == $region['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($region['region_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="province_id" class="block text-sm font-medium text-gray-700">Province</label>
                                <select name="province_id" id="province_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="<?php echo $request['province_id']; ?>"><?php echo htmlspecialchars($province_name); ?></option>
                                </select>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="district_id" class="block text-sm font-medium text-gray-700">District</label>
                                <select name="district_id" id="district_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select District</option>
                                </select>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="municipality_id" class="block text-sm font-medium text-gray-700">Municipality</label>
                                <select name="municipality_id" id="municipality_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="<?php echo $request['municipality_id']; ?>"><?php echo htmlspecialchars($municipality_name); ?></option>
                                </select>
                            </div>

                            <!-- Support Request Details -->
                            <div class="col-span-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Support Request Details</h3>
                            </div>

                            <div class="col-span-6">
                                <label for="support_type" class="block text-sm font-medium text-gray-700">Support Type</label>
                                <select name="support_type" id="support_type" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php foreach ($supportTypes as $value => $label): ?>
                                        <option value="<?php echo $label; ?>" <?php echo $request['support_type'] === $label ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-span-6">
                                <label for="issue_description" class="block text-sm font-medium text-gray-700">Issue Description</label>
                                <textarea name="issue_description" id="issue_description" rows="4" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars($request['issue_description']); ?></textarea>
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
