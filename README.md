# MDRRM-ARMS - Municipal Disaster Risk Reduction and Management – Annex Reporting and Monitoring System

A comprehensive web-based disaster reporting and management system designed for Municipal Disaster Risk Reduction and Management in compliance with Memorandum Circular No. 05, s. 2025.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Security Features](#security-features)
- [API Endpoints](#api-endpoints)
- [Database Schema](#database-schema)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

This system provides a secure, user-friendly platform for disaster monitoring and reporting. It implements all 21 reporting templates (Annexes 1-21) for comprehensive disaster management, including incident reporting, damage assessment, status monitoring, and assistance tracking.

The system features role-based access control, real-time weather integration, audit logging, and advanced security measures to ensure data integrity and compliance with government standards.

## ✨ Features

### Core Functionality
- **21 Complete Annex Forms**: Full implementation of MC No. 05, s. 2025 reporting templates
- **Role-Based Access Control**: Admin and regular user roles with appropriate permissions
- **Barangay Management**: Admin can lock/select specific barangays for reporting
- **Real-time Weather Integration**: PAGASA weather API integration for disaster monitoring
- **Comprehensive Records Management**: View, archive, and export submitted reports

### Reporting Modules
- **Annex 1**: Related Incidents (landslides, flooding, fires, etc.)
- **Annex 2**: Casualty Report
- **Annex 3**: Assistance Provided
- **Annex 4**: Damaged Houses
- **Annex 5**: Damaged Agriculture
- **Annex 6**: Damaged Infrastructure
- **Annex 7**: Status of Roads and Bridges
- **Annex 8**: Status of Other Infrastructure
- **Annex 9**: Status of Critical Facilities
- **Annex 11**: Evacuation Centers
- **Annex 14**: Status of Power Supply
- **Annex 15**: Status of Communication Systems
- **Annex 18**: Status of Work Suspension
- **Annex 19**: Status of Education Facilities
- **Annex 21**: Status of Water Supply

### Security & Compliance
- **Advanced Session Security**: Fingerprint validation, secure cookies, CSRF protection
- **Rate Limiting**: Prevents abuse and DoS attacks
- **Audit Logging**: Complete tracking of user actions and system events
- **Input Validation**: Comprehensive sanitization and validation
- **File Security**: Secure upload handling with integrity checks
- **Error Handling**: Production-safe error management

### User Experience
- **Responsive Design**: Mobile-friendly Bootstrap interface
- **Skeleton Loading**: Smooth loading animations
- **Flash Messages**: User feedback for actions
- **Technical Notes**: Built-in guidance for each form
- **Export Functionality**: PDF and Excel export capabilities

## 🛠 Technologies Used

### Backend
- **PHP 8.0+**: Server-side scripting
- **MySQL/MariaDB**: Database management
- **PDO**: Secure database abstraction layer

### Frontend
- **HTML5/CSS3**: Semantic markup and styling
- **Bootstrap 5**: Responsive framework
- **JavaScript**: Dynamic interactions
- **jQuery**: DOM manipulation (if used)

### Security & Libraries
- **PHPMailer**: Email functionality (if implemented)
- **Composer**: Dependency management
- **Custom Security Framework**: Built-in security functions

### External Integrations
- **PAGASA Weather API**: Real-time weather data
- **Bootstrap Icons**: Icon library

## 📦 Installation

### Prerequisites
- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.0+
- **Composer**: For dependency management (if applicable)

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd ndrrmc-annex-system
   ```

2. **Configure Web Server**
   - Point document root to the `public/` directory
   - Ensure `.htaccess` is enabled for Apache
   - Configure URL rewriting if needed

3. **Database Setup**
   ```sql
   -- Create database
   CREATE DATABASE annex_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

   -- Import schema (if provided)
   -- Run the SQL files in the database/ directory
   ```

4. **Environment Configuration**
   - Copy `includes/config.php` and adjust settings
   - Set database credentials
   - Configure security settings for production

5. **File Permissions**
   ```bash
   chmod 755 logs/
   chmod 755 uploads/
   chown www-data:www-data logs/
   chown www-data:www-data uploads/
   ```

6. **Initial Setup**
   - Access the application through your web browser
   - Run any database migrations if applicable
   - Create admin user account

## ⚙️ Configuration

### Database Configuration (`includes/config.php`)
```php
$db_host = 'localhost';
$db_name = 'annex_management_system';
$db_user = 'your_db_user';
$db_pass = 'your_secure_password';
```

### Security Settings
- Set `DEBUG_MODE = false` in production
- Configure secure session settings
- Set up proper file permissions
- Enable HTTPS in production

### Weather API Configuration
- Obtain PAGASA API credentials
- Configure API endpoints in `fetch_weather_api.php`
- Set appropriate rate limits

## 🚀 Usage

### For Regular Users
1. **Login**: Access the system with your credentials
2. **Dashboard**: View available reporting forms
3. **Submit Reports**: Fill out appropriate annex forms
4. **View Records**: Check submitted and archived reports

### For Administrators
1. **User Management**: Create and manage user accounts
2. **Barangay Locking**: Control which barangays can submit reports
3. **System Monitoring**: View audit logs and security events
4. **Report Generation**: Export comprehensive reports

### Form Submission Process
1. Select the appropriate annex from the sidebar
2. Fill in location details (auto-populated for regular users)
3. Complete incident/assessment details
4. Review technical notes for guidance
5. Submit the form with validation feedback

## 📁 Project Structure

```
ndrrmc-annex-system/
├── admin/                    # Administrative functions
│   └── barangay_locking.php
├── api/                      # REST API endpoints
│   ├── annex1_edit.php
│   ├── annex1_save.php
│   └── ... (all annex APIs)
├── includes/                 # Core system files
│   ├── config.php           # Database & security config
│   ├── functions.php        # Utility functions
│   ├── auth.php             # Authentication
│   ├── security_headers.php # Security headers
│   └── ...
├── logs/                    # Error and audit logs
├── print_templates/         # PDF templates
├── public/                  # Public web interface
│   ├── annex1.php          # Annex 1 form
│   ├── dashboard.php       # Main dashboard
│   ├── login.php           # Authentication
│   ├── records.php         # Report viewing
│   └── css/                # Stylesheets
├── report/                  # Reporting system
│   ├── sitrep.php          # Situation reports
│   └── sitrep_settings.php
├── templates/               # HTML templates
├── uploads/                 # File uploads (secured)
├── .htaccess               # Apache configuration
├── .gitignore             # Git ignore rules
└── README.md              # This file
```

## 🔒 Security Features

### Authentication & Authorization
- **Secure Password Policies**: Complexity requirements and expiration
- **Session Management**: Fingerprint validation and regeneration
- **Role-Based Access**: Admin vs regular user permissions
- **Barangay Restrictions**: Users limited to assigned barangays

### Data Protection
- **Input Sanitization**: XSS prevention and data validation
- **Prepared Statements**: SQL injection protection
- **CSRF Protection**: Token-based form validation
- **Rate Limiting**: Prevents brute force and DoS attacks

### File Security
- **Upload Validation**: File type, size, and integrity checks
- **Secure Storage**: Files stored outside web root
- **Access Control**: Proper file permissions

### Monitoring & Logging
- **Audit Trails**: Complete user action logging
- **Security Events**: Intrusion detection logging
- **Error Handling**: Safe error display in production

## 🔌 API Endpoints

### Annex Management APIs
- `POST /api/annex1_save.php` - Save Annex 1 reports
- `POST /api/annex2_save.php` - Save Annex 2 reports
- `GET /api/annex1_view.php` - View Annex 1 data
- `PUT /api/annex1_update.php` - Update Annex 1 entries

### Weather Integration
- `GET /public/fetch_weather_api.php` - Get weather data

### Authentication
- `POST /public/login.php` - User authentication
- `POST /public/logout.php` - User logout

## 🗄️ Database Schema

### Core Tables
- `users` - User accounts and roles
- `annex1_related_incidents` - Incident reports
- `annex2_casualty_report` - Casualty data
- `annex3_assistance_provided` - Assistance tracking

### Security Tables
- `audit_logs` - User action tracking
- `security_logs` - Security events
- `rate_limits` - Rate limiting data

### System Tables
- `barangay_locks` - Admin barangay controls
- `user_sessions` - Session management

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation
- Ensure security compliance

## 📄 License

This project is proprietary software developed for Municipal Disaster Risk Reduction and Management. All rights reserved.

## 📞 Support

For technical support or questions:
- Create an issue in the repository
- Contact the development team
- Refer to the technical documentation

## 🔄 Version History

- **v1.0.0**: Initial release with all 21 annex forms
- Security hardening and audit logging
- Weather API integration
- Admin panel and user management

---

**Note**: This system is designed for official use by authorized Municipal Disaster Risk Reduction and Management personnel only. Ensure compliance with all relevant data protection and privacy regulations.
