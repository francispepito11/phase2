<?php
// Include database connection
require_once 'includes/db_connect.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Get support types from database
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input data
        $client_name = sanitize_input($_POST['client_name']);
        $agency = sanitize_input($_POST['agency']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $region = sanitize_input($_POST['region']);
        $province = sanitize_input($_POST['province']);
        $district = isset($_POST['district']) ? sanitize_input($_POST['district']) : '';
        $municipality = sanitize_input($_POST['municipality']);
        $support_type = sanitize_input($_POST['support_type']);
        $issue_description = sanitize_input($_POST['issue_description']);
        
        // Prepare SQL statement
        $sql = "INSERT INTO tech_support_requests (client_name, agency, email, phone, region, province, district, 
                municipality, support_type, issue_description, date_requested, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param("ssssssssss", 
            $client_name, 
            $agency, 
            $email, 
            $phone, 
            $region, 
            $province, 
            $district, 
            $municipality, 
            $support_type, 
            $issue_description
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            $success_message = "Tech support request submitted successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        // Close statement
        $stmt->close();
        
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
    <title>Tech Support - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }    </style>
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
                    <div class="hidden md:ml-6 md:flex md:space-x-8">                        <a href="index.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Home</a>
                        <a href="services_provided.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Services</a>
                        <a href="training.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Training</a>
                        <a href="tech_support.php" class="border-b-2 border-white px-1 pt-1 text-sm font-medium">Tech Support</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <a href="index.php" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    Tech Support Request
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
                <h2 class="text-lg font-medium text-blue-800 mb-4">Request Technical Support</h2>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Client Information -->
                        <div class="sm:col-span-6">
                            <h3 class="text-md font-medium text-gray-700 mb-2">Client Information</h3>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="client_name" class="block text-sm font-medium text-gray-700">Full Name</label>
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
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <div class="mt-1">
                                <input type="tel" name="phone" id="phone" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="sm:col-span-6">
                            <h3 class="text-md font-medium text-gray-700 mb-2">Location Information</h3>
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
                            <label for="district" class="block text-sm font-medium text-gray-700">District (Optional)</label>
                            <div class="mt-1">
                                <input type="text" name="district" id="district" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="municipality" class="block text-sm font-medium text-gray-700">City/Municipality</label>
                            <div class="mt-1">
                                <input type="text" name="municipality" id="municipality" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Support Request Details -->
                        <div class="sm:col-span-6">
                            <h3 class="text-md font-medium text-gray-700 mb-2">Support Request Details</h3>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="support_type" class="block text-sm font-medium text-gray-700">Support Type</label>
                            <div class="mt-1">
                                <select id="support_type" name="support_type" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Support Type</option>
                                    <?php foreach ($support_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="issue_description" class="block text-sm font-medium text-gray-700">Issue Description</label>
                            <div class="mt-1">
                                <textarea id="issue_description" name="issue_description" rows="4" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Please provide detailed information about the technical issue you're experiencing.</p>
                        </div>

                        <div class="sm:col-span-6 pt-5">
                            <div class="flex justify-end">
                                <button type="button" onclick="window.location.href='index.php'" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </button>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Submit Request
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