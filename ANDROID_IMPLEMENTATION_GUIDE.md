# Android App Implementation Guide - Complete Setup

## ✅ Laravel Backend - COMPLETED

The following Laravel backend tasks have been completed:
- ✅ Migration created and run: Added `location` column to `experiences` table
- ✅ NotificationController created with all required endpoints
- ✅ Notification routes added to `/api/notifications`

### API Endpoints Available

#### Notification Endpoints (All require `auth:sanctum`)
```
GET    /api/notifications              - Get all notifications (paginated)
GET    /api/notifications/unread       - Get unread notifications only
GET    /api/notifications/unread-count - Get count of unread notifications
POST   /api/notifications/{id}/read    - Mark single notification as read
POST   /api/notifications/mark-all-read - Mark all notifications as read
DELETE /api/notifications/{id}         - Delete a notification
```

---

## 📱 Android App - Implementation Tasks

### Step 1: Copy XML Layout Files

Create these files in your Android project's `res/layout/` directory:

#### `res/layout/item_empty_state.xml`
```xml
<?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="wrap_content"
    android:orientation="vertical"
    android:gravity="center"
    android:padding="32dp">

    <ImageView
        android:layout_width="64dp"
        android:layout_height="64dp"
        android:src="@drawable/ic_empty"
        android:tint="#CCCCCC"/>

    <TextView
        android:id="@+id/tvEmptyMessage"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="No data available"
        android:textSize="14sp"
        android:textColor="#999999"
        android:layout_marginTop="16dp"/>

</LinearLayout>
```

### Step 2: Create Vector Drawable

Create this file in your Android project's `res/drawable/` directory:

#### `res/drawable/ic_empty.xml`
```xml
<?xml version="1.0" encoding="utf-8"?>
<vector xmlns:android="http://schemas.android.com/apk/res/android"
    android:width="24dp"
    android:height="24dp"
    android:viewportWidth="24"
    android:viewportHeight="24">
    <path
        android:fillColor="#FF000000"
        android:pathData="M12,2C6.48,2 2,6.48 2,12s4.48,10 10,10 10,-4.48 10,-10S17.52,2 12,2zM8,12c-1.1,0 -2,-0.9 -2,-2s0.9,-2 2,-2 2,0.9 2,2 -0.9,2 -2,2zM16,12c-1.1,0 -2,-0.9 -2,-2s0.9,-2 2,-2 2,0.9 2,2 -0.9,2 -2,2z"/>
</vector>
```

### Step 3: Update QualificationsFragment

Replace your `QualificationsFragment.java` with:

```java
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import androidx.fragment.app.Fragment;
import com.example.payroll.models.EmployeeProfileResponse;
import com.example.payroll.R;

public class QualificationsFragment extends Fragment {
    
    private LinearLayout layoutQualifications;
    private EmployeeProfileResponse profileData;
    
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_qualifications, container, false);
        layoutQualifications = view.findViewById(R.id.layoutQualifications);
        return view;
    }
    
    public void displayQualifications() {
        layoutQualifications.removeAllViews();
        
        if (profileData != null && profileData.getQualifications() != null 
            && !profileData.getQualifications().isEmpty()) {
            
            // Show qualifications
            for (EmployeeProfileResponse.Qualification qual : profileData.getQualifications()) {
                View qualView = LayoutInflater.from(requireContext())
                    .inflate(R.layout.item_qualification, layoutQualifications, false);
                
                // Bind data to views
                TextView tvDegree = qualView.findViewById(R.id.tvDegree);
                TextView tvInstitution = qualView.findViewById(R.id.tvInstitution);
                TextView tvYear = qualView.findViewById(R.id.tvYear);
                
                tvDegree.setText(qual.getDegree());
                tvInstitution.setText(qual.getInstitution());
                tvYear.setText(String.valueOf(qual.getYear()));
                
                layoutQualifications.addView(qualView);
            }
        } else {
            // Show empty state
            View emptyView = LayoutInflater.from(requireContext())
                .inflate(R.layout.item_empty_state, layoutQualifications, false);
            TextView tvEmpty = emptyView.findViewById(R.id.tvEmptyMessage);
            tvEmpty.setText("No qualifications added yet");
            layoutQualifications.addView(emptyView);
        }
    }
    
    public void setProfileData(EmployeeProfileResponse data) {
        this.profileData = data;
        displayQualifications();
    }
}
```

