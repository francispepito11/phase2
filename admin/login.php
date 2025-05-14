<?php
// Start session
session_start();

// Check if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

// TEMPORARY: Auto-login bypass code has been removed
// Now the actual login form will work

// Initialize variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // In a real application, you would check against a database
        // For this example, we'll use hardcoded credentials
        if ($username === "admin" && $password === "password") {
            // Password is correct, start a new session
            session_start();
            
            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $username;
            
            // Redirect user to dashboard
            header("location: dashboard.php");
        } else {
            // Username or password is invalid
            $login_err = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DICT Client Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-md">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                DICT Client Management System
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Admin Login
            </p>
        </div>
        
        <?php if (!empty($login_err)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $login_err; ?></span>
            </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input id="username" name="username" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Username" value="<?php echo $username; ?>">
                    <?php if (!empty($username_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $username_err; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Password">
                    <?php if (!empty($password_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $password_err; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="font-medium text-sm text-blue-600 hover:text-blue-500">
                Back to Homepage
            </a>
        </div>
    </div>
</body>
</html>