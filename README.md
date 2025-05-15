# DICT Client Management System

## Overview
The DICT Client Management System is a web-based application designed for the Department of Information and Communications Technology to manage client service requests, technical support, and service tracking. This system allows DICT staff to efficiently record, track, and respond to various technical support and service requests from government agencies and the public.

**Version:** 1.0.0 (Last Updated: May 2025)

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
- Web server (Apache/XAMPP recommended)
- Modern web browser (Chrome, Firefox, Edge)
- Minimum 2GB RAM, 10GB storage space

### Installation

1. Clone the repository or extract files to your web server directory (e.g., `c:\xampp\htdocs\`)
2. Create a MySQL database named `dict`
3. Import the database schema from `dict.sql` file included in the repository
4. Configure database connection in `includes/db_connect.php` if needed
5. Start your Apache and MySQL services (if using XAMPP, use the XAMPP Control Panel)
6. Access the system through a web browser at `http://localhost/phase2-1/`

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
  - logout.php: Admin logout functionality
- **includes/**: Shared components
  - db_connect.php: Database connection
  - crud_operations.php: Database CRUD operations
- **assets/**: Static assets
  - img/: Images including logos
- **images/**: Additional image resources
  - dict-logo.png: DICT logo
- **uploads/**: Storage for uploaded files
  - support/: Support-related uploads
- **spike-nuxtjs-free-1.0.0/**: Frontend template resources (Nuxt.js)
- **index.php**: Homepage
- **services_list.php**: View list of services
- **services_provided.php**: Record services provided
- **services_request.php**: Client service request form
- **tech_support.php**: Technical support request form
- **training.php**: Training-related services
- **view_service.php**: View service details
- **edit_service.php**: Edit service details
- **delete_service.php**: Delete service records
- **dict.sql**: Database schema

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
- Email notification system for service request updates
- Mobile application client for on-the-go access
- Integration with other government information systems
- Enhanced reporting and analytics dashboard
- Client self-service portal
- Two-factor authentication for admin access
- API development for third-party integrations
- Load balancing for improved performance

## Development Roadmap
- **Q3 2025**: Email notification system implementation
- **Q4 2025**: Enhanced reporting and analytics
- **Q1 2026**: Client self-service portal development
- **Q2 2026**: Mobile application release

## Troubleshooting
- **Database Connection Issues**: Verify credentials in `includes/db_connect.php`
- **File Upload Errors**: Check folder permissions for the `uploads` directory
- **Session Timeout**: Adjust timeout settings in PHP configuration
- **Login Problems**: Reset password through database if necessary

## Support
For technical support or inquiries, please contact:
- System Administrator: admin@dict.gov.ph
- DICT Helpdesk: helpdesk@dict.gov.ph or call (02) 8920-0101

## License
© Department of Information and Communications Technology (2023-2025). All rights reserved.