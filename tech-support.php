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
    <title>Technical Support - DICT Client Management System</title>
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
                        <a href="services_provided.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Services</a>
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
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Submit Support Request Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Submit a Support Request</h2>
                    <form action="submit_support.php" method="post" enctype="multipart/form-data">                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" id="first_name" name="first_name" required 
                                    value="<?php echo isset($form_data['first_name']) ? htmlspecialchars($form_data['first_name']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" id="last_name" name="last_name" required 
                                    value="<?php echo isset($form_data['last_name']) ? htmlspecialchars($form_data['last_name']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="middle_initial" class="block text-sm font-medium text-gray-700 mb-1">Middle Initial</label>
                                <input type="text" id="middle_initial" name="middle_initial" maxlength="1"
                                    value="<?php echo isset($form_data['middle_initial']) ? htmlspecialchars($form_data['middle_initial']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select id="gender" name="gender" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo (isset($form_data['gender']) && $form_data['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="agency" class="block text-sm font-medium text-gray-700 mb-1">Agency/Organization</label>
                                <input type="text" id="agency" name="agency" required 
                                    value="<?php echo isset($form_data['agency']) ? htmlspecialchars($form_data['agency']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select id="region" name="region" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Region</option>
                                    <!-- Regions will be loaded dynamically -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                <select id="province" name="province_id" 
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
                        </div>
                          <div class="mb-4">
                            <label for="support_type" class="block text-sm font-medium text-gray-700 mb-1">Support Type</label>
                            <select id="support_type" name="support_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Support Type</option>
                                <?php
                                // Fetch support types from service_types table
                                $query = "SELECT service_code, service_name FROM service_types WHERE is_active = 1 ORDER BY service_name";
                                $result = $conn->query($query);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = (isset($form_data['support_type']) && $form_data['support_type'] == $row['service_code']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($row['service_code']) . '" ' . $selected . '>' . htmlspecialchars($row['service_name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" id="subject" name="subject" required 
                                value="<?php echo isset($form_data['subject']) ? htmlspecialchars($form_data['subject']) : ''; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Description of Issue</label>
                            <textarea id="message" name="message" rows="5" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo isset($form_data['message']) ? htmlspecialchars($form_data['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">Attachment (optional)</label>
                            <input type="file" id="attachment" name="attachment" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Allowed file formats: PDF, JPG, PNG (Max 5MB)</p>
                        </div>
                        
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="privacy" name="privacy" required 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="privacy" class="ml-2 block text-sm text-gray-700">
                                I consent to DICT collecting and processing my data for the purpose of providing technical support.
                            </label>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Submit Support Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Right: Quick Links and Support Info -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Support Hours</h3>
                    <p class="text-gray-600 mb-2">Monday to Friday: 8:00 AM - 5:00 PM</p>
                    <p class="text-gray-600 mb-4">Excluding holidays</p>
                    
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Contact Information</h3>
                    <p class="flex items-center text-gray-600 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        (02) 8-920-0101
                    </p>
                    <p class="flex items-center text-gray-600 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        info@dict.gov.ph
                    </p>                </div>
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
</body>
</html>