<?php
// Start session to store messages
session_start();

// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Initialize error array
$errors = [];
$debug_info = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log the start of form processing
        error_log("Processing support form submission");
        $debug_info[] = "Processing form submission";        // Validate and sanitize inputs
        $first_name = isset($_POST['first_name']) ? sanitize_input($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_input($_POST['last_name']) : '';
        $middle_initial = isset($_POST['middle_initial']) ? sanitize_input($_POST['middle_initial']) : '';
        $agency = isset($_POST['agency']) ? sanitize_input($_POST['agency']) : '';
        $region = isset($_POST['region']) ? sanitize_input($_POST['region']) : '';
        $province_id = isset($_POST['province_id']) && !empty($_POST['province_id']) ? sanitize_input($_POST['province_id']) : null;
        $district_id = isset($_POST['district_id']) && !empty($_POST['district_id']) ? sanitize_input($_POST['district_id']) : null;
        $municipality_id = isset($_POST['municipality_id']) && !empty($_POST['municipality_id']) ? sanitize_input($_POST['municipality_id']) : null;
        $support_type = isset($_POST['support_type']) ? sanitize_input($_POST['support_type']) : '';
        $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
        $privacy = isset($_POST['privacy']) ? true : false;
        
        // Debug info
        $debug_info[] = "Form data received and sanitized";        // Basic validation
        if (empty($first_name)) {
            $errors[] = "First name is required";
        }
        
        if (empty($last_name)) {
            $errors[] = "Last name is required";
        }
        
        if (empty($agency)) {
            $errors[] = "Agency/Organization is required";
        }
        
        if (empty($region)) {
            $errors[] = "Region is required";
        }
        
        if (empty($support_type)) {
            $errors[] = "Support type is required";
        }
        
        if (empty($subject)) {
            $errors[] = "Subject is required";
        }
        
        if (empty($message)) {
            $errors[] = "Description of issue is required";
        }
        
        if (!$privacy) {
            $errors[] = "You must consent to the privacy policy";
        }
        
        // Debug info
        $debug_info[] = "Validation complete. Errors: " . count($errors);
        
        // If no errors, proceed with saving the data
        if (empty($errors)) {
            // Test database connection
            if (!$conn || $conn->connect_error) {
                throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection object is null"));
            }
            
            $debug_info[] = "Database connection successful";
            
            // Get current date and time
            $current_datetime = date('Y-m-d H:i:s');            // Construct full name with middle initial if available
            $full_name = $first_name;
            if (!empty($middle_initial)) {
                $full_name .= ' ' . $middle_initial . '.';
            }
            $full_name .= ' ' . $last_name;
            
            // Prepare data for insertion
            $support_data = [
                'client_name' => $full_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'middle_initial' => $middle_initial,
                'agency' => $agency,
                'region_id' => $region,
                'province_id' => $province_id,
                'district_id' => $district_id,
                'municipality_id' => $municipality_id,
                'support_type' => $support_type,
                'subject' => $subject,
                'issue_description' => $message,
                'status' => 'Pending',
                'date_requested' => $current_datetime
            ];
            
            $debug_info[] = "Support data prepared for insertion";
            
            // Handle file upload if present
            $attachment_path = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
                $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                $max_file_size = 5 * 1024 * 1024; // 5MB
                
                // Get file info
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $_FILES['attachment']['tmp_name']);
                finfo_close($file_info);
                
                $debug_info[] = "File upload detected. MIME type: " . $mime_type;
                
                // Validate file type and size
                if (in_array($mime_type, $allowed_types) && $_FILES['attachment']['size'] <= $max_file_size) {
                    // Create uploads directory if not exists
                    $upload_dir = 'uploads/support/';
                    if (!file_exists($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            throw new Exception("Failed to create upload directory");
                        }
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                    $unique_filename = uniqid('support_') . '.' . $file_extension;
                    $upload_path = $upload_dir . $unique_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                        $attachment_path = $upload_path;
                        $support_data['attachment'] = $attachment_path;
                        $debug_info[] = "File uploaded successfully to: " . $upload_path;
                    } else {
                        throw new Exception("Failed to upload file: " . error_get_last()['message']);
                    }
                } else {
                    $errors[] = "Invalid file. Please upload a PDF, JPG, or PNG file (max 5MB).";
                    $debug_info[] = "File validation failed";
                }
            }
            
            // Check if tech_support_requests table exists
            $check_table_query = "SHOW TABLES LIKE 'tech_support_requests'";
            $table_exists_result = $conn->query($check_table_query);
            
            if (!$table_exists_result) {
                throw new Exception("Error checking if table exists: " . $conn->error);
            }
            
            $table_exists = ($table_exists_result->num_rows > 0);
            $debug_info[] = "Table exists check: " . ($table_exists ? "Yes" : "No");
              if (!$table_exists) {
                $create_table_sql = "CREATE TABLE `tech_support_requests` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `client_name` varchar(100) NOT NULL,
                    `first_name` varchar(50) NOT NULL,
                    `last_name` varchar(50) NOT NULL,
                    `middle_initial` varchar(1) DEFAULT NULL,
                    `agency` varchar(100) NOT NULL,
                    `region_id` int(11) NOT NULL,
                    `province_id` int(11) DEFAULT NULL,
                    `district_id` int(11) DEFAULT NULL,
                    `municipality_id` int(11) DEFAULT NULL,
                    `support_type` varchar(100) NOT NULL,
                    `subject` varchar(255) DEFAULT NULL,
                    `issue_description` text NOT NULL,
                    `attachment` varchar(255) DEFAULT NULL,
                    `status` enum('Pending','In Progress','Resolved','Cancelled') NOT NULL DEFAULT 'Pending',
                    `assisted_by_id` int(11) DEFAULT NULL,
                    `date_requested` datetime NOT NULL,
                    `date_assisted` datetime DEFAULT NULL,
                    `date_resolved` datetime DEFAULT NULL,
                    `remarks` text DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                
                if (!$conn->query($create_table_sql)) {
                    throw new Exception("Error creating table: " . $conn->error);
                }
                
                $debug_info[] = "Table created successfully";
            } else {
                // Check if subject column exists
                $check_column_sql = "SHOW COLUMNS FROM `tech_support_requests` LIKE 'subject'";
                $column_exists_result = $conn->query($check_column_sql);
                
                if (!$column_exists_result) {
                    throw new Exception("Error checking if column exists: " . $conn->error);
                }
                
                $column_exists = ($column_exists_result->num_rows > 0);
                $debug_info[] = "Subject column exists check: " . ($column_exists ? "Yes" : "No");
                
                if (!$column_exists) {
                    $add_column_sql = "ALTER TABLE `tech_support_requests` ADD COLUMN `subject` varchar(255) DEFAULT NULL AFTER `support_type`";
                    if (!$conn->query($add_column_sql)) {
                        throw new Exception("Error adding subject column: " . $conn->error);
                    }
                    $debug_info[] = "Subject column added successfully";
                }
            }
            
            // Direct database insertion for debugging
            $columns = implode(', ', array_map(function($col) {
                return "`$col`";
            }, array_keys($support_data)));
            
            $placeholders = implode(', ', array_fill(0, count($support_data), '?'));
            
            $sql = "INSERT INTO `tech_support_requests` ($columns) VALUES ($placeholders)";
            $debug_info[] = "SQL Query: " . $sql;
            
            // Prepare statement
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            
            $debug_info[] = "Statement prepared successfully";
            
            // Bind parameters
            $types = '';
            $values = [];
            
            foreach ($support_data as $key => $value) {
                $debug_info[] = "Binding parameter: $key = " . (is_null($value) ? "NULL" : $value);
                
                if (is_null($value)) {
                    $types .= 's'; // Treat NULL as string for binding
                } elseif (is_int($value) || is_numeric($value)) {
                    $types .= 'i';
                } elseif (is_double($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                
                $values[] = $value;
            }
            
            $debug_info[] = "Parameter types: " . $types;
            
            // Create bind_param arguments
            $bind_params = array($types);
            foreach ($values as &$value) {
                $bind_params[] = &$value;
            }
            
            // Bind parameters
            if (!call_user_func_array(array($stmt, 'bind_param'), $bind_params)) {
                throw new Exception("Error binding parameters: " . $stmt->error);
            }
            
            $debug_info[] = "Parameters bound successfully";
            
            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }
            
            $debug_info[] = "Statement executed successfully. Insert ID: " . $stmt->insert_id;
            
            // Close statement
            $stmt->close();
            
            // Success - store message in session and redirect
            $_SESSION['success_message'] = "Your support request has been submitted successfully. Our team will contact you soon.";
            
            // Store debug info in session for viewing if needed
            $_SESSION['debug_info'] = $debug_info;
            
            header('Location: tech-support.php?status=success');
            exit();
        }
    } catch (Exception $e) {
        $error_message = "Error in submit_support.php: " . $e->getMessage();
        error_log($error_message);
        $errors[] = "An error occurred while submitting your request. Please try again. Error: " . $e->getMessage();
        $debug_info[] = "Exception caught: " . $e->getMessage();
        
        // Store debug info in session
        $_SESSION['debug_info'] = $debug_info;
    }
    
    // If there are errors, store them in session and redirect back to form
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: tech-support.php');
        exit();
    }
} else {
    // Not a POST request, redirect to the form
    header('Location: tech-support.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Support Request - DICT Client Management System</title>
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
                <a href="tech-support.php" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    Support Request Submission
                </h1>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-3xl mx-auto">
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd" />
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 112 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                There were errors with your submission:
                            </h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($debug_info)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Debug Information:
                            </h3>
                            <ul class="mt-2 text-sm text-yellow-700 list-disc list-inside">
                                <?php foreach ($debug_info as $info): ?>
                                    <li><?php echo htmlspecialchars($info); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="text-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h2 class="mt-2 text-lg font-medium text-gray-900">Error Processing Request</h2>
                <p class="mt-1 text-sm text-gray-500">
                    There was an error processing your support request. Please try again or contact us directly.
                </p>
                <div class="mt-6">
                    <a href="tech-support.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Return to Support Form
                    </a>
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
</body>
</html>