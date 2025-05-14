<?php
// Include database connection and CRUD operations
require_once 'includes/db_connect.php';
require_once 'includes/crud_operations.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Process form submission for event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    try {
        // Sanitize and validate input data
        $event_id = sanitize_input($_POST['event_id']);
        $participant_name = sanitize_input($_POST['participant_name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $agency = sanitize_input($_POST['agency']);
        $position = sanitize_input($_POST['position']);
        
        // Here you would insert this data into a 'training_registrations' table
        // For now, we'll just set a success message
        $success_message = "Registration successful! You are now registered for the training event.";
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get upcoming training events (mock data for now)
// In a real implementation, you would fetch this from a database table
$upcoming_events = [
    [
        'id' => 1,
        'title' => 'Basic ICT Literacy Training',
        'description' => 'Introduction to basic computer operations, internet usage, and common office applications.',
        'date' => '2025-05-25',
        'time' => '9:00 AM - 4:00 PM',
        'location' => 'DICT Training Room A, Quezon City',
        'seats_available' => 20,
        'type' => 'in-person'
    ],
    [
        'id' => 2,
        'title' => 'Cybersecurity Awareness Seminar',
        'description' => 'Learn about the latest cybersecurity threats and how to protect yourself and your organization.',
        'date' => '2025-06-05',
        'time' => '1:00 PM - 5:00 PM',
        'location' => 'Virtual (Zoom)',
        'seats_available' => 100,
        'type' => 'virtual'
    ],
    [
        'id' => 3,
        'title' => 'Government Website Development Workshop',
        'description' => 'Hands-on workshop on creating and maintaining government websites using modern web technologies.',
        'date' => '2025-06-15',
        'time' => '9:00 AM - 4:00 PM',
        'location' => 'DICT Training Room B, Quezon City',
        'seats_available' => 15,
        'type' => 'in-person'
    ],
    [
        'id' => 4,
        'title' => 'Data Privacy and Protection Training',
        'description' => 'Comprehensive training on data privacy laws, regulations, and implementation in government agencies.',
        'date' => '2025-06-20',
        'time' => '9:00 AM - 12:00 PM',
        'location' => 'Virtual (Zoom)',
        'seats_available' => 50,
        'type' => 'virtual'
    ]
];

// Get past training events (mock data for now)
$past_events = [
    [
        'id' => 101,
        'title' => 'E-Government Systems Introduction',
        'description' => 'Overview of e-government systems and digital transformation in public service.',
        'date' => '2025-04-12',
        'time' => '9:00 AM - 4:00 PM',
        'location' => 'DICT Main Office, Quezon City',
        'participants' => 45,
        'type' => 'in-person'
    ],
    [
        'id' => 102,
        'title' => 'ICT Project Management for Government',
        'description' => 'Best practices in managing ICT projects within government agencies.',
        'date' => '2025-03-22',
        'time' => '1:00 PM - 5:00 PM',
        'location' => 'Virtual (MS Teams)',
        'participants' => 78,
        'type' => 'virtual'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Training - DICT Client Management System</title>
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
                        <a href="training.php" class="border-b-2 border-white px-1 pt-1 text-sm font-medium">Training</a>
                        <a href="tech-support.php" class="border-transparent border-b-2 hover:border-gray-300 px-1 pt-1 text-sm font-medium">Tech Support</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <a href="index.php" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Event Training</h1>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Training Overview Section -->
        <section class="mb-12">
            <div class="bg-blue-700 rounded-lg text-white p-8">
                <h2 class="text-3xl font-bold mb-4">DICT Training Programs</h2>
                <p class="text-lg mb-6">Enhance your ICT skills through our comprehensive training programs designed for government employees and the public.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="bg-blue-800 bg-opacity-50 p-6 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">ICT Literacy</h3>
                        <p>Basic to advanced computer skills training for improved productivity</p>
                    </div>
                    <div class="bg-blue-800 bg-opacity-50 p-6 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">Cybersecurity</h3>
                        <p>Training on protecting digital assets and ensuring data security</p>
                    </div>
                    <div class="bg-blue-800 bg-opacity-50 p-6 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">Digital Transformation</h3>
                        <p>Guidance on implementing digital solutions in government services</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Upcoming Training Events -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Upcoming Training Events</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($upcoming_events as $event): ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p class="text-sm text-blue-600 font-medium">
                                        <?php echo date('F j, Y', strtotime($event['date'])); ?> • <?php echo htmlspecialchars($event['time']); ?>
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $event['type'] === 'virtual' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($event['type'])); ?>
                                </span>
                            </div>
                            <p class="mt-3 text-gray-500"><?php echo htmlspecialchars($event['description']); ?></p>
                            <div class="mt-4 flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <?php echo htmlspecialchars($event['seats_available']); ?> seats available
                            </div>
                            <div class="mt-6">
                                <button onclick="openRegistrationModal(<?php echo $event['id']; ?>, '<?php echo addslashes($event['title']); ?>')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Register Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Past Training Events -->
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Past Training Events</h2>
            <div class="overflow-hidden bg-white shadow-md sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participants</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($past_events as $event): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($event['description']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('F j, Y', strtotime($event['date'])); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($event['time']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($event['participants']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $event['type'] === 'virtual' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($event['type'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Registration Modal -->
    <div id="registrationModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center" style="z-index: 9999;">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Register for Event</h3>
                    <button onclick="closeRegistrationModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" action="training.php" class="px-6 py-4">
                <input type="hidden" name="event_id" id="eventId">
                <div class="mb-4">
                    <label for="participant_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="participant_name" id="participant_name" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="agency" class="block text-sm font-medium text-gray-700">Agency/Organization</label>
                    <input type="text" name="agency" id="agency" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="position" class="block text-sm font-medium text-gray-700">Position/Designation</label>
                    <input type="text" name="position" id="position" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="py-3 bg-gray-50 text-right">
                    <button type="submit" name="register_event" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-800 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">DICT Client Management System</h3>
                    <p class="text-sm text-blue-100">
                        Providing technical support, services, and training to government agencies and the public.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="text-blue-100 hover:text-white">Home</a></li>
                        <li><a href="services_provided.php" class="text-blue-100 hover:text-white">Services</a></li>
                        <li><a href="training.php" class="text-blue-100 hover:text-white">Training</a></li>
                        <li><a href="tech-support.php" class="text-blue-100 hover:text-white">Tech Support</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <address class="text-sm text-blue-100 not-italic">
                        <p>Department of Information and Communications Technology</p>
                        <p>C.P. Garcia Avenue, Diliman, Quezon City</p>
                        <p>Email: info@dict.gov.ph</p>
                        <p>Phone: (02) 8920-0101</p>
                    </address>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-blue-700 text-center text-sm text-blue-100">
                <p>&copy; <?php echo date('Y'); ?> Department of Information and Communications Technology. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for the registration modal -->
    <script>
        function openRegistrationModal(eventId, eventTitle) {
            document.getElementById('modalTitle').textContent = 'Register for: ' + eventTitle;
            document.getElementById('eventId').value = eventId;
            document.getElementById('registrationModal').classList.remove('hidden');
        }
        
        function closeRegistrationModal() {
            document.getElementById('registrationModal').classList.add('hidden');
        }
    </script>
</body>
</html>
