<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DICT Client Management System</title>
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
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-blue-700 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 flex flex-col items-center">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                    DICT Client Services Portal
                </h1>
                <p class="mt-6 text-xl max-w-3xl">
                    Providing technical support and services to government agencies and the public.
                </p>
            </div>
            
            <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-8 w-full max-w-5xl">
                <!-- Services Provided Button -->
                <a href="services_provided.php" class="bg-white text-blue-700 hover:bg-gray-100 rounded-lg shadow-lg overflow-hidden flex flex-col items-center text-center p-8 transition-all duration-200 transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h2 class="text-xl font-bold">Services Provided</h2>
                    <p class="mt-2 text-sm text-gray-600">Request and track services provided by DICT</p>
                </a>
                
                <!-- Tech Support Button -->
                <a href="tech-support.php" class="bg-white text-blue-700 hover:bg-gray-100 rounded-lg shadow-lg overflow-hidden flex flex-col items-center text-center p-8 transition-all duration-200 transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h2 class="text-xl font-bold">Tech Support</h2>
                    <p class="mt-2 text-sm text-gray-600">Get technical assistance and support</p>
                </a>
                
                <!-- Event Training Button -->
                <a href="training.php" class="bg-white text-blue-700 hover:bg-gray-100 rounded-lg shadow-lg overflow-hidden flex flex-col items-center text-center p-8 transition-all duration-200 transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <h2 class="text-xl font-bold">Event Training</h2>
                    <p class="mt-2 text-sm text-gray-600">Register for ICT training events and seminars</p>
                </a>
                
                <!-- Admin Login Button -->
                <a href="admin/login.php" class="bg-white text-blue-700 hover:bg-gray-100 rounded-lg shadow-lg overflow-hidden flex flex-col items-center text-center p-8 transition-all duration-200 transform hover:scale-105">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-xl font-bold">Admin Login</h2>
                    <p class="mt-2 text-sm text-gray-600">Login to the admin dashboard</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-800 text-white">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Department of Information and Communications Technology. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>