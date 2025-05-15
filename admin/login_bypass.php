<?php
// login_bypass.php - For testing and emergency access only
// WARNING: Do not use in production environments

// Start session
session_start();

// Set session variables to simulate successful login
$_SESSION["loggedin"] = true;
$_SESSION["username"] = "admin";

// Log the bypass attempt (optional but recommended for security auditing)
$log_message = date('Y-m-d H:i:s') . " - Login bypass used from IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
error_log($log_message, 3, "bypass_log.txt");

// Redirect to dashboard
header("Location: dashboard.php");
exit;
?>
