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
        $remarks = sanitize_input($_POST['remarks']);

        // Prepare SQL statement
        $sql = "INSERT INTO services_provided (region, province, district, municipality, client_name, agency, 
                support_type, service_provided, support_details, date_requested, date_assisted, date_resolved, 
                assisted_by, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $assisted_by = "System"; // This would be replaced by the logged-in user in a real system
        
        $stmt->bind_param("ssssssssssss", 
            $region, 
            $province, 
            $district, 
            $municipality, 
            $client_name, 
            $agency, 
            $support_type, 
            $service_provided, 
            $support_details, 
            $date_requested, 
            $assisted_by, 
            $remarks
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            $success_message = "Service request submitted successfully!";
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
    <title>Services Provided - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">    <!-- Navigation -->
    <nav class="bg-blue-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold">DICT Client Management System</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="index.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Home</a>
                        <a href="services_provided.php" class="border-b-2 border-white px-1 pt-1 text-sm font-medium">Services</a>
                        <a href="training.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Training</a>
                        <a href="tech-support.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Tech Support</a>
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
                    Services Provided
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
                <h2 class="text-lg font-medium text-blue-800 mb-4">SERVICES PROVIDED - <?php echo date('F Y'); ?></h2>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Row 1: Basic Information -->
                        <div class="sm:col-span-1">
                            <label for="region" class="block text-sm font-medium text-gray-700">REGION</label>
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

                        <div class="sm:col-span-1">
                            <label for="province" class="block text-sm font-medium text-gray-700">PROVINCE</label>
                            <div class="mt-1">
                                <input type="text" name="province" id="province" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="district" class="block text-sm font-medium text-gray-700">DISTRICT</label>
                            <div class="mt-1">
                                <input type="text" name="district" id="district" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="municipality" class="block text-sm font-medium text-gray-700">CITY/MUNICIPALITY</label>
                            <div class="mt-1">
                                <input type="text" name="municipality" id="municipality" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="client_name" class="block text-sm font-medium text-gray-700">CLIENT NAME</label>
                            <div class="mt-1">
                                <input type="text" name="client_name" id="client_name" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="agency" class="block text-sm font-medium text-gray-700">AGENCY</label>
                            <div class="mt-1">
                                <input type="text" name="agency" id="agency" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Row 2: Support Information -->
                        <div class="sm:col-span-2">
                            <label for="support_type" class="block text-sm font-medium text-gray-700">SUPPORT TYPE</label>
                            <div class="mt-1">
                                <select id="support_type" name="support_type" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">Select Support Type</option>
                                    <?php foreach ($support_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="service_provided" class="block text-sm font-medium text-gray-700">SERVICE PROVIDED</label>
                            <div class="mt-1">
                                <input type="text" name="service_provided" id="service_provided" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="support_details" class="block text-sm font-medium text-gray-700">SUPPORT DETAILS</label>
                            <div class="mt-1">
                                <textarea id="support_details" name="support_details" rows="3" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>

                        <!-- Row 3: Dates and Remarks -->
                        <div class="sm:col-span-1">
                            <label for="date_requested" class="block text-sm font-medium text-gray-700">DATE REQUESTED</label>
                            <div class="mt-1">
                                <input type="date" name="date_requested" id="date_requested" required class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="remarks" class="block text-sm font-medium text-gray-700">REMARKS</label>
                            <div class="mt-1">
                                <input type="text" name="remarks" id="remarks" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>

                        <div class="sm:col-span-6 pt-5">
                            <div class="flex justify-end">
                                <button type="button" onclick="window.location.href='index.php'" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </button>
                                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Services List Section -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Services Provided</h3>
                <a href="services_list.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NO</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">REGION</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PROVINCE</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DISTRICT</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CITY/MUNICIPALITY</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENT NAME</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AGENCY</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SUPPORT TYPE</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SERVICE PROVIDED</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DATE REQUESTED</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DATE ASSISTED</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DATE RESOLVED</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ASSISTED BY</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">REMARKS</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        // Fetch recent services from database
                        $sql = "SELECT * FROM services_provided ORDER BY date_requested DESC LIMIT 5";
                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            $count = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . $count . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['region']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['province']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['district']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['municipality']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['client_name']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['agency']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['support_type']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['service_provided']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['date_requested']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['date_assisted']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['date_resolved']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['assisted_by']) . "</td>";
                                echo "<td class='px-3 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['remarks']) . "</td>";
                                echo "</tr>";
                                $count++;
                            }
                        } else {
                            echo "<tr><td colspan='14' class='px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-center'>No services found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
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