<?php
// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Get support types
$support_types = [
    'WiFi Installation/configuration',
    'GovNet Installation/Maintenance',
    'iBPLS Virtual Assistance',
    'PNPKI Tech Support',
    'Lending of ICT Equipment',
    'Use of ICT Equipment',
    'Use of Office Facility',
    'Use of Space, ICT Equipment & Internet Connectivity',
    'Sim Card Registration',
    'Comms-related concern',
    'Cybersecurity/Data Privacy related concern',
    'Provision of Technical Personnel/ Resource Person',
    'Others'
];

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: services-list.php');
    exit;
}

$id = (int)$_GET['id'];

// Get service details
$service = get_record_by_id('services_provided', $id);

// If service not found, redirect to services list
if (!$service) {
    header('Location: services-list.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input data
        $region = sanitize_input($_POST['region']);
        $province = sanitize_input($_POST['province']);
        $district = sanitize_input($_POST['district']);
        $municipality = sanitize_input($_POST['municipality']);
        $client_name = sanitize_input($_POST['client_name']);
        $agency = sanitize_input($_POST['agency']);
        $support_type = sanitize_input($_POST['support_type']);
        $service_provided = sanitize_input($_POST['service_provided']);
        $support_details = sanitize_input($_POST['support_details']);
        $date_requested = sanitize_input($_POST['date_requested']);
        $date_assisted = !empty($_POST['date_assisted']) ? sanitize_input($_POST['date_assisted']) : null;
        $date_resolved = !empty($_POST['date_resolved']) ? sanitize_input($_POST['date_resolved']) : null;
        $assisted_by = sanitize_input($_POST['assisted_by']);
        $remarks = sanitize_input($_POST['remarks']);

        // Prepare data for update
        $data = [
            'region' => $region,
            'province' => $province,
            'district' => $district,
            'municipality' => $municipality,
            'client_name' => $client_name,
            'agency' => $agency,
            'support_type' => $support_type,
            'service_provided' => $service_provided,
            'support_details' => $support_details,
            'date_requested' => $date_requested,
            'assisted_by' => $assisted_by,
            'remarks' => $remarks
        ];

        // Add date_assisted and date_resolved if provided
        if ($date_assisted) {
            $data['date_assisted'] = $date_assisted;
        }
        
        if ($date_resolved) {
            $data['date_resolved'] = $date_resolved;
        }

        // Update the record
        if (update_record('services_provided', $id, $data)) {
            $success_message = "Service updated successfully!";
            // Refresh service data
            $service = get_record_by_id('services_provided', $id);
        } else {
            $error_message = "Error updating service.";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold">DICT Client Management System</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="index.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Home</a>
                        <a href="services-provided.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Services Provided</a>
                        <a href="services-list.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Services List</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <a href="services-list.php" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    Edit Service
                </h1>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-blue-800 mb-4">Edit Service Information</h2>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Row 1: Basic Information -->
                        <div class="sm:col-span-1">
                            <label for="region" class="block text-sm font-medium text-gray-700">REGION</label>
                            <div class="mt-1">
                                <select id="region" name="region" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Region</option>
                                    <option value="NCR" <?php echo $service['region'] === 'NCR' ? 'selected' : ''; ?>>National Capital Region</option>
                                    <option value="CAR" <?php echo $service['region'] === 'CAR' ? 'selected' : ''; ?>>Cordillera Administrative Region</option>
                                    <option value="Region I" <?php echo $service['region'] === 'Region I' ? 'selected' : ''; ?>>Region I - Ilocos Region</option>
                                    <option value="Region II" <?php echo $service['region'] === 'Region II' ? 'selected' : ''; ?>>Region II - Cagayan Valley</option>
                                    <option value="Region III" <?php echo $service['region'] === 'Region III' ? 'selected' : ''; ?>>Region III - Central Luzon</option>
                                    <option value="Region IV-A" <?php echo $service['region'] === 'Region IV-A' ? 'selected' : ''; ?>>Region IV-A - CALABARZON</option>
                                    <option value="Region IV-B" <?php echo $service['region'] === 'Region IV-B' ? 'selected' : ''; ?>>Region IV-B - MIMAROPA</option>
                                    <option value="Region V" <?php echo $service['region'] === 'Region V' ? 'selected' : ''; ?>>Region V - Bicol Region</option>
                                    <option value="Region VI" <?php echo $service['region'] === 'Region VI' ? 'selected' : ''; ?>>Region VI - Western Visayas</option>
                                    <option value="Region VII" <?php echo $service['region'] === 'Region VII' ? 'selected' : ''; ?>>Region VII - Central Visayas</option>
                                    <option value="Region VIII" <?php echo $service['region'] === 'Region VIII' ? 'selected' : ''; ?>>Region VIII - Eastern Visayas</option>
                                    <option value="Region IX" <?php echo $service['region'] === 'Region IX' ? 'selected' : ''; ?>>Region IX - Zamboanga Peninsula</option>
                                    <option value="Region X" <?php echo $service['region'] === 'Region X' ? 'selected' : ''; ?>>Region X - Northern Mindanao</option>
                                    <option value="Region XI" <?php echo $service['region'] === 'Region XI' ? 'selected' : ''; ?>>Region XI - Davao Region</option>
                                    <option value="Region XII" <?php echo $service['region'] === 'Region XII' ? 'selected' : ''; ?>>Region XII - SOCCSKSARGEN</option>
                                    <option value="Region XIII" <?php echo $service['region'] === 'Region XIII' ? 'selected' : ''; ?>>Region XIII - Caraga</option>
                                    <option value="BARMM" <?php echo $service['region'] === 'BARMM' ? 'selected' : ''; ?>>Bangsamoro Autonomous Region in Muslim Mindanao</option>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="province" class="block text-sm font-medium text-gray-700">PROVINCE</label>
                            <div class="mt-1">
                                <input type="text" name="province" id="province" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['province']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="district" class="block text-sm font-medium text-gray-700">DISTRICT</label>
                            <div class="mt-1">
                                <input type="text" name="district" id="district" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['district']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="municipality" class="block text-sm font-medium text-gray-700">CITY/MUNICIPALITY</label>
                            <div class="mt-1">
                                <input type="text" name="municipality" id="municipality" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['municipality']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="client_name" class="block text-sm font-medium text-gray-700">CLIENT NAME</label>
                            <div class="mt-1">
                                <input type="text" name="client_name" id="client_name" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['client_name']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="agency" class="block text-sm font-medium text-gray-700">AGENCY</label>
                            <div class="mt-1">
                                <input type="text" name="agency" id="agency" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['agency']); ?>">
                            </div>
                        </div>

                        <!-- Row 2: Support Information -->
                        <div class="sm:col-span-2">
                            <label for="support_type" class="block text-sm font-medium text-gray-700">SUPPORT TYPE</label>
                            <div class="mt-1">
                                <select id="support_type" name="support_type" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Support Type</option>
                                    <?php foreach ($support_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $service['support_type'] === $type ? 'selected' : ''; ?>><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="service_provided" class="block text-sm font-medium text-gray-700">SERVICE PROVIDED</label>
                            <div class="mt-1">
                                <input type="text" name="service_provided" id="service_provided" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['service_provided']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="support_details" class="block text-sm font-medium text-gray-700">SUPPORT DETAILS</label>
                            <div class="mt-1">
                                <textarea id="support_details" name="support_details" rows="3" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($service['support_details']); ?></textarea>
                            </div>
                        </div>

                        <!-- Row 3: Dates and Status -->
                        <div class="sm:col-span-1">
                            <label for="date_requested" class="block text-sm font-medium text-gray-700">DATE REQUESTED</label>
                            <div class="mt-1">
                                <input type="date" name="date_requested" id="date_requested" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['date_requested']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="date_assisted" class="block text-sm font-medium text-gray-700">DATE ASSISTED</label>
                            <div class="mt-1">
                                <input type="datetime-local" name="date_assisted" id="date_assisted" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo !empty($service['date_assisted']) ? date('Y-m-d\TH:i', strtotime($service['date_assisted'])) : ''; ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="date_resolved" class="block text-sm font-medium text-gray-700">DATE RESOLVED</label>
                            <div class="mt-1">
                                <input type="datetime-local" name="date_resolved" id="date_resolved" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo !empty($service['date_resolved']) ? date('Y-m-d\TH:i', strtotime($service['date_resolved'])) : ''; ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="assisted_by" class="block text-sm font-medium text-gray-700">ASSISTED BY</label>
                            <div class="mt-1">
                                <input type="text" name="assisted_by" id="assisted_by" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['assisted_by']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="remarks" class="block text-sm font-medium text-gray-700">REMARKS</label>
                            <div class="mt-1">
                                <input type="text" name="remarks" id="remarks" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" value="<?php echo htmlspecialchars($service['remarks']); ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-6 pt-5">
                            <div class="flex justify-end">
                                <a href="services-list.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </a>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Update Service
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Department of Information and Communications Technology. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>