### Step 4: Update ExperiencesFragment

Replace your `ExperiencesFragment.java` with:

```java
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import androidx.fragment.app.Fragment;
import com.example.payroll.models.EmployeeProfileResponse;
import com.example.payroll.R;

public class ExperiencesFragment extends Fragment {
    
    private LinearLayout layoutExperiences;
    private EmployeeProfileResponse profileData;
    
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_experiences, container, false);
        layoutExperiences = view.findViewById(R.id.layoutExperiences);
        return view;
    }
    
    public void displayExperiences() {
        layoutExperiences.removeAllViews();
        
        if (profileData != null && profileData.getExperiences() != null 
            && !profileData.getExperiences().isEmpty()) {
            
            // Show experiences
            for (EmployeeProfileResponse.Experience exp : profileData.getExperiences()) {
                View expView = LayoutInflater.from(requireContext())
                    .inflate(R.layout.item_experience, layoutExperiences, false);
                
                // Bind data to views
                TextView tvCompany = expView.findViewById(R.id.tvCompany);
                TextView tvPosition = expView.findViewById(R.id.tvPosition);
                TextView tvLocation = expView.findViewById(R.id.tvLocation);
                TextView tvDates = expView.findViewById(R.id.tvDates);
                
                tvCompany.setText(exp.getCompany());
                tvPosition.setText(exp.getPosition());
                tvLocation.setText(exp.getLocation());
                tvDates.setText(exp.getStartDate() + " to " + exp.getEndDate());
                
                layoutExperiences.addView(expView);
            }
        } else {
            // Show empty state
            View emptyView = LayoutInflater.from(requireContext())
                .inflate(R.layout.item_empty_state, layoutExperiences, false);
            TextView tvEmpty = emptyView.findViewById(R.id.tvEmptyMessage);
            tvEmpty.setText("No experiences added yet");
            layoutExperiences.addView(emptyView);
        }
    }
    
    public void setProfileData(EmployeeProfileResponse data) {
        this.profileData = data;
        displayExperiences();
    }
}
```

### Step 5: Update EmergencyContactsFragment

```java
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import androidx.fragment.app.Fragment;
import com.example.payroll.models.EmployeeProfileResponse;
import com.example.payroll.R;

public class EmergencyContactsFragment extends Fragment {
    
    private LinearLayout layoutContacts;
    private EmployeeProfileResponse profileData;
    
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_emergency_contacts, container, false);
        layoutContacts = view.findViewById(R.id.layoutContacts);
        return view;
    }
    
    public void displayContacts() {
        layoutContacts.removeAllViews();
        
        if (profileData != null && profileData.getEmergencyContacts() != null 
            && !profileData.getEmergencyContacts().isEmpty()) {
            
            // Show contacts
            for (EmployeeProfileResponse.EmergencyContact contact : profileData.getEmergencyContacts()) {
                View contactView = LayoutInflater.from(requireContext())
                    .inflate(R.layout.item_emergency_contact, layoutContacts, false);
                
                // Bind data to views
                TextView tvName = contactView.findViewById(R.id.tvName);
                TextView tvRelationship = contactView.findViewById(R.id.tvRelationship);
                TextView tvPhone = contactView.findViewById(R.id.tvPhone);
                
                tvName.setText(contact.getName());
                tvRelationship.setText(contact.getRelationship());
                tvPhone.setText(contact.getPhone());
                
                layoutContacts.addView(contactView);
            }
        } else {
            // Show empty state
            View emptyView = LayoutInflater.from(requireContext())
                .inflate(R.layout.item_empty_state, layoutContacts, false);
            TextView tvEmpty = emptyView.findViewById(R.id.tvEmptyMessage);
            tvEmpty.setText("No emergency contacts added yet");
            layoutContacts.addView(emptyView);
        }
    }
    
    public void setProfileData(EmployeeProfileResponse data) {
        this.profileData = data;
        displayContacts();
    }
}
```

