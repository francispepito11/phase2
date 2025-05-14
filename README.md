# DICT Client Management System

## Overview
The DICT Client Management System is a web-based application designed for the Department of Information and Communications Technology to manage client service requests, technical support, and service tracking. This system allows DICT staff to efficiently record, track, and respond to various technical support and service requests from government agencies and the public.

## Features

### Public-facing Features
- **Service Request Submission**: Allows clients to submit various types of service requests
- **Technical Support**: Dedicated module for technical support requests
- **Service Tracking**: Clients can track the status of their submitted requests

### Administrative Features
- **Dashboard**: Overview of service requests, stats, and analytics
- **Service Management**: View, edit, and manage service requests
- **User Management**: Admin can manage system users
- **Reporting**: Generate reports on service delivery performance

## Services Provided
The system supports various technical services including:
- WiFi Installation/Configuration
- GovNet Installation/Maintenance
- iBPLS Virtual Assistance
- PNPKI Tech Support
- ICT Equipment Lending
- Office Facility Usage
- Sim Card Registration
- Communications-related Support
- Cybersecurity Support
- Technical Personnel Provision
- And more...

## Technical Information

### System Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx)
- Modern web browser

### Installation

1. Clone the repository or extract files to your web server directory
2. Create a MySQL database named `dict`
3. Import the database schema (SQL file provided separately)
4. Configure database connection in `includes/db_connect.php`
5. Access the system through a web browser

### Database Configuration
The system uses a MySQL database with the following default configuration:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dict";
```

## File Structure
- **admin/**: Administrative backend files
  - dashboard.php: Admin dashboard
  - login.php: Admin authentication
- **includes/**: Shared components
  - db_connect.php: Database connection
  - crud_operations.php: Database CRUD operations
- **assets/**: Static assets
  - img/: Images including logos
- **index.php**: Homepage
- **services_list.php**: View list of services
- **services_provided.php**: Record services provided
- **services_request.php**: Client service request form
- **tech_support.php**: Technical support request form
- **view_service.php**: View service details
- **edit_service.php**: Edit service details
- **delete_service.php**: Delete service records

## Usage

### Client Side
1. Navigate to the homepage
2. Select the desired service type
3. Fill out the service request form
4. Submit the request
5. Use the provided reference number to track request status

### Admin Side
1. Login to the admin panel at `/admin/login.php`
2. Default credentials: 
   - Username: admin
   - Password: password
3. View dashboard for system overview
4. Manage service requests through the appropriate sections
5. Update request status as they progress

## Security Considerations
- This system implements input sanitization for all form submissions
- Admin access is secured by session-based authentication
- Database queries use prepared statements to prevent SQL injection

## Future Enhancements
- Email notification system
- Mobile application client
- Integration with other government information systems
- Enhanced reporting and analytics
- Client self-service portal

## Support
For technical support or inquiries, please contact the system administrator or DICT helpdesk.

## License
© Department of Information and Communications Technology. All rights reserved.


brobro