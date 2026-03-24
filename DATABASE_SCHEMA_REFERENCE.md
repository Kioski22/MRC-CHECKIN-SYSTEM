# DATABASE SCHEMA REFERENCE
**Generated on: 2025-10-14**  
**Database: psmenow_natcon**  
**Host: 20.24.15.42**  

## Overview
This document provides a comprehensive reference for the PSME National Convention database schema. The database supports a food registration and check-in management system with external API integration capabilities.

---

## TABLES

### 1. `food` Table
**Purpose**: Stores food registration records for the national convention

| Column | Type | Null | Key | Default | Extra | Description |
|--------|------|------|-----|---------|-------|-------------|
| `id` | int(11) | NO | PRI | NULL | auto_increment | Primary key, unique identifier |
| `email_address` | varchar(255) | NO | UNI | NULL | | Registrant's email address (unique) |
| `first_name` | varchar(100) | NO | | NULL | | Registrant's first name |
| `middle_name` | varchar(100) | YES | | NULL | | Registrant's middle name (optional) |
| `last_name` | varchar(100) | NO | | NULL | | Registrant's last name |
| `suffix` | varchar(20) | YES | | NULL | | Name suffix (Jr., Sr., III, etc.) |
| `mapping` | varchar(255) | YES | | NULL | | Email mapping for external API correlation |

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `email_address` (`email_address`)

**Relationships**: None (standalone table)

**Business Rules**:
- Email address must be unique across all registrations
- First name, last name, and email address are required
- Mapping field is used for correlating with external PSME API data
- When mapping is NULL or empty, the record is considered "unmapped"

---

### 2. `checkins` Table
**Purpose**: Stores check-in records and kit release information

| Column | Type | Null | Key | Default | Extra | Description |
|--------|------|------|-----|---------|-------|-------------|
| `id` | int(11) | NO | PRI | NULL | auto_increment | Primary key, unique identifier |
| `member_id` | varchar(50) | YES | | NULL | | Member identification number |
| `first_name` | varchar(100) | NO | | NULL | | Member's first name |
| `last_name` | varchar(100) | NO | | NULL | | Member's last name |
| `email_address` | varchar(255) | YES | | NULL | | Member's email address |
| `checkin_time` | datetime | YES | | NULL | | Timestamp when member checked in |
| `kit_release_time` | datetime | YES | | NULL | | Timestamp when kit was released |
| `qr_code` | varchar(255) | YES | | NULL | | QR code for member identification |
| `status` | enum('pending','checked_in','kit_released') | YES | | 'pending' | | Current status of the member |

**Indexes**:
- PRIMARY KEY (`id`)
- INDEX `member_id` (`member_id`)
- INDEX `email_address` (`email_address`)

**Business Rules**:
- checkin_time is set when member checks in
- kit_release_time is set when kit is released to member
- Status progresses: pending → checked_in → kit_released
- QR codes are used for quick identification during check-in process

---

### 3. `users` Table
**Purpose**: Stores system user accounts and authentication data

| Column | Type | Null | Key | Default | Extra | Description |
|--------|------|------|-----|---------|-------|-------------|
| `id` | int(11) | NO | PRI | NULL | auto_increment | Primary key, unique identifier |
| `username` | varchar(50) | NO | UNI | NULL | | Unique username for login |
| `password_hash` | varchar(255) | NO | | NULL | | Hashed password |
| `email` | varchar(255) | YES | | NULL | | User's email address |
| `role` | enum('admin','user') | YES | | 'user' | | User role/permission level |
| `access_token` | varchar(500) | YES | | NULL | | Session/API access token |
| `created_at` | timestamp | YES | | CURRENT_TIMESTAMP | | Account creation timestamp |
| `last_login` | timestamp | YES | | NULL | | Last successful login timestamp |

**Indexes**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `username` (`username`)

**Business Rules**:
- Username must be unique
- Passwords are stored as hashed values (never plain text)
- Access tokens are used for API authentication and session management
- Admin role has full system access, user role has limited access

---

## API INTEGRATION

### External PSME API
**Endpoint**: https://psme-api.smartpay.ph/public/index.php/api/postSearch  
**Method**: POST  
**Authentication**: Bearer token (stored in users.access_token)  
**Purpose**: Look up member information for food registration mapping

**Parameters**:
- `search`: Email address to search for
- `event_id`: Event identifier (currently using 65)

**Response Structure**:
```json
{
    "resultKey": 1,
    "resultValue": [
        {
            "firstname": "John",
            "lastname": "Doe", 
            "email": "john.doe@example.com"
        }
    ],
    "errorCode": 0
}
```

---

## ENDPOINTS REFERENCE

### Food Registration Management
- **GET** `fetchFoodData` - DataTables server-side processing
- **POST** `fetchAllFoodData` - Export all food data
- **GET** `foodData` - Dashboard statistics (total/unmapped counts)
- **POST** `addFoodRegistration` - Add new food registration
- **GET** `getFoodRegistration?id=X` - Get single registration for editing
- **POST** `updateFoodRegistration` - Update existing registration
- **POST** `deleteFoodRegistration` - Delete registration
- **GET** `getUnmappedFoodData` - Get records without mapping
- **POST** `updateFoodMapping` - Update mapping field for registration

### Check-in Management
- **GET** `data` - Dashboard statistics (checkins/kit releases)
- **POST** `fetchCheckinsData` - DataTables processing for checkins
- **POST** `insertCheckinData` - Add new check-in record
- **POST** `deleteCheckin` - Remove check-in record

### Authentication & Session
- **POST** `loginScript` - User authentication
- **GET** `renewToken` - Refresh API access token
- **GET** `logout` - End user session

---

## DASHBOARD METRICS

### Food Registration Dashboard
1. **Total Food Registration**: `SELECT COUNT(*) FROM food`
2. **Total Unmapped**: `SELECT COUNT(*) FROM food WHERE mapping IS NULL OR mapping = ''`

### Check-in Dashboard  
1. **Total Checked In**: `SELECT COUNT(*) FROM checkins WHERE checkin_time IS NOT NULL`
2. **Total Released Kits**: `SELECT COUNT(*) FROM checkins WHERE kit_release_time IS NOT NULL`

---

## FILE STRUCTURE

### Core Files
- `dbConnection.php` - Database connection configuration
- `functions.php` - Common utility functions
- `sidenav.php` - Navigation sidebar component
- `topbar.php` - Header/topbar component

### Food Registration System
- `list_food.php` - Main food registration interface
- `addFoodRegistration.php` - Add new registration endpoint
- `updateFoodRegistration.php` - Update registration endpoint
- `deleteFoodRegistration.php` - Delete registration endpoint
- `getFoodRegistration.php` - Get single registration endpoint
- `fetchFoodData.php` - DataTables data endpoint
- `fetchAllFoodData.php` - Export data endpoint
- `foodData.php` - Dashboard statistics endpoint
- `getUnmappedFoodData.php` - Unmapped records endpoint
- `updateFoodMapping.php` - Mapping update endpoint

### Check-in System
- `checkin.php` - Main check-in interface
- `list_checkins.php` - Check-in records management
- `insertCheckinData.php` - Add check-in endpoint
- `deleteCheckin.php` - Delete check-in endpoint
- `fetchCheckinsData.php` - DataTables data endpoint
- `data.php` - Dashboard statistics endpoint

### Authentication
- `login.php` - Login interface
- `loginScript.php` - Authentication processing
- `logout.php` - Logout processing
- `renewToken.php` - Token renewal endpoint

---

## BUSINESS LOGIC

### Food Registration Workflow
1. **Registration Entry**: Add new food registrations via web interface
2. **Mapping Process**: Correlate local registrations with external PSME API data
3. **Bulk Operations**: Map multiple registrations automatically using API lookup
4. **Data Management**: Edit/delete registrations as needed
5. **Reporting**: Export data for external processing

### Check-in Workflow
1. **Member Arrival**: Scan QR code or manual entry for check-in
2. **Status Update**: Update member status to "checked_in"
3. **Kit Release**: Mark kit as released when distributed
4. **Status Tracking**: Monitor check-in and kit release statistics

### Authentication Flow
1. **Login**: Username/password authentication
2. **Session Management**: Access token-based sessions
3. **Token Renewal**: Automatic token refresh for external API access
4. **Permission Control**: Role-based access (admin/user)

---

## TECHNICAL NOTES

### Server Compatibility
- **Production**: Apache with mod_rewrite (.htaccess routing)
- **Development**: Python HTTP server (limited PHP GET parameter support)
- **Database**: MySQL/MariaDB with utf8mb4 charset
- **PHP Version**: Compatible with PHP 7.4+

### Security Features
- **Authentication**: Session-based with access tokens
- **SQL Injection Protection**: Prepared statements throughout
- **Input Validation**: Server-side validation for all inputs
- **CSRF Protection**: Session validation on all POST operations
- **Email Uniqueness**: Enforced at database and application level

### Performance Considerations
- **DataTables**: Server-side processing for large datasets
- **Indexing**: Proper indexes on frequently queried columns
- **Caching**: Disabled for AJAX requests to ensure fresh data
- **Pagination**: 25 records per page default with customizable options
- **API Rate Limiting**: 200ms delay between external API calls

---

*This reference document should be updated whenever database schema changes are made.*