### Step 6: Fix TabLayout Warning

Update your `activity_employee_profile.xml`:

```xml
<com.google.android.material.tabs.TabLayout
    android:id="@+id/tabLayout"
    android:layout_width="match_parent"
    android:layout_height="wrap_content"
    android:background="@color/white"
    app:tabIndicatorColor="@color/primary"
    app:tabSelectedTextColor="@color/primary"
    app:tabTextColor="@color/text_secondary"
    app:tabMode="scrollable"
    app:tabGravity="center"/>
```

### Step 7: Create NotificationAPI Retrofit Service

Add to your Retrofit API service interface:

```java
public interface PayrollApiService {
    // ... existing endpoints ...
    
    // =================== NOTIFICATION APIs ===================
    @GET("notifications")
    Call<NotificationResponse> getNotifications();
    
    @GET("notifications/unread")
    Call<NotificationResponse> getUnreadNotifications();
    
    @GET("notifications/unread-count")
    Call<UnreadCountResponse> getUnreadCount();
    
    @POST("notifications/{id}/read")
    Call<ApiResponse> markNotificationAsRead(@Path("id") int id);
    
    @POST("notifications/mark-all-read")
    Call<ApiResponse> markAllNotificationsAsRead();
    
    @DELETE("notifications/{id}")
    Call<ApiResponse> deleteNotification(@Path("id") int id);
}
```

### Step 8: Create Notification Response Model

```java
public class NotificationResponse {
    private List<Notification> data;
    private String message;
    
    public static class Notification {
        private int id;
        private String title;
        private String message;
        private String type;
        private boolean is_read;
        private String created_at;
        
        // Getters and setters
        public int getId() { return id; }
        public String getTitle() { return title; }
        public String getMessage() { return message; }
        public String getType() { return type; }
        public boolean isRead() { return is_read; }
        public String getCreatedAt() { return created_at; }
    }
    
    public List<Notification> getData() { return data; }
    public String getMessage() { return message; }
}
```

```java
public class UnreadCountResponse {
    private int unread_count;
    private String message;
    
    public int getUnreadCount() { return unread_count; }
    public String getMessage() { return message; }
}
```

---

## 🧪 Testing the API

Use Postman to test the notification endpoints:

1. **Get All Notifications:**
   ```
   GET http://localhost:8000/api/notifications
   Headers: Authorization: Bearer <token>
   ```

2. **Get Unread Count:**
   ```
   GET http://localhost:8000/api/notifications/unread-count
   Headers: Authorization: Bearer <token>
   ```

3. **Mark as Read:**
   ```
   POST http://localhost:8000/api/notifications/1/read
   Headers: Authorization: Bearer <token>
   ```

4. **Mark All as Read:**
   ```
   POST http://localhost:8000/api/notifications/mark-all-read
   Headers: Authorization: Bearer <token>
   ```

---

## ✅ Summary of Changes

### Backend (Laravel)
- ✅ Added location column to experiences table
- ✅ Created NotificationController with full CRUD operations
- ✅ Added notification routes to API
- ✅ No breaking changes to existing APIs

### Frontend (Android)
- 📋 XML layout files for empty states
- 📋 Vector drawable for empty state icon
- 📋 Updated fragments to handle empty data gracefully
- 📋 Notification service integration
- 📋 Fixed TabLayout configuration

---

## 🐛 Known Issues Fixed

1. ✅ **Missing location column** → Added with migration
2. ✅ **404 on notification endpoints** → Routes added
3. ✅ **Empty data not handled** → Empty state views added
4. ✅ **TabLayout warning** → Configuration updated

---

## 📞 Next Steps

1. Copy the XML files to your Android project
2. Update your fragments with empty state handling
3. Test all API endpoints with Postman
4. Implement the NotificationService in your Android app
5. Deploy and test on actual device

---

**Created:** 2026-03-02
**Status:** Ready for Implementation
