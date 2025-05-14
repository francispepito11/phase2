<?php
// Initialize variables
$serviceType = isset($_GET['type']) ? $_GET['type'] : '';
$serviceTypes = [
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

$serviceName = isset($serviceTypes[$serviceType]) ? $serviceTypes[$serviceType] : 'Service Request';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real application, you would validate inputs and save to database
    // For this example, we'll just simulate a successful submission
    $success = true;
    
    // Database connection and insertion would go here
    // Example:
    // $conn = new mysqli($servername, $username, $password, $dbname);
    // $stmt = $conn->prepare("INSERT INTO service_requests (client_name, agency, region, province, district, municipality, service_type, description, contact_email, contact_phone, date_requested) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    // $stmt->bind_param("ssssssssss", $_POST['client_name'], $_POST['agency'], $_POST['region'], $_POST['province'], $_POST['district'], $_POST['municipality'], $_POST['service_type'], $_POST['description'], $_POST['contact_email'], $_POST['contact_phone']);
    // $success = $stmt->execute();
    // $stmt->close();
    // $conn->close();
    
    if ($success) {
        // Redirect to thank you page or show success message
        header("Location: thank-you.php?service=" . urlencode($serviceName));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($serviceName); ?> Request - DICT Client Management System</title>
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
                        <a href="services.php" class="border-b-2 border-white px-1 pt-1 text-sm font-medium">Services</a>
                        <a href="training.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Training Calendar</a>
                        <a href="reports.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Reports</a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="login.php" class="text-sm px-4 py-2 rounded bg-blue-700 hover:bg-blue-600">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <a href="services.php" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($serviceName); ?> Request Form
                </h1>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?type=" . urlencode($serviceType)); ?>">
                    <div class="space-y-8 divide-y divide-gray-200">
                        <!-- Client Information -->
                        <div class="space-y-6 pt-8 sm:pt-10">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Client Information</h3>
                                <p class="mt-1 text-sm text-gray-500">Please provide your contact and agency details.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name</label>
                                    <div class="mt-1">
                                        <input type="text" name="client_name" id="client_name" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="agency" class="block text-sm font-medium text-gray-700">Agency/Organization</label>
                                    <div class="mt-1">
                                        <input type="text" name="agency" id="agency" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="region" class="block text-sm font-medium text-gray-700">Region</label>
                                    <div class="mt-1">
                                        <select id="region" name="region" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Select Region</option>
                                            <option value="NCR">National Capital Region</option>
                                            <option value="CAR">Cordillera Administrative Region</option>
                                            <option value="Region I">Region I - Ilocos Region</option>
                                            <option value="Region II">Region II - Cagayan Valley</option>
                                            <option value="Region III">Region III - Central Luzon</option>
                                            <option value="Region IV-A">Region IV-A - CALABARZON</option>
                                            <option value="Region IV-B">Region IV-B - MIMAROPA</option>
                                            <option value="Region V">Region V - Bicol Region</option>
                                            <option value="Region VI">Region VI - Western Visayas</option>
                                            <option value="Region VII">Region VII - Central Visayas</option>
                                            <option value="Region VIII">Region VIII - Eastern Visayas</option>
                                            <option value="Region IX">Region IX - Zamboanga Peninsula</option>
                                            <option value="Region X">Region X - Northern Mindanao</option>
                                            <option value="Region XI">Region XI - Davao Region</option>
                                            <option value="Region XII">Region XII - SOCCSKSARGEN</option>
                                            <option value="Region XIII">Region XIII - Caraga</option>
                                            <option value="BARMM">Bangsamoro Autonomous Region in Muslim Mindanao</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
                                    <div class="mt-1">
                                        <input type="text" name="province" id="province" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="district" class="block text-sm font-medium text-gray-700">District</label>
                                    <div class="mt-1">
                                        <input type="text" name="district" id="district" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="municipality" class="block text-sm font-medium text-gray-700">Municipality/City</label>
                                    <div class="mt-1">
                                        <input type="text" name="municipality" id="municipality" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="space-y-6 pt-8 sm:pt-10">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Service Details</h3>
                                <p class="mt-1 text-sm text-gray-500">Provide details about the service you're requesting.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="service_type" class="block text-sm font-medium text-gray-700">Service Type</label>
                                    <div class="mt-1">
                                        <select id="service_type" name="service_type" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Select Service Type</option>
                                            <?php foreach ($serviceTypes as $key => $value): ?>
                                                <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($key === $serviceType) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($value); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Description of Request
                                    </label>
                                    <div class="mt-1">
                                        <textarea id="description" name="description" rows="4" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">Please provide detailed information about your request.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="space-y-6 pt-8 sm:pt-10">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Contact Information</h3>
                                <p class="mt-1 text-sm text-gray-500">How can we reach you regarding this request?</p>
                            </div>

                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <div class="mt-1">
                                        <input type="email" name="contact_email" id="contact_email" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <div class="mt-1">
                                        <input type="tel" name="contact_phone" id="contact_phone" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5">
                        <div class="flex justify-end">
                            <a href="services.php" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">DICT Client Management System</h3>
                    <p class="text-sm text-blue-100">
                        Providing technical support and services to government agencies and the public.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="text-blue-100 hover:text-white">Home</a></li>
                        <li><a href="services.php" class="text-blue-100 hover:text-white">Services</a></li>
                        <li><a href="training.php" class="text-blue-100 hover:text-white">Training Calendar</a></li>
                        <li><a href="contact.php" class="text-blue-100 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <address class="text-sm text-blue-100 not-italic">
                        <p>Department of Information and Communications Technology</p>
                        <p>C.P. Garcia Avenue, Diliman, Quezon City</p>
                        <p>Email: info@dict.gov.ph</p>
                        <p>Phone: (02) 8920-0101</p>
                    </address>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-blue-700 text-center text-sm text-blue-100">
                <p>&copy; <?php echo date('Y'); ?> Department of Information and Communications Technology. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>