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
        // Validate and sanitize inputs
        $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : 'support@example.com'; // Default email
        $agency = isset($_POST['agency']) ? sanitize_input($_POST['agency']) : '';
        $region = isset($_POST['region']) ? sanitize_input($_POST['region']) : '';
        $province_id = isset($_POST['province_id']) && !empty($_POST['province_id']) ? sanitize_input($_POST['province_id']) : null;
        $district_id = isset($_POST['district_id']) && !empty($_POST['district_id']) ? sanitize_input($_POST['district_id']) : null;
        $municipality_id = isset($_POST['municipality_id']) && !empty($_POST['municipality_id']) ? sanitize_input($_POST['municipality_id']) : null;
        $support_type = isset($_POST['support_type']) ? sanitize_input($_POST['support_type']) : '';
        $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
        $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '1234567890'; // Default phone
        $privacy = isset($_POST['privacy']) ? true : false;
        
        // Basic validation
        if (empty($name)) {
            $errors[] = "Full name is required";
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
        
        // If no errors, proceed with saving the data
        if (empty($errors)) {
            // Get current date and time
            $current_datetime = date('Y-m-d H:i:s');
            
            // Prepare data for insertion - DO NOT include the ID field
            $support_data = [
                'client_name' => $name,
                'agency' => $agency,
                'email' => $email,
                'phone' => $phone,
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
            
            // Insert record using direct query to avoid the PRIMARY KEY issue
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