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
<?php
// Include database connection and location data
require_once 'includes/db_connect.php';
require_once 'includes/location_data.php';
?>
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left: Submit Support Request Form -->
            <div class="md:col-span-2">                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Submit a Support Request</h2>
                    <form action="submit_support.php" method="post" enctype="multipart/form-data"><div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="agency" class="block text-sm font-medium text-gray-700 mb-1">Agency/Organization</label>
                                <input type="text" id="agency" name="agency" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>                                <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
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
                                <option value="PNPKI Technical Support">PNPKI Technical Support</option>
                                <option value="iGovPhil Support">iGovPhil Support</option>
                                <option value="GovNet Technical Support">GovNet Technical Support</option>
                                <option value="WiFi Connectivity Issues">WiFi Connectivity Issues</option>
                                <option value="National Broadband Plan">National Broadband Plan Support</option>
                                <option value="DICT eGov Services">DICT eGov Services</option>
                                <option value="Other Technical Assistance">Other Technical Assistance</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" id="subject" name="subject" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Description of Issue</label>
                            <textarea id="message" name="message" rows="5" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
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
                    </p>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Common Support Topics</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="#" class="flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                PNPKI Certificate Installation Guide
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                GovNet Connection Troubleshooting
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                iGovPhil Services Overview
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Free WiFi Access Points Locations
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                FAQ - Frequently Asked Questions
                            </a>
                        </li>
                    </ul>
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
        </div>    </footer>    <!-- JavaScript for Dynamic Dropdowns -->
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
                console.log('Fetching all regions');
                fetch('includes/location_data.php?action=get_regions')
                    .then(response => {
                        console.log('Region response status:', response.status);
                        return response.text(); // Use text() instead of json() for debugging
                    })
                    .then(text => {
                        console.log('Raw region response:', text);
                        // Convert text to JSON after logging
                        let data;
                        try {
                            data = JSON.parse(text);
                            console.log('Parsed region data:', data);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            return;
                        }
                        
                        // Clear existing options except the first one
                        regionSelect.innerHTML = '<option value="">Select Region</option>';
                        
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(region => {
                                const option = document.createElement('option');
                                option.value = region.id;
                                option.textContent = region.region_name;
                                regionSelect.appendChild(option);
                            });
                            console.log('Added', data.length, 'regions to dropdown');
                        } else {
                            console.log('No regions found or data is not an array');
                        }
                    })
                    .catch(error => console.error('Error fetching regions:', error));
            }
              
            // Function to fetch provinces by region
            function fetchProvinces(regionId) {
                console.log('Fetching provinces for region ID:', regionId);
                fetch(`includes/location_data.php?action=get_provinces&region_id=${regionId}`)
                    .then(response => {
                        console.log('Province response status:', response.status);
                        return response.text(); // Use text() instead of json() for debugging
                    })
                    .then(text => {
                        console.log('Raw province response:', text);
                        // Convert text to JSON after logging
                        let data;
                        try {
                            data = JSON.parse(text);
                            console.log('Parsed province data:', data);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            return;
                        }
                        
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(province => {
                                const option = document.createElement('option');
                                option.value = province.id;
                                option.textContent = province.province_name;
                                provinceSelect.appendChild(option);
                            });
                            console.log('Added', data.length, 'provinces to dropdown');
                        } else {
                            console.log('No provinces found or data is not an array');
                        }
                    })
                    .catch(error => console.error('Error fetching provinces:', error));
            }
              // Function to fetch districts by province
            function fetchDistricts(provinceId) {
                console.log('Fetching districts for province ID:', provinceId);
                fetch(`includes/location_data.php?action=get_districts&province_id=${provinceId}`)
                    .then(response => {
                        console.log('District response status:', response.status);
                        return response.text(); // Use text() instead of json() for debugging
                    })
                    .then(text => {
                        console.log('Raw district response:', text);
                        // Convert text to JSON after logging
                        let data;
                        try {
                            data = JSON.parse(text);
                            console.log('Parsed district data:', data);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            return;
                        }
                        
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(district => {
                                const option = document.createElement('option');
                                option.value = district.id;
                                option.textContent = district.district_name;
                                districtSelect.appendChild(option);
                            });
                            console.log('Added', data.length, 'districts to dropdown');
                        } else {
                            console.log('No districts found or data is not an array');
                        }
                    })
                    .catch(error => console.error('Error fetching districts:', error));
            }
            
            // Function to fetch municipalities by province and district
            function fetchMunicipalities(provinceId, districtId = null) {
                console.log('Fetching municipalities for province ID:', provinceId, 'district ID:', districtId);
                let url = `includes/location_data.php?action=get_municipalities&province_id=${provinceId}`;
                if (districtId) {
                    url += `&district_id=${districtId}`;
                }
                
                fetch(url)
                    .then(response => {
                        console.log('Municipality response status:', response.status);
                        return response.text(); // Use text() instead of json() for debugging
                    })
                    .then(text => {
                        console.log('Raw municipality response:', text);
                        // Convert text to JSON after logging
                        let data;
                        try {
                            data = JSON.parse(text);
                            console.log('Parsed municipality data:', data);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            return;
                        }
                        
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(municipality => {
                                const option = document.createElement('option');
                                option.value = municipality.id;
                                option.textContent = municipality.municipality_name;
                                municipalitySelect.appendChild(option);
                            });
                            console.log('Added', data.length, 'municipalities to dropdown');
                        } else {
                            console.log('No municipalities found or data is not an array');
                        }
                    })
                    .catch(error => console.error('Error fetching municipalities:', error));
            }
        });
    </script>
</body>
</html>
