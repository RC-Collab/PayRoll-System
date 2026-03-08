# Notification System - Quick Start Guide

## 🎯 What's New?

The notification bell (🔔) in the top navbar is now **FULLY FUNCTIONAL**:
- Shows unread notification count with badge
- Displays unread notifications when clicked
- Updates automatically every 30 seconds
- Admin/HR can create and send notifications

## 📋 Quick Commands

### View All Notifications
```bash
# Open in browser
http://payroll.local/notifications
```

### Create Notification (Admin/HR Only)
```bash
# Web form
http://payroll.local/notifications/create

# Or via API
curl -X POST http://payroll.local/notifications \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: token" \
  -d '{
    "user_id": 5,
    "title": "Hello",
    "message": "This is a test",
    "type": "general"
  }'
```

## 🔧 Setup Instructions

### 1. Migration Already Run ✅
```bash
# Notifications table created
php artisan migrate
```

### 2. Test Notifications
```bash
# Add sample data
php artisan db:seed --class=NotificationSeeder
```

### 3. Verify Installation
```bash
php artisan tinker
> \App\Models\Notification::count()  # Should show notifications
> User::first()->notifications()->unread()->count()  # Check unread
```

## 📚 Files Created/Modified

| File | Purpose |
|------|---------|
| `app/Models/Notification.php` | Data model |
| `app/Http/Controllers/NotificationController.php` | Logic |
| `resources/views/notifications/modal.blade.php` | Bell modal |
| `resources/views/notifications/create.blade.php` | Create form |
| `routes/web.php` | API routes |
| `resources/views/layouts/app.blade.php` | Navigation bar |
| `app/Helpers/NotificationHelper.php` | Helper functions |

## 🎬 How to Use

### For Employees
1. **Click the bell icon** (🔔) in top right
2. **View notifications** in modal
3. **Delete** or **mark as read** using buttons

### For Admin/HR
1. Click bell → **Create Notification** (if you see the button)
2. Or go to `/notifications/create`
3. **Select users** (hold Ctrl/Cmd for multiple)
4. **Enter title and message**
5. **Choose type**: general, leave, salary, attendance, employee, department
6. **Click Send**

## 💻 For Developers

### Use Helper Functions

```php
// In any controller
use App\Helpers\NotificationHelper;

// Single notification
NotificationHelper::notify($userId, 'Title', 'Message', 'general');

// Multiple users
NotificationHelper::notifyBatch($userIds, 'Title', 'Message', 'salary');

// All HR users
NotificationHelper::notifyHR('Title', 'Message');

// All admins
NotificationHelper::notifyAdmins('Title', 'Message');

// Specific scenarios
NotificationHelper::notifySalaryProcessed($employeeId, $month, $year);
NotificationHelper::notifyLeaveApproved($employeeId, $leaveId, $type);
NotificationHelper::notifyLeaveRequest($empId, $empName, $leaveId, $type);
```

### API Endpoints

```bash
# Get notifications
GET /notifications
GET /notifications/unread
GET /notifications/unread-count

# Mark as read
POST /notifications/{id}/read

# Mark as unread
POST /notifications/{id}/unread

# Mark all as read
POST /notifications/mark-all-read

# Delete
DELETE /notifications/{id}
DELETE /notifications  # Delete all

# Create (Admin/HR only)
GET /notifications/create
POST /notifications
POST /notifications/batch
```

## 🔐 Permissions

| Action | Admin | HR | Accountant | Employee |
|--------|-------|----|----|----------|
| View own | ✅ | ✅ | ✅ | ✅ |
| Create | ✅ | ✅ | ❌ | ❌ |
| Delete | ✅ | ✅ | ✅ | ✅ |
| Mark read | ✅ | ✅ | ✅ | ✅ |

## 🎨 Notification Types

```
'general'     - General announcements
'leave'       - Leave requests & approvals
'salary'      - Salary processing & slips
'attendance'  - Attendance records
'employee'    - Employee updates
'department'  - Department announcements
```

## 📊 Database

```sql
SELECT COUNT(*) FROM notifications;                    -- Total
SELECT COUNT(*) FROM notifications WHERE is_read=0;   -- Unread
SELECT * FROM notifications ORDER BY created_at DESC; -- Recent
```

## 🐛 Troubleshooting

### Bell not showing badge
```bash
# Check if notifications exist
php artisan tinker
> \App\Models\Notification::count()

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Cannot create notifications
- Check your role: Must be 'admin' or 'hr'
- Try: `php artisan tinker` → `User::find(1)->role`

### Notifications not updating
- Default refresh is 30 seconds
- Hard refresh browser (Ctrl+Shift+R)
- Check browser console for errors

## 📞 Support

**Documentation Files:**
- `NOTIFICATION_SYSTEM.md` - Complete guide
- `NOTIFICATION_IMPLEMENTATION.md` - What was built
- `app/Helpers/NotificationHelper.php` - Usage examples

**Database Commands:**
```bash
# Check table
php artisan migrate:status

# Reset
php artisan migrate:rollback

# Fresh
php artisan migrate:fresh
```

## ✅ Status

✅ **READY TO USE**
- All features working
- Fully integrated
- Production ready
- Documentation complete

---

**Start using notifications now by clicking the bell icon! 🔔**
