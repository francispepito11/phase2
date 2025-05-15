<?php
// This is a basic test file to verify that the location_data.php endpoint is accessible
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        #log {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
            font-family: monospace;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        select {
            padding: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
            min-width: 200px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Location API Endpoint Test</h1>
    
    <div id="log"></div>
    
    <div class="test-section">
        <h2>Test 1: Get Regions</h2>
        <button id="test-regions">Test Get Regions</button>
    </div>
    
    <div class="test-section">
        <h2>Test 2: Get Provinces</h2>
        <label for="region-select">Select Region:</label>
        <select id="region-select">
            <option value="">Select a region first</option>
        </select>
        <button id="test-provinces">Test Get Provinces</button>
    </div>
    
    <div class="test-section">
        <h2>Test 3: Get Districts</h2>
        <label for="province-select">Select Province:</label>
        <select id="province-select">
            <option value="">Select a province first</option>
        </select>
        <button id="test-districts">Test Get Districts</button>
    </div>
    
    <div class="test-section">
        <h2>Test 4: Get Municipalities</h2>
        <label for="district-select">Select District (optional):</label>
        <select id="district-select">
            <option value="">Select a district (optional)</option>
        </select>
        <button id="test-municipalities">Test Get Municipalities</button>
    </div>
    
    <button id="clear-log">Clear Log</button>
    <a href="tech-support.php" style="margin-left: 10px;">Back to Tech Support Form</a>
    
    <script>
        // Elements
        const logEl = document.getElementById('log');
        const regionSelect = document.getElementById('region-select');
        const provinceSelect = document.getElementById('province-select');
        const districtSelect = document.getElementById('district-select');
        
        // Log function
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const cssClass = type === 'error' ? 'color: red;' : 
                            type === 'success' ? 'color: green;' : '';
            logEl.innerHTML += `<div style="${cssClass}">[${timestamp}] ${message}</div>`;
            logEl.scrollTop = logEl.scrollHeight;
        }
        
        // Clear log
        document.getElementById('clear-log').addEventListener('click', () => {
            logEl.innerHTML = '';
        });
        
        // Test 1: Get Regions
        document.getElementById('test-regions').addEventListener('click', async () => {
            log('Testing GET regions...');
            
            try {
                // Direct approach (without AJAX)
                fetch('includes/location_data.php')
                    .then(response => {
                        log(`Direct fetch status: ${response.status} ${response.statusText}`);
                        return response.text();
                    })
                    .then(text => {
                        if (text.trim()) {
                            log(`Direct fetch response: ${text.substring(0, 100)}...`);
                        } else {
                            log('Direct fetch: Empty response', 'error');
                        }
                    })
                    .catch(error => log(`Direct fetch error: ${error.message}`, 'error'));
                
                // AJAX approach
                const response = await fetch('includes/location_data.php?action=get_regions');
                log(`AJAX regions status: ${response.status} ${response.statusText}`);
                
                const text = await response.text();
                log(`AJAX regions raw response: ${text.substring(0, 100)}...`);
                
                try {
                    const data = JSON.parse(text);
                    log(`AJAX regions parsed ${data.length} items`, 'success');
                    
                    // Clear and populate region dropdown
                    regionSelect.innerHTML = '<option value="">Select a region</option>';
                    data.forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.id;
                        option.textContent = region.region_name;
                        regionSelect.appendChild(option);
                    });
                } catch (e) {
                    log(`JSON parse error: ${e.message}`, 'error');
                }
            } catch (error) {
                log(`Fetch error: ${error.message}`, 'error');
            }
        });
        
        // Test 2: Get Provinces
        document.getElementById('test-provinces').addEventListener('click', async () => {
            const regionId = regionSelect.value;
            
            if (!regionId) {
                log('Please select a region first', 'error');
                return;
            }
            
            log(`Testing GET provinces for region ID: ${regionId}...`);
            
            try {
                const response = await fetch(`includes/location_data.php?action=get_provinces&region_id=${regionId}`);
                log(`AJAX provinces status: ${response.status} ${response.statusText}`);
                
                const text = await response.text();
                log(`AJAX provinces raw response: ${text.substring(0, 100)}...`);
                
                try {
                    const data = JSON.parse(text);
                    log(`AJAX provinces parsed ${data.length} items`, 'success');
                    
                    // Clear and populate province dropdown
                    provinceSelect.innerHTML = '<option value="">Select a province</option>';
                    data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.province_name;
                        provinceSelect.appendChild(option);
                    });
                } catch (e) {
                    log(`JSON parse error: ${e.message}`, 'error');
                }
            } catch (error) {
                log(`Fetch error: ${error.message}`, 'error');
            }
        });
        
        // Test 3: Get Districts
        document.getElementById('test-districts').addEventListener('click', async () => {
            const provinceId = provinceSelect.value;
            
            if (!provinceId) {
                log('Please select a province first', 'error');
                return;
            }
            
            log(`Testing GET districts for province ID: ${provinceId}...`);
            
            try {
                const response = await fetch(`includes/location_data.php?action=get_districts&province_id=${provinceId}`);
                log(`AJAX districts status: ${response.status} ${response.statusText}`);
                
                const text = await response.text();
                log(`AJAX districts raw response: ${text.substring(0, 100)}...`);
                
                try {
                    const data = JSON.parse(text);
                    log(`AJAX districts parsed ${data.length} items`, 'success');
                    
                    // Clear and populate district dropdown
                    districtSelect.innerHTML = '<option value="">Select a district (optional)</option>';
                    data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.district_name;
                        districtSelect.appendChild(option);
                    });
                } catch (e) {
                    log(`JSON parse error: ${e.message}`, 'error');
                }
            } catch (error) {
                log(`Fetch error: ${error.message}`, 'error');
            }
        });
        
        // Test 4: Get Municipalities
        document.getElementById('test-municipalities').addEventListener('click', async () => {
            const provinceId = provinceSelect.value;
            const districtId = districtSelect.value;
            
            if (!provinceId) {
                log('Please select a province first', 'error');
                return;
            }
            
            log(`Testing GET municipalities for province ID: ${provinceId}${districtId ? `, district ID: ${districtId}` : ''}...`);
            
            try {
                let url = `includes/location_data.php?action=get_municipalities&province_id=${provinceId}`;
                if (districtId) {
                    url += `&district_id=${districtId}`;
                }
                
                const response = await fetch(url);
                log(`AJAX municipalities status: ${response.status} ${response.statusText}`);
                
                const text = await response.text();
                log(`AJAX municipalities raw response: ${text.substring(0, 100)}...`);
                
                try {
                    const data = JSON.parse(text);
                    log(`AJAX municipalities parsed ${data.length} items`, 'success');
                } catch (e) {
                    log(`JSON parse error: ${e.message}`, 'error');
                }
            } catch (error) {
                log(`Fetch error: ${error.message}`, 'error');
            }
        });
    </script>
</body>
</html>
