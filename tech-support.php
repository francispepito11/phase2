<?php
// Start session to access error messages
session_start();

// Include database connection and location data
require_once 'includes/db_connect.php';
require_once 'includes/location_data.php';

// Get any error messages from session
$errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear session variables
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ILCD</title>
    <link rel="icon" type="image/x-icon" href="images/dict-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Satisfaction Modal Fix */
        #satisfactionModal {
            z-index: 9999;
        }
        #satisfactionModal .modal-content {
            z-index: 10000;
        }
        #satisfactionModal .modal-backdrop {
            z-index: 9998;
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
                        <a href="http://localhost/phase2/">
                            <img src="images/dict-logo.png" alt="DICT Logo" class="h-10 w-auto mr-3">
                        </a>
                        <span class="text-xl font-bold">ILCDB Client Services Portal</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="index.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Home</a>
                        <a href="training.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Training</a>
                        <a href="tech-support.php" class="border-b-2 border-white px-1 pt-1 text-sm font-medium">Tech Support</a>
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
                    Technical Support
                </h1>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
            <p class="font-bold">There were errors with your submission:</p>
            <ul class="mt-2 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success' || !empty($success_message)): ?>
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
            <p class="font-bold">Success!</p>
            <p><?php echo !empty($success_message) ? $success_message : 'Your support request has been submitted successfully. Our team will contact you soon.'; ?></p>
        </div>
        <!-- Client Satisfaction Popup Modal -->                <div id="satisfactionModal" class="fixed inset-0 flex items-center justify-center z-50 hidden" style="z-index:9999;">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full border border-blue-200 modal-content" style="z-index:10000;">
                <h2 class="text-xl font-semibold mb-4 text-center text-blue-800">We Value Your Feedback!</h2>
                <form id="satisfactionForm">
                    <input type="hidden" id="supportRequestId" name="support_request_id" value="">
                    <input type="hidden" id="clientNameFeedback" name="client_name" value="<?php echo isset($_GET['client_name']) ? htmlspecialchars($_GET['client_name']) : ''; ?>">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2 text-sm">How satisfied are you with our support?</label>
                        <div class="flex justify-between space-x-2">
                            <button type="button" class="satisfaction-btn flex-1 py-2 rounded bg-green-100 hover:bg-green-200 text-green-800 font-semibold" data-value="5">Very Satisfied</button>
                            <button type="button" class="satisfaction-btn flex-1 py-2 rounded bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold" data-value="4">Satisfied</button>
                            <button type="button" class="satisfaction-btn flex-1 py-2 rounded bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-semibold" data-value="3">Neutral</button>
                            <button type="button" class="satisfaction-btn flex-1 py-2 rounded bg-orange-100 hover:bg-orange-200 text-orange-800 font-semibold" data-value="2">Dissatisfied</button>
                            <button type="button" class="satisfaction-btn flex-1 py-2 rounded bg-red-100 hover:bg-red-200 text-red-800 font-semibold" data-value="1">Very Dissatisfied</button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="satisfactionComment" class="block text-gray-700 mb-2 text-sm">Additional Comments (optional):</label>
                        <textarea id="satisfactionComment" name="comment" rows="2" class="w-full border border-gray-300 rounded-md p-2"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="closeSatisfactionModal" class="mr-2 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Close</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit</button>
                    </div>
                </form>
                <div id="feedbackSuccessMsg" class="hidden mt-4 text-green-700 text-center font-semibold">Thank you for your feedback!</div>
            </div>
            <div class="fixed inset-0 bg-black opacity-40 modal-backdrop"></div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Submit Support Request Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Submit a Support Request</h2>
                    <form action="submit_support.php" method="post" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" id="firstname" name="firstname" required 
                                    value="<?php echo isset($form_data['firstname']) ? htmlspecialchars($form_data['firstname']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
    
                            <div>
                                <label for="middle_initial" class="block text-sm font-medium text-gray-700 mb-1">M.I.</label>
                                <input type="text" id="middle_initial" name="middle_initial" maxlength="1" 
                                    value="<?php echo isset($form_data['middle_initial']) ? htmlspecialchars($form_data['middle_initial']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="surname" class="block text-sm font-medium text-gray-700 mb-1">Surname</label>
                                <input type="text" id="surname" name="surname" required 
                                    value="<?php echo isset($form_data['surname']) ? htmlspecialchars($form_data['surname']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select id="gender" name="gender" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    <option value="Prefer not to say" <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                                </select>
                            </div>
                            <div>
                                <label for="birthdate" class="block text-sm font-medium text-gray-700 mb-1">Birth Date</label>
                                <input type="date" id="birthdate" name="birthdate" required 
                                    value="<?php echo isset($form_data['birthdate']) ? htmlspecialchars($form_data['birthdate']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <input type="hidden" id="email" name="email" value="support@example.com">
                            <input type="hidden" id="phone" name="phone" value="1234567890">
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select id="region" name="region" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Region</option>
                                    <!-- Regions will be loaded dynamically -->
                                </select>
                            </div>
                            <div>
                                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                <select id="province" name="province_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Province</option>
                                    <!-- Provinces will be loaded dynamically -->
                                </select>
                            </div>
                            <div>
                                <label for="district" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                <select id="district" name="district_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select District</option>
                                    <!-- Districts will be loaded dynamically -->
                                </select>
                            </div>
                            <div>
                                <label for="city_municipality" class="block text-sm font-medium text-gray-700 mb-1">City/Municipality</label>
                                <select id="city_municipality" name="municipality_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select City/Municipality</option>
                                    <!-- Municipalities will be loaded dynamically -->
                                </select>
                            </div>
                            <div>
                                <label for="support_type" class="block text-sm font-medium text-gray-700 mb-1">Support Type</label>
                                <select id="support_type" name="support_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Support Type</option>
                                    <option value="Wifi Installation/Configuration" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Wifi Installation/Configuration') ? 'selected' : ''; ?>>Wifi Installation/Configuration</option>
                                    <option value="GovNet Technical Support" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'GovNet Technical Support') ? 'selected' : ''; ?>>GovNet Installation/Maintenance</option>
                                    <option value="IBPLS Virtual Assistance" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'IBPLS Virtual Assistance') ? 'selected' : ''; ?>>IBPLS Virtual Assistance</option>
                                    <option value="PNPKI Technical Support" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'PNPKI Technical Support') ? 'selected' : ''; ?>>PNPKI Tech Support</option>
                                    <option value="Lending of ICT Equipment" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Lending of ICT Equipment') ? 'selected' : ''; ?>>Lending of ICT Equipment</option>
                                    <option value="Use of ICT Equipment" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Use of ICT Equipment') ? 'selected' : ''; ?>>Use of ICT Equipment</option>
                                    <option value="Use of Office Facility" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Use of Office Facility') ? 'selected' : ''; ?>>Use of Office Facility</option>
                                    <option value="Use of Space, ICT Equipment and Internet Connectivity" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Use of Space, ICT Equipment and Internet Connectivity') ? 'selected' : ''; ?>>Use of Space, ICT Equipment and Internet Connectivity</option>
                                    <option value="Sim Card Registration" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Sim Card Registration') ? 'selected' : ''; ?>>Sim Card Registration</option>
                                    <option value="Comms-related concern" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Comms-related concern') ? 'selected' : ''; ?>>Comms-related concern</option>
                                    <option value="Cybersecurity/Data Privacy related concern" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Cybersecurity/Data Privacy related concern') ? 'selected' : ''; ?>>Cybersecurity/Data Privacy related concern</option>
                                    <option value="Provision of Technical Personnel/ Resoure Person" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Provision of Technical Personnel/ Resoure Person') ? 'selected' : ''; ?>>Provision of Technical Personnel/ Resoure Person</option>
                                    <option value="Others" <?php echo (isset($form_data['support_type']) && $form_data['support_type'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                                </select>
                            </div>
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Service Provided</label>
                                <input type="text" id="subject" name="subject" required 
                                    value="<?php echo isset($form_data['subject']) ? htmlspecialchars($form_data['subject']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="agency" class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
                                <select id="agency" name="agency" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Sector</option>
                                    <option value="Student" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                                    <option value="Youth" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Youth') ? 'selected' : ''; ?>>Youth</option>
                                    <option value="Seniors" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Seniors') ? 'selected' : ''; ?>>Seniors</option>
                                    <option value="Homemaker" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Homemaker') ? 'selected' : ''; ?>>Homemaker</option>
                                    <option value="PWD" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'PWD') ? 'selected' : ''; ?>>PWD</option>
                                    <option value="IPs" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'IPs') ? 'selected' : ''; ?>>IP's</option>
                                    <option value="Out of School Youth" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Out of School Youth') ? 'selected' : ''; ?>>Out of School Youth</option>
                                    <option value="Unemployed" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Unemployed') ? 'selected' : ''; ?>>Unemployed</option>
                                    <option value="OFW" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'OFW') ? 'selected' : ''; ?>>OFW</option>
                                    <option value="Farmers" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Farmers') ? 'selected' : ''; ?>>Farmers</option>
                                    <option value="Fisherman" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Fisherman') ? 'selected' : ''; ?>>Fisherman</option>
                                    <option value="Vendors" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Vendors') ? 'selected' : ''; ?>>Vendors</option>
                                    <option value="Business Owner" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Business Owner') ? 'selected' : ''; ?>>Business Owner</option>
                                    <option value="Teacher/Educator" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Teacher/Educator') ? 'selected' : ''; ?>>Teacher/Educator</option>
                                    <option value="Healthcare Worker" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Healthcare Worker') ? 'selected' : ''; ?>>Healthcare Worker</option>
                                    <option value="Government Employee" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Government Employee') ? 'selected' : ''; ?>>Government Employee</option>
                                    <option value="Skilled Laborers" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Skilled Laborers') ? 'selected' : ''; ?>>Skilled Laborers</option>
                                    <option value="Service Worker" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Service Worker') ? 'selected' : ''; ?>>Service Worker</option>
                                    <option value="Transportation Worker" <?php echo (isset($form_data['agency']) && $form_data['agency'] == 'Transportation Worker') ? 'selected' : ''; ?>>Transportation Worker</option>
                                </select>
                            </div>
                        </div>
                        <!-- Hidden field for attachment -->
                        <input type="hidden" name="attachment_removed" value="1">
                        
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="privacy" name="privacy" required 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="privacy" class="ml-2 block text-sm text-gray-700">
                                I consent to DICT collecting and processing my data for the purpose of providing technical support.
                            </label>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                class="w-50px py-2 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Submit Support Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Right: Quick Links and Support Info -->            <div>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex flex-col items-center mb-4">
                        <img src="images/dict-logo.png" alt="DICT Logo" class="h-24 w-auto mb-4">
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Support Hours</h3>
                    <p class="text-gray-600 mb-2">Monday to Friday: 8:00 AM - 5:00 PM</p>
                    <p class="text-gray-600 mb-4">Excluding holidays</p>
                    
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Contact Information</h3>
                    <p class="flex items-center text-gray-600 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        (062) 991 2742
                    </p>
                    <p class="flex items-center text-gray-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        region9basulta@dict.gov.ph
                    </p>
                </div>
  
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

    <!-- JavaScript for Dynamic Dropdowns -->
    <script>
        // Client Satisfaction Modal Logic
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.search.includes('status=success') || <?php echo !empty($success_message) ? 'true' : 'false'; ?>) {                // Get client name from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const clientName = urlParams.get('client_name');
                if (clientName) {
                    document.getElementById('clientNameFeedback').value = decodeURIComponent(clientName);
                }
                setTimeout(function() {
                    document.getElementById('satisfactionModal').classList.remove('hidden');
                }, 1000);
            }
            // Handle satisfaction button selection
            let selectedValue = null;
            document.querySelectorAll('.satisfaction-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    selectedValue = this.getAttribute('data-value');
                    document.querySelectorAll('.satisfaction-btn').forEach(b => b.classList.remove('ring', 'ring-2', 'ring-blue-500'));
                    this.classList.add('ring', 'ring-2', 'ring-blue-500');
                });
            });
            // Close modal
            document.getElementById('closeSatisfactionModal').addEventListener('click', function() {
                document.getElementById('satisfactionModal').classList.add('hidden');
            });
            // Handle form submit
            document.getElementById('satisfactionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var comment = document.getElementById('satisfactionComment').value;
                var supportRequestId = document.getElementById('supportRequestId').value;
                var clientName = document.getElementById('clientNameFeedback').value;
                if (!selectedValue) {
                    alert('Please select your satisfaction rating.');
                    return;
                }
                var formData = new FormData();
                formData.append('rating', selectedValue);
                formData.append('comment', comment);
                formData.append('support_request_id', supportRequestId);
                formData.append('client_name', clientName);
                fetch('includes/save_feedback.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('feedbackSuccessMsg').classList.remove('hidden');
                        setTimeout(function() {
                            document.getElementById('satisfactionModal').classList.add('hidden');
                            document.getElementById('feedbackSuccessMsg').classList.add('hidden');
                        }, 2000);
                    } else {
                        alert('Failed to save feedback: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(() => {
                    alert('Failed to save feedback.');
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Get reference to the dropdown elements
            const regionSelect = document.getElementById('region');
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const municipalitySelect = document.getElementById('city_municipality');
            
            // Load regions on page load
            fetchRegions();
            
            // Event listener for region change
            regionSelect.addEventListener('change', function() {
                const regionId = this.value;
                
                // Clear dependent dropdowns
                provinceSelect.innerHTML = '<option value="">Select Province</option>';
                districtSelect.innerHTML = '<option value="">Select District</option>';
                municipalitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                
                if (regionId) {
                    fetchProvinces(regionId);
                }
            });
            
            // Event listener for province change
            provinceSelect.addEventListener('change', function() {
                const provinceId = this.value;
                
                // Clear dependent dropdowns
                districtSelect.innerHTML = '<option value="">Select District</option>';
                municipalitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                
                if (provinceId) {
                    fetchDistricts(provinceId);
                    fetchMunicipalities(provinceId);
                }
            });
            
            // Event listener for district change
            districtSelect.addEventListener('change', function() {
                const districtId = this.value;
                const provinceId = provinceSelect.value;
                
                // Clear municipality dropdown
                municipalitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                
                if (districtId && provinceId) {
                    fetchMunicipalities(provinceId, districtId);
                }
            });
            
            // Function to fetch all regions
            function fetchRegions() {
                fetch('includes/location_data.php?action=get_regions')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(region => {
                                const option = document.createElement('option');
                                option.value = region.id;
                                option.textContent = region.region_name;
                                
                                // Set selected if matches form data
                                if ('<?php echo isset($form_data['region']) ? $form_data['region'] : ''; ?>' === region.id) {
                                    option.selected = true;
                                }
                                
                                regionSelect.appendChild(option);
                            });
                            
                            // If region is selected, load provinces
                            if (regionSelect.value) {
                                fetchProvinces(regionSelect.value);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching regions:', error);
                        // Add user-friendly error message
                        regionSelect.innerHTML = '<option value="">Error loading regions</option>';
                    });
            }
            
            // Function to fetch provinces by region
            function fetchProvinces(regionId) {
                fetch(`includes/location_data.php?action=get_provinces&region_id=${regionId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(province => {
                                const option = document.createElement('option');
                                option.value = province.id;
                                option.textContent = province.province_name;
                                
                                // Set selected if matches form data
                                if ('<?php echo isset($form_data['province_id']) ? $form_data['province_id'] : ''; ?>' === province.id) {
                                    option.selected = true;
                                }
                                
                                provinceSelect.appendChild(option);
                            });
                            
                            // If province is selected, load districts and municipalities
                            if (provinceSelect.value) {
                                fetchDistricts(provinceSelect.value);
                                fetchMunicipalities(provinceSelect.value);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching provinces:', error);
                        // Add user-friendly error message
                        provinceSelect.innerHTML = '<option value="">Error loading provinces</option>';
                    });
            }
            
            // Function to fetch districts by province
            function fetchDistricts(provinceId) {
                fetch(`includes/location_data.php?action=get_districts&province_id=${provinceId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(district => {
                                const option = document.createElement('option');
                                option.value = district.id;
                                option.textContent = district.district_name;
                                
                                // Set selected if matches form data
                                if ('<?php echo isset($form_data['district_id']) ? $form_data['district_id'] : ''; ?>' === district.id) {
                                    option.selected = true;
                                }
                                
                                districtSelect.appendChild(option);
                            });
                            
                            // If district is selected, load municipalities
                            if (districtSelect.value && provinceSelect.value) {
                                fetchMunicipalities(provinceSelect.value, districtSelect.value);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching districts:', error);
                        // Add user-friendly error message
                        districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                    });
            }
            
            // Function to fetch municipalities by province and district
            function fetchMunicipalities(provinceId, districtId = null) {
                let url = `includes/location_data.php?action=get_municipalities&province_id=${provinceId}`;
                if (districtId) {
                    url += `&district_id=${districtId}`;
                }
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(municipality => {
                                const option = document.createElement('option');
                                option.value = municipality.id;
                                option.textContent = municipality.municipality_name;
                                
                                // Set selected if matches form data
                                if ('<?php echo isset($form_data['municipality_id']) ? $form_data['municipality_id'] : ''; ?>' === municipality.id) {
                                    option.selected = true;
                                }
                                
                                municipalitySelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching municipalities:', error);
                        // Add user-friendly error message
                        municipalitySelect.innerHTML = '<option value="">Error loading municipalities</option>';
                    });
            }
        });
    </script>

     <footer class="bg-blue-800 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">ILCDB Client Services Portal</h3>
                    <p class="text-sm text-blue-100">
                        Providing technical support, services, and training to government agencies and the public.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="text-blue-100 hover:text-white">Home</a></li>
                        <li><a href="training.php" class="text-blue-100 hover:text-white">Training</a></li>
                        <li><a href="tech-support.php" class="text-blue-100 hover:text-white">Tech Support</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <address class="text-sm text-blue-100 not-italic">
                        <p>DICT IX BASULTA</p>
                        <p>CDICT Region IX BASULTA Zamboanga City, Zamboanga City, Philippines</p>
                        <p>Email: region9basulta@dict.gov.ph</p>
                        <p>Phone: (062) 991 2742</p>
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