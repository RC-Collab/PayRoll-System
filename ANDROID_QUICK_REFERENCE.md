# Android Implementation Quick Reference

## 1️⃣ Copy XML Files (2 minutes)

### Source Location
- Documents: `android_templates/item_empty_state.xml`
- Documents: `android_templates/ic_empty.xml`

### Destination in Android Project
```
app/src/main/res/layout/item_empty_state.xml
app/src/main/res/drawable/ic_empty.xml
```

---

## 2️⃣ Notification API Endpoints

```
// All require: Authorization: Bearer {token}

GET    /api/notifications              → Get all (paginated)
GET    /api/notifications/unread       → Get unread only
GET    /api/notifications/unread-count → Get count
POST   /api/notifications/{id}/read    → Mark read
POST   /api/notifications/mark-all-read → Mark all read
DELETE /api/notifications/{id}         → Delete one
```

---

## 3️⃣ Retrofit Service Interface

```java
public interface PayrollApiService {
    @GET("notifications")
    Call<NotificationListResponse> getNotifications();
    
    @GET("notifications/unread")
    Call<NotificationListResponse> getUnreadNotifications();
    
    @GET("notifications/unread-count")
    Call<UnreadCountResponse> getUnreadCount();
    
    @POST("notifications/{id}/read")
    Call<ApiResponse> markAsRead(@Path("id") int notificationId);
    
    @POST("notifications/mark-all-read")
    Call<ApiResponse> markAllAsRead();
    
    @DELETE("notifications/{id}")
    Call<ApiResponse> deleteNotification(@Path("id") int notificationId);
}
```

---

## 4️⃣ Response Models

### NotificationListResponse
```java
{
  "success": boolean,
  "data": {
    "current_page": int,
    "data": [Notification],
    "per_page": int,
    "total": int
  },
  "message": String
}
```

### Notification Object
```java
{
  "id": int,
  "user_id": int,
  "title": String,
  "message": String,
  "type": String,
  "is_read": boolean,
  "read_at": String|null,
  "created_at": String
}
```

### UnreadCountResponse
```java
{
  "success": boolean,
  "unread_count": int,
  "message": String
}
```

---

## 5️⃣ Fragment Implementation Pattern

```java
public void displayData() {
    if (profileData != null && profileData.getData() != null 
        && !profileData.getData().isEmpty()) {
        
        // Show data
        for (Item item : profileData.getData()) {
            // Inflate and bind
        }
    } else {
        // Show empty state
        View empty = LayoutInflater.from(requireContext())
            .inflate(R.layout.item_empty_state, container, false);
        TextView tv = empty.findViewById(R.id.tvEmptyMessage);
        tv.setText("No items added yet");
        container.addView(empty);
    }
}
```

---

## 6️⃣ Updated Database Column

### Experience Table
```sql
ALTER TABLE experiences ADD location VARCHAR(255) NULLABLE AFTER position;
```

**Example Data:**
```json
{
  "company": "Pushpanjali School",
  "position": "Teacher",
  "location": "Attariya",
  "start_date": "2020-01-01",
  "end_date": "2023-12-31"
}
```

---

## ✅ Verification Checklist

- [ ] XML files copied to res/layout/ and res/drawable/
- [ ] Notification retrofit service created
- [ ] Response models implemented
- [ ] Fragment updated with empty state handling
- [ ] Empty state shows correct message
- [ ] All 6 notification endpoints tested
- [ ] Experience creation includes location
- [ ] APK built and tested on device

---

## 🧪 Quick Test Commands

```bash
# Test notifications
curl "http://localhost:8000/api/notifications" \
  -H "Authorization: Bearer TOKEN"

# Test unread count
curl "http://localhost:8000/api/notifications/unread-count" \
  -H "Authorization: Bearer TOKEN"

# Mark notification as read
curl -X POST "http://localhost:8000/api/notifications/1/read" \
  -H "Authorization: Bearer TOKEN"
```

---

## 📊 HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad request |
| 401 | Unauthorized |
| 404 | Not found |
| 500 | Server error |

---

## 💡 Pro Tips

1. **Empty State Message** - Make it context-specific:
   - Qualifications: "No qualifications added yet"
   - Experiences: "No work experience added"
   - Contacts: "No emergency contacts added"

2. **Pagination** - Default is 20 items per page

3. **Icons** - Use ic_empty.xml for professional look

4. **Error Handling** - Always implement try-catch for API calls

5. **Testing** - Test with real backend, not mock data

---

## 📂 File References

| File | Purpose |
|------|---------|
| `ANDROID_IMPLEMENTATION_GUIDE.md` | Full documentation |
| `IMPLEMENTATION_SUMMARY.md` | Complete status report |
| `API_IMPLEMENTATION_COMPLETION.md` | API details |
| `item_empty_state.xml` | Empty state layout |
| `ic_empty.xml` | Empty state icon |

---

**Android Team:** Use this as your quick reference while implementing!  
**Complete Guide:** See `ANDROID_IMPLEMENTATION_GUIDE.md`  
**Estimated Time:** 2-3 hours for full integration  
