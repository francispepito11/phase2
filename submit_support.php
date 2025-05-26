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
        // Log the start of form processing
        error_log("Processing support form submission at " . date('Y-m-d H:i:s'));

        // Validate and sanitize inputs
        $first_name = isset($_POST['firstname']) ? sanitize_input($_POST['firstname']) : '';
        $surname = isset($_POST['surname']) ? sanitize_input($_POST['surname']) : '';
        $middle_initial = isset($_POST['middle_initial']) ? sanitize_input($_POST['middle_initial']) : '';
        $agency = isset($_POST['agency']) ? sanitize_input($_POST['agency']) : '';
        $region = isset($_POST['region']) ? sanitize_input($_POST['region']) : '';
        $province_id = isset($_POST['province_id']) && !empty($_POST['province_id']) ? sanitize_input($_POST['province_id']) : null;
        $district_id = isset($_POST['district_id']) && !empty($_POST['district_id']) ? sanitize_input($_POST['district_id']) : null;
        $municipality_id = isset($_POST['municipality_id']) && !empty($_POST['municipality_id']) ? sanitize_input($_POST['municipality_id']) : null;
        $support_type = isset($_POST['support_type']) ? sanitize_input($_POST['support_type']) : '';
        $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
        $gender = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
        $age = isset($_POST['age']) && !empty($_POST['age']) ? (int)sanitize_input($_POST['age']) : null;
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : 'support@example.com'; // Default email
        $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '1234567890'; // Default phone
        $privacy = isset($_POST['privacy']) ? true : false;

        // Basic validation
        if (empty($first_name)) {
            $errors[] = "First name is required";
        }
        if (empty($surname)) {
            $errors[] = "Surname is required";
        }
        if (empty($agency)) {
            $errors[] = "Agency/Organization is required";
        }
        if (empty($region)) {
            $errors[] = "Region is required";
        }
        if (empty($support_type)) {
            $errors[] = "Support type is required";
        }        if (empty($subject)) {
            $errors[] = "Subject is required";
        }
        if (empty($gender)) {
            $errors[] = "Gender is required";
        }
        if (empty($age) || $age < 1 || $age > 120) {
            $errors[] = "Age must be between 1 and 120";
        }
        if (!$privacy) {
            $errors[] = "You must consent to the privacy policy";
        }

        // If no errors, proceed with saving the data
        if (empty($errors)) {
            // Get current date and time
            $current_datetime = date('Y-m-d H:i:s');

            // Prepare data for insertion
            $support_data = [
                'firstname' => $first_name,
                'surname' => $surname,
                'middle_initial' => $middle_initial,
                'client_name' => $first_name . ' ' . $middle_initial . ' ' . $surname, 
                'agency' => $agency,
                'gender' => $gender,
                'age' => $age,
                'region' => $region,
                'region_id' => $region,
                'province_id' => $province_id,
                'district_id' => $district_id,                'municipality_id' => $municipality_id,                'support_type' => $support_type,
                'subject' => $subject,
                'message' => $message,
                'status' => 'Pending',
                'date_requested' => $current_datetime
            ];

            // Insert record using CRUD function
            $result = create_record('tech_support_requests', $support_data);

            if ($result === false) {
                throw new Exception("Failed to create support request record");
            }

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