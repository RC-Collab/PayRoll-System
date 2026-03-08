# Notification System Documentation

## Overview
The notification system allows HR and Admin users to send notifications to employees and other users in the payroll system. The notification bell in the top navbar now displays unread notifications that users can view and manage.

## Features

### 1. Notification Bell (Top Navbar)
- **Location**: Top right corner, left of user name label
- **Functionality**: 
  - Click to open notification modal
  - Shows badge with unread count
  - Real-time updates every 30 seconds
  - Displays unread notifications only

### 2. Notification Types
The system supports the following notification types:
- `general` - General announcements and information
- `leave` - Leave-related notifications
- `salary` - Salary slip and payment notifications
- `attendance` - Attendance records and reports
- `employee` - Employee-related updates
- `department` - Department announcements

### 3. User Roles
Only the following roles can **create** notifications:
- **Admin** - Full access to create notifications for all users
- **HR** - Can create notifications for employees

All authenticated users can:
- View their notifications
- Mark notifications as read/unread
- Delete notifications

## How to Create Notifications

### Method 1: Via Web Interface (Admin/HR Only)
1. Click the notification bell icon in the top navbar
2. Look for "Create Notification" link (if you're Admin/HR)
3. Navigate to: `/notifications/create`
4. Fill in the form:
   - **Send to User(s)**: Select one or multiple users (hold Ctrl/Cmd)
   - **Title**: Short notification title
   - **Message**: Detailed notification message
   - **Type**: Select notification category
   - **Related Model** (Optional): Model class name (e.g., "Leave", "Salary")
   - **Related ID** (Optional): ID of the related record

### Method 2: Via API (Admin/HR Only)

**Create single notification:**
```bash
POST /notifications
Content-Type: application/json
X-CSRF-TOKEN: your-token

{
  "user_id": 5,
  "title": "Salary Processed",
  "message": "Your salary has been processed successfully",
  "type": "salary",
  "related_model": "MonthlySalary",
  "related_id": 123
}
```

**Create batch notifications:**
```bash
POST /notifications/batch
Content-Type: application/json
X-CSRF-TOKEN: your-token

{
  "user_ids": [1, 2, 3, 4, 5],
  "title": "New Company Policy",
  "message": "Please review the updated attendance policy",
  "type": "general"
}
```

## API Endpoints

All endpoints require authentication (`middleware: auth`).

### View Notifications
- `GET /notifications` - Get all notifications (paginated)
- `GET /notifications/unread` - Get unread notifications
- `GET /notifications/unread-count` - Get unread count (for badge)

### Manage Notifications
- `POST /notifications/{id}/read` - Mark single notification as read
- `POST /notifications/{id}/unread` - Mark single notification as unread
- `POST /notifications/mark-all-read` - Mark all notifications as read
- `DELETE /notifications/{id}` - Delete single notification
- `DELETE /notifications` - Delete all notifications

### Create Notifications (Admin/HR Only)
- `GET /notifications/create` - Show create form
- `POST /notifications` - Create single notification
- `POST /notifications/batch` - Create batch notifications

## Database Schema

### notifications table
```sql
CREATE TABLE notifications (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(50) DEFAULT 'general',
  related_model VARCHAR(100),
  related_id BIGINT,
  created_by BIGINT,
  is_read BOOLEAN DEFAULT FALSE,
  read_at TIMESTAMP,
  deleted_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX (user_id, is_read),
  INDEX (created_at),
  INDEX (type)
)
```

## Model & Controller

### Notification Model
**Location**: `app/Models/Notification.php`

**Key Methods:**
- `markAsRead()` - Mark notification as read
- `markAsUnread()` - Mark notification as unread
- `user()` - Get the user this notification belongs to
- `createdBy()` - Get the user who created the notification

**Scopes:**
- `unread()` - Get only unread notifications
- `read()` - Get only read notifications
- `recent()` - Order by created_at descending

### Notification Controller
**Location**: `app/Http/Controllers/NotificationController.php`

**Key Methods:**
- `index()` - List all notifications
- `unread()` - Get unread notifications
- `unreadCount()` - Get count of unread
- `markAsRead($id)` - Mark as read
- `markAsUnread($id)` - Mark as unread
- `markAllAsRead()` - Mark all as read
- `destroy($id)` - Delete notification
- `deleteAll()` - Delete all notifications
- `store(Request $request)` - Create single notification
- `storeBatch(Request $request)` - Create multiple notifications
- `createForm()` - Show create form

## Frontend Components

### Notification Modal
**Location**: `resources/views/notifications/modal.blade.php`

Displays:
- List of unread notifications
- Buttons to mark as read and delete
- "Mark all as read" option
- "Clear all" option
- Auto-refresh every 30 seconds

### Create Notification Form
**Location**: `resources/views/notifications/create.blade.php`

Allows Admin/HR to:
- Select multiple users
- Enter title and message
- Choose notification type
- Optionally link to related record

## Usage Examples

### Example 1: Notify all employees about salary processing
```bash
curl -X POST http://payroll.local/notifications/batch \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: token" \
  -d '{
    "user_ids": [2, 3, 4, 5, 6],
    "title": "Salary Processed - January",
    "message": "Your salary for January 2026 has been processed. Salary slip is available in the Salary section.",
    "type": "salary"
  }'
```

### Example 2: Notify HR about leave request
Via code in controller:
```php
use App\Models\Notification;

Notification::create([
    'user_id' => $hrUser->id,
    'title' => 'New Leave Request',
    'message' => 'Employee ' . $employee->name . ' has requested leave from ' . $leave->start_date->format('M d'),
    'type' => 'leave',
    'related_model' => 'LeaveRecord',
    'related_id' => $leave->id,
    'created_by' => auth()->id(),
]);
```

## Seeding Sample Data

Run the notification seeder to add sample notifications:
```bash
php artisan db:seed --class=NotificationSeeder
```

This will create sample notifications for testing.

## Security

✅ **Implemented:**
- Authorization checks (Admin/HR only for creation)
- User-level access control (can only see own notifications)
- CSRF token protection on forms
- Input validation
- SQL injection prevention (Eloquent ORM)

## Frontend Auto-Refresh

The notification badge auto-refreshes every 30 seconds:
- Checks for unread count
- Updates badge in real-time
- No page reload required

## Troubleshooting

### Notification bell not showing badge
- Check if notifications table exists: `php artisan migrate:status`
- Clear cache: `php artisan cache:clear`
- Refresh page

### Cannot create notifications (permission denied)
- Check user role: `php artisan tinker` → `User::find(1)->role`
- Only 'admin' and 'hr' roles can create
- Update user role if needed

### Notifications not updating in real-time
- Default refresh is 30 seconds
- Clear browser cache
- Check browser console for errors

## Future Enhancements

1. **Email Notifications** - Send email when notification is created
2. **Push Notifications** - Browser/mobile push notifications
3. **Notification Preferences** - Let users choose which types they receive
4. **Notification History** - Archive old notifications
5. **Scheduled Notifications** - Send at specific times
6. **Read Receipts** - Track when users read notifications
7. **Notification Templates** - Pre-built notification formats

## Support

For issues:
1. Check `storage/logs/laravel.log`
2. Run: `php artisan migrate:status`
3. Test with: `php artisan tinker`
4. Verify: `\App\Models\Notification::count()`

---

**Status:** ✅ Production Ready
**Last Updated:** January 27, 2026
**Version:** 1.0
