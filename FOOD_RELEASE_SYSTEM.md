# Food Release Tracking System

## Overview
A comprehensive food release management system for the 73rd National Convention, designed to track daily food distributions from October 16-18, 2025, and prevent duplicate releases to the same registrant.

## Database Design

### New Table: `food_releases`
```sql
CREATE TABLE food_releases (
    id INT(11) NOT NULL AUTO_INCREMENT,
    food_id INT(11) NOT NULL,
    release_date DATE NOT NULL,
    release_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    released_by INT(11) NOT NULL,
    notes TEXT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (food_id) REFERENCES food(id) ON DELETE CASCADE,
    FOREIGN KEY (released_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_daily_release (food_id, release_date)
);
```

### Database Views
1. **`food_releases_view`** - Comprehensive view joining releases with registrant and staff details
2. **`food_release_status`** - Pivoted view showing each registrant's status for all 3 days

## Key Features

### 1. Daily Release Tracking
- **Convention Dates**: October 16-18, 2025 (3 days)
- **One Food Per Day**: Unique constraint prevents duplicate releases
- **Release History**: Complete audit trail with timestamps and staff member tracking

### 2. Duplicate Prevention
- **Database Constraint**: `UNIQUE KEY unique_daily_release (food_id, release_date)`
- **Application Logic**: Backend validation before allowing releases
- **Real-time Checking**: Frontend shows release eligibility status

### 3. Enhanced Dashboard
- **Dynamic Metrics**: Shows different stats based on current date
- **Convention Period**: Displays today's releases and eligible registrants
- **Outside Convention**: Shows total releases and unmapped registrants

### 4. Updated DataTable
- **Release Summary**: Shows "X/3 days" with color-coded badges
- **Daily Status**: Individual day indicators (D1, D2, D3)
- **Smart Actions**: Release button only appears when eligible
- **Status Viewer**: Detailed release history modal

## Files Created/Modified

### New Files
1. **`create_food_releases_table.sql`** - Database schema creation script
2. **`releaseFoodToday.php`** - Release food for current date
3. **`getFoodReleaseStatus.php`** - Get release status for registrant
4. **`getFoodReleasesData.php`** - DataTables endpoint for release history
5. **`setup_food_release_system.php`** - Database setup helper script

### Modified Files
1. **`list_food.php`** - Enhanced with release functionality and status display
2. **`fetchFoodData.php`** - Updated to use `food_release_status` view
3. **`foodData.php`** - Enhanced dashboard with release metrics
4. **`database_schema.json`** - Complete schema documentation

## Setup Instructions

### 1. Database Setup
```bash
# Navigate to your project directory
cd c:\xampp\htdocs\checkin

# Run the setup script via web browser
http://localhost:8000/setup_food_release_system.php
```

### 2. Testing the System
1. **Start Python HTTP Server**:
   ```bash
   python -m http.server 8000
   ```

2. **Access the Food Registration Page**:
   ```
   http://localhost:8000/list_food.php
   ```

### 3. Verify Functionality
- ✅ Dashboard shows correct metrics based on current date
- ✅ DataTable displays release status columns
- ✅ Release buttons only appear during convention period
- ✅ Release status modal shows detailed information
- ✅ Duplicate release prevention works
- ✅ Release history tracking functions

## Business Rules

### Release Eligibility
- **Date Validation**: Only allow releases on Oct 16-18, 2025
- **One Per Day**: Each registrant can only receive one food per day
- **Real-time Updates**: Dashboard and table refresh after releases

### User Interface
- **Convention Period**: Shows "Released Today" and "Eligible for Release Today"
- **Outside Convention**: Shows "Total Unmapped" and "Total Food Releases"
- **Action Buttons**: Context-sensitive based on date and release status

### Data Integrity
- **Cascade Delete**: If food registration is deleted, all releases are deleted
- **Restrict Delete**: Cannot delete users who have released food
- **Audit Trail**: Complete tracking of who released food and when

## API Endpoints

### Release Management
- **`POST /releaseFoodToday`** - Release food for current date
- **`GET /getFoodReleaseStatus?id={food_id}`** - Get release status
- **`POST /getFoodReleasesData`** - DataTables for release history

### Enhanced Existing
- **`GET /foodData`** - Updated dashboard metrics
- **`POST /fetchFoodData`** - Enhanced with release status

## Dashboard Cards

### During Convention (Oct 16-18)
1. **Total Food Registration** (Primary)
2. **Released Today** (Success)
3. **Eligible for Release Today** (Info)

### Outside Convention
1. **Total Food Registration** (Primary)
2. **Total Unmapped** (Warning)
3. **Total Food Releases** (Success)

## Security Features
- **Session Validation**: All endpoints require valid login
- **User Tracking**: Records which staff member performed each release
- **Input Validation**: Sanitizes all inputs and validates data types
- **SQL Injection Protection**: Uses prepared statements throughout

## Future Enhancements
- **QR Code Scanning**: Integrate with existing QR code system
- **Mobile Interface**: Responsive design for tablet/phone use
- **Bulk Operations**: Mass release functionality for groups
- **Reports**: Advanced reporting and analytics
- **Notifications**: Email/SMS alerts for releases

## Troubleshooting

### Common Issues
1. **Database Connection**: Ensure MySQL is running and credentials are correct
2. **Table Creation**: Run `setup_food_release_system.php` if tables don't exist
3. **Date Issues**: Check server timezone settings for correct date validation
4. **Permission Errors**: Verify user has proper database privileges

### Debug Mode
Enable PHP error reporting in development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

This system provides a complete solution for tracking food releases during the national convention while maintaining data integrity and preventing duplicate distributions.