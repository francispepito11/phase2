<?php
// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Initialize error array
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {    // Validate and sanitize inputs
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $agency = isset($_POST['agency']) ? sanitize_input($_POST['agency']) : '';
    $region = isset($_POST['region']) ? sanitize_input($_POST['region']) : '';
    $province_id = isset($_POST['province_id']) ? sanitize_input($_POST['province_id']) : 0;
    $district_id = isset($_POST['district_id']) ? sanitize_input($_POST['district_id']) : null;
    $municipality_id = isset($_POST['municipality_id']) ? sanitize_input($_POST['municipality_id']) : 0;
    $support_type = isset($_POST['support_type']) ? sanitize_input($_POST['support_type']) : '';
    $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    
    // Basic validation
    if (empty($name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
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
    
    // If no errors, proceed with saving the data
    if (empty($errors)) {        // Get region ID from region code (simple approach)
        $region_id = $region; // Region ID is now directly passed from the form
        
        // Default values for optional fields are already set during validation
        
        // Prepare data for insertion
        $support_data = [
            'client_name' => $name,
            'agency' => $agency,
            'email' => $email,
            'phone' => $phone,
            'region_id' => $region_id,
            'province_id' => $province_id,
            'district_id' => $district_id,
            'municipality_id' => $municipality_id,
            'support_type' => $support_type,
            'issue_description' => $message,
            'status' => 'Pending'
        ];
        
        // Handle file upload if present
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
            $max_file_size = 5 * 1024 * 1024; // 5MB
            
            // Validate file type and size
            if (in_array($_FILES['attachment']['type'], $allowed_types) && $_FILES['attachment']['size'] <= $max_file_size) {
                // Create uploads directory if not exists
                $upload_dir = 'uploads/support/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $unique_filename = uniqid('support_') . '.' . $file_extension;
                $upload_path = $upload_dir . $unique_filename;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                    $attachment_path = $upload_path;
                    $support_data['attachment'] = $attachment_path;
                } else {
                    $errors[] = "Failed to upload file. Please try again.";
                }
            } else {
                $errors[] = "Invalid file. Please upload a PDF, JPG, or PNG file (max 5MB).";
            }
        }
        
        // Insert support request into database
        if (empty($errors)) {
            $result = create_record('tech_support_requests', $support_data);
            
            if ($result) {
                // Redirect to success page
                $_SESSION['success_message'] = "Your support request has been submitted successfully. Our team will contact you soon.";
                header('Location: tech-support.php?status=success');
                exit();
            } else {
                $errors[] = "An error occurred while submitting your request. Please try again.";
            }
        }
    }
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
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                Your support request has been submitted successfully. Our team will contact you soon.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-6">
                    <a href="tech-support.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Return to Tech Support
                    </a>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
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
