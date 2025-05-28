<?php
// Start session to store messages
session_start();

// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Get PDO connection from db_connect.php
$pdo = get_db_connection();

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
        $birthdate = isset($_POST['birthdate']) ? sanitize_input($_POST['birthdate']) : '';
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : 'support@example.com';
        $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '1234567890';
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
        }
        if (empty($subject)) {
            $errors[] = "Subject is required";
        }
        if (empty($gender)) {
            $errors[] = "Gender is required";
        }
        if (empty($birthdate)) {
            $errors[] = "Birth date is required";
        } else {
            // Validate date format (YYYY-MM-DD) and not in the future
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate) || strtotime($birthdate) > time()) {
                $errors[] = "Please enter a valid birth date";
            }
        }
        if (!$privacy) {
            $errors[] = "You must consent to the privacy policy";
        }

        // If no errors, proceed with saving the data
        if (empty($errors)) {
            // Get current date and time
            $current_datetime = date('Y-m-d H:i:s');

            // Check if client already exists
            $client_name = $first_name . ' ' . $middle_initial . ' ' . $surname;
            $existing_client = null;
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE firstname = ? AND surname = ? AND middle_initial = ?");
                $stmt->execute([$first_name, $surname, $middle_initial]);
                $existing_client = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error checking for existing client: " . $e->getMessage());
            }

            // Client data for insertion or reference
            $client_data = [
                'firstname' => $first_name,
                'surname' => $surname,
                'middle_initial' => $middle_initial,
                'client_name' => $client_name,
                'agency' => $agency,
                'gender' => $gender,
                'birthdate' => $birthdate,
                'region' => $region,
                'region_id' => $region,
                'province_id' => $province_id,
                'district_id' => $district_id,
                'municipality_id' => $municipality_id,
                'email' => $email,
                'phone' => $phone,
                'created_at' => $current_datetime
            ];

            // Get or create client record
            $client_id = null;
            if ($existing_client) {
                $client_id = $existing_client['id'];
                // Update existing client info
                $update_data = array_intersect_key($client_data, array_flip(['agency', 'email', 'phone']));
                if (!empty($update_data)) {
                    update_record('clients', $client_id, $update_data);
                }
            } else {
                // Insert new client record
                $client_id = create_record('clients', $client_data);
                if ($client_id === false) {
                    throw new Exception("Failed to create client record");
                }
            }

            // Prepare support request data
            $support_data = [
                'client_id' => $client_id,
                'support_type' => $support_type,
                'subject' => $subject,
                'message' => $message,
                'status' => 'Pending',
                'date_requested' => $current_datetime,
                'created_at' => $current_datetime
            ];

            // Insert support request record
            $result = create_record('tech_support_requests', $support_data);
            if ($result === false) {
                throw new Exception("Failed to create support request record");
            }

            // Success - store message in session and redirect
            $_SESSION['success_message'] = "Your support request has been submitted successfully. Our team will contact you soon.";
            header('Location: tech-support.php?status=success&client_name=' . urlencode($client_name));
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