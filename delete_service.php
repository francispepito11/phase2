<?php
// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: services_list.php');
    exit;
}

$id = (int)$_GET['id'];

// Get service details to confirm it exists
$service = get_record_by_id('services_provided', $id);

// If service not found, redirect to services list
if (!$service) {
    header('Location: services_list.php?error=Service not found');
    exit;
}

// Process deletion
if (delete_record('services_provided', $id)) {
    header('Location: services_list.php?success=Service deleted successfully');
} else {
    header('Location: services_list.php?error=Failed to delete service');
}
exit;
?>