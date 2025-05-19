<?php
// Start session to store messages
session_start();

// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Initialize error array
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
<<<<<<< HEAD
        // Validate and sanitize inputs
        $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : 'support@example.com'; // Default email
=======
        // Log the start of form processing
        error_log("Processing support form submission");
        $debug_info[] = "Processing form submission";        // Validate and sanitize inputs
        $first_name = isset($_POST['first_name']) ? sanitize_input($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_input($_POST['last_name']) : '';
        $middle_initial = isset($_POST['middle_initial']) ? sanitize_input($_POST['middle_initial']) : '';
>>>>>>> 62cf84f8be47b7893b6cfbc324a6d4b9ce2ed352
        $agency = isset($_POST['agency']) ? sanitize_input($_POST['agency']) : '';
        $region = isset($_POST['region']) ? sanitize_input($_POST['region']) : '';
        $province_id = isset($_POST['province_id']) && !empty($_POST['province_id']) ? sanitize_input($_POST['province_id']) : null;
        $district_id = isset($_POST['district_id']) && !empty($_POST['district_id']) ? sanitize_input($_POST['district_id']) : null;
        $municipality_id = isset($_POST['municipality_id']) && !empty($_POST['municipality_id']) ? sanitize_input($_POST['municipality_id']) : null;
        $support_type = isset($_POST['support_type']) ? sanitize_input($_POST['support_type']) : '';
        $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
<<<<<<< HEAD
        $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '1234567890'; // Default phone
        $privacy = isset($_POST['privacy']) ? true : false;
        
        // Basic validation
        if (empty($name)) {
            $errors[] = "Full name is required";
        }
        
=======
        $privacy = isset($_POST['privacy']) ? true : false;
        
        // Debug info
        $debug_info[] = "Form data received and sanitized";        // Basic validation
        if (empty($first_name)) {
            $errors[] = "First name is required";
        }
        
        if (empty($last_name)) {
            $errors[] = "Last name is required";
        }
        
>>>>>>> 62cf84f8be47b7893b6cfbc324a6d4b9ce2ed352
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
        
        // If no errors, proceed with saving the data
        if (empty($errors)) {
            // Get current date and time
            $current_datetime = date('Y-m-d H:i:s');            // Construct full name with middle initial if available
            $full_name = $first_name;
            if (!empty($middle_initial)) {
                $full_name .= ' ' . $middle_initial . '.';
            }
            $full_name .= ' ' . $last_name;
            
            // Prepare data for insertion - DO NOT include the ID field
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
            
<<<<<<< HEAD
            // Insert record using direct query to avoid the PRIMARY KEY issue
=======
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
>>>>>>> 62cf84f8be47b7893b6cfbc324a6d4b9ce2ed352
            $columns = implode(', ', array_map(function($col) {
                return "`$col`";
            }, array_keys($support_data)));
            
            $placeholders = implode(', ', array_fill(0, count($support_data), '?'));
            
            $sql = "INSERT INTO `tech_support_requests` ($columns) VALUES ($placeholders)";
            
            // Prepare statement
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            
            // Bind parameters
            $types = '';
            $values = [];
            
            foreach ($support_data as $value) {
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
            
            // Create bind_param arguments
            $bind_params = array($types);
            foreach ($values as &$value) {
                $bind_params[] = &$value;
            }
            
            // Bind parameters
            if (!call_user_func_array(array($stmt, 'bind_param'), $bind_params)) {
                throw new Exception("Error binding parameters: " . $stmt->error);
            }
            
            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }
            
            // Close statement
            $stmt->close();
            
            // Success - store message in session and redirect
            $_SESSION['success_message'] = "Your support request has been submitted successfully. Our team will contact you soon.";
            header('Location: tech-support.php?status=success');
            exit();
        }
    } catch (Exception $e) {
        $errors[] = "An error occurred while submitting your request. Please try again. Error: " . $e->getMessage();
        error_log("Error in submit_support.php: " . $e->getMessage());
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