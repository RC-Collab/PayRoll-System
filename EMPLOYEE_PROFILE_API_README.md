# Employee Profile Self-Service API

A comprehensive RESTful API for managing employee self-service profile information in a Laravel payroll application. This system enables employees to maintain their own profile data including personal information, qualifications, work experience, emergency contacts, and related documents.

## ✨ Features

### 🎯 Complete Profile Management
- **Personal Information**: Name, contact details, demographics, citizenship
- **Address Management**: Current and permanent address with location details  
- **Qualifications**: Educational background with certificates and verification
- **Work Experience**: Career history with achievements and certificates
- **Emergency Contacts**: Multiple contacts with primary designation
- **Documents**: ID documents, certificates with verification status
- **Employment Details**: Designation, department, joining dates, probation info
- **Salary Information**: Bank details, UAN, ESI, IFSC code (masked for security)

### 🔒 Security Features
- Bearer token authentication (Laravel Sanctum)
- Ownership verification on all operations
- Comprehensive input validation
- File type and size restrictions
- Account number masking
- SQL injection prevention
- CORS protection

### 🗂️ File Management
- Profile image upload
- Certificate uploads for qualifications/experiences
- Automatic file cleanup on delete/update
- Secure file storage
- Supported formats: PDF, JPG, JPEG, PNG (max 5MB)

### ⚡ Performance Optimizations
- Eager relationship loading (N+1 prevention)
- Transaction management for critical operations
- Efficient database queries
- Ordered result sets for better UX

### 🛡️ Error Handling
- Comprehensive try-catch blocks
- Detailed validation error messages
- Standardized error responses
- Exception logging with stack traces

---

## 📡 API Endpoints (13 Total)

### Profile Operations
```
GET    /api/employee/profile              - Get complete profile
PUT    /api/employee/profile/personal     - Update personal info
PUT    /api/employee/profile/address      - Update address
POST   /api/employee/profile/profile-image - Upload profile image
```

### Qualifications
```
GET    /api/employee/profile/qualifications      - List all
POST   /api/employee/profile/qualifications      - Create/update
DELETE /api/employee/profile/qualifications/:id  - Delete
```

### Work Experience
```
GET    /api/employee/profile/experiences      - List all
POST   /api/employee/profile/experiences      - Create/update
DELETE /api/employee/profile/experiences/:id  - Delete
```

### Emergency Contacts
```
GET    /api/employee/profile/emergency-contacts      - List all
POST   /api/employee/profile/emergency-contacts      - Create/update
DELETE /api/employee/profile/emergency-contacts/:id  - Delete
```

---

## 🚀 Quick Start

### Authentication
All endpoints require a Sanctum bearer token:
```bash
Authorization: Bearer YOUR_TOKEN_HERE
```

### Get Your Profile
```bash
curl -X GET "https://your-domain.com/api/employee/profile" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Update Personal Information
```bash
curl -X PUT "https://your-domain.com/api/employee/profile/personal" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "mobile_number": "9841234567",
    "religion": "Hindu"
  }'
```

### Add a Qualification
```bash
curl -X POST "https://your-domain.com/api/employee/profile/qualifications" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "degree=Bachelor" \
  -F "institution=Tribhuvan University" \
  -F "year=2020" \
  -F "percentage=75.5" \
  -F "certificate=@path/to/certificate.pdf"
```

---

## 📊 Response Format

### Success Response (200)
```json
{
  "status": "success",
  "message": "Operation description",
  "data": {
    "id": 1,
    "field": "value"
  }
}
```

### Validation Error (422)
```json
{
  "status": "error",
  "errors": {
    "first_name": ["The first name field is required."],
    "email": ["The email must be a valid email address."]
  }
}
```

### Server Error (500)
```json
{
  "status": "error",
  "message": "Failed to process request",
  "error": "Exception message"
}
```

---

## 🗄️ Database Schema

### Qualifications Table
```sql
id, employee_id, degree, institution, board, year, percentage, 
grade, specialization, start_date, end_date, is_pursuing, 
certificate_path, created_at, updated_at
```

### Experiences Table
```sql
id, employee_id, company, position, location, start_date, end_date,
is_current, description, achievements, certificate_path, 
created_at, updated_at
```

### Emergency Contacts Table
```sql
id, employee_id, name, relationship, phone, phone2, email, 
address, is_primary, created_at, updated_at
```

### Documents Table
```sql
id, employee_id, type, document_number, issue_date, expiry_date,
issue_place, file_path, is_verified, created_at, updated_at
```

---

## 📦 Models & Relationships

### Employee Model Relationships
```php
- hasMany('qualifications')        // Educational qualifications
- hasMany('experiences')           // Work experience records
- hasMany('emergencyContacts')     // Emergency contact information
- hasMany('documents')             // Uploaded documents
- belongsTo('manager', 'reports_to') // Reporting to (self-referential)
- hasMany('subordinates', 'reports_to') // Employees reporting to this user
- belongsToMany('departments')     // Department assignments
- hasOne('salaryStructure')        // Salary information
```

### New Columns Added to Employees Table
```
religion, reports_to, probation_end_date, work_shift, confirmation_date,
alternative_number, present_address, city, state, country, postal_code,
ifsc_code, uan_number, esi_number
```

---

## ✅ Validation Rules

### Personal Information
- `first_name`: Required, max 255 characters
- `mobile_number`: Required, valid phone format
- `email`: Required, valid email, unique
- `gender`: Required, enum (male/female/other)
- `religion`: Optional, string
- `blood_group`: Optional, enum (A+, A-, B+, B-, O+, O-, AB+, AB-)

### Address Information
- All fields optional
- String/text field types only

### Qualifications
- `degree`: Required, max 255 chars
- `institution`: Required, max 255 chars
- `year`: Required, between 1900 and current year + 5
- `percentage`: Optional, between 0-100
- `start_date`/`end_date`: Optional dates, end after start
- `certificate`: Optional file, PDF/JPG/PNG, max 5MB

### Work Experience
- `company`: Required, max 255 chars
- `position`: Required, max 255 chars
- `start_date`: Required, date format
- `end_date`: Optional, must be after start_date
- `is_current`: Boolean (if true, end_date ignored)

### Emergency Contacts
- `name`: Required, max 255 chars
- `relationship`: Required, string
- `phone`: Required, valid phone
- `email`: Optional, valid email format
- `is_primary`: Boolean (only one can be primary)

---

## 🧪 Testing

### Using Postman
1. Import the API collection from Postman_Collection.json
2. Set your bearer token in the Authorization tab
3. Test each endpoint with sample data

### Using Laravel Pest
```bash
php artisan test tests/Feature/EmployeeProfileApiTest.php
```

### Manual cURL Testing
See Quick Start section above for example commands.

---

## 📋 Complete Profile Response Structure

```json
{
  "personal": {
    "id": 1,
    "employee_code": "EMP001",
    "first_name": "John",
    "full_name": "John Doe",
    "email": "john@company.com",
    "mobile_number": "9841234567",
    "date_of_birth": "1995-05-15",
    "gender": "male",
    "marital_status": "single",
    "blood_group": "O+",
    "nationality": "Nepali",
    "religion": "Hindu",
    "profile_image": "https://url/to/image.jpg"
  },
  "address": {
    "present_address": "Kathmandu, Nepal",
    "permanent_address": "Bhaktapur, Nepal",
    "city": "Kathmandu",
    "state": "Bagmati",
    "country": "Nepal",
    "postal_code": "44600"
  },
  "emergency_contacts": [
    {
      "id": 1,
      "name": "Jane Doe",
      "relationship": "Spouse",
      "phone": "9841234567",
      "is_primary": true
    }
  ],
  "qualifications": [
    {
      "id": 1,
      "degree": "Bachelor",
      "institution": "TU",
      "year": 2020,
      "percentage": 75.5
    }
  ],
  "experiences": [
    {
      "id": 1,
      "company": "Tech Corp",
      "position": "Senior Developer",
      "start_date": "2021-01-01",
      "is_current": true
    }
  ],
  "documents": [],
  "employment": {
    "designation": "Senior Developer",
    "employee_type": "permanent",
    "joining_date": "2021-01-01",
    "reporting_to": {
      "id": 5,
      "name": "Manager Name",
      "designation": "Manager"
    }
  },
  "salary": {
    "basic_salary": 50000,
    "bank_name": "Bank Name",
    "account_holder": "John Doe",
    "uan_number": "UAN123456789"
  }
}
```

---

## 🔧 Installation & Setup

### Prerequisites
- Laravel 10.x
- PHP 8.5+
- MySQL/PostgreSQL
- Composer
- Laravel Sanctum

### Installation Steps

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Create Test Data (Optional)**
   ```bash
   php artisan db:seed
   ```

3. **Verify Routes**
   ```bash
   php artisan route:list | grep profile
   ```

4. **Test API**
   ```bash
   php artisan test tests/Feature/EmployeeProfileApiTest.php
   ```

---

## 📚 Documentation Files

- **EMPLOYEE_PROFILE_API.md** - Complete API documentation with examples
- **EMPLOYEE_PROFILE_API_IMPLEMENTATION.md** - Implementation details and summary
- **README.md** - This file, quick reference guide

---

## 🎯 Key Implementation Details

### Controllers (1 file)
- `EmployeeProfileController.php` (808 lines, 13 methods)
  - Comprehensive error handling with try-catch
  - Database transaction support
  - File upload/cleanup management
  - Ownership verification

### Models (5 files)
- `Qualification.php` - Educational qualifications
- `Experience.php` - Work experience
- `EmergencyContact.php` - Emergency contacts
- `Document.php` - Document management
- `Employee.php` - Updated with relationships

### Migrations (7 files)
- Create qualifications table
- Create experiences table
- Create emergency_contacts table
- Create documents table
- Add additional employee columns
- Add confirmation_date to employees
- Fixed payroll columns migration

### Routes (13 endpoints)
All protected by `auth:sanctum` middleware
- 4 profile endpoints
- 3 qualification endpoints
- 3 experience endpoints
- 3 emergency contact endpoints

### Tests (14 tests)
Comprehensive feature tests for all operations

---

## 🔐 Security Checklist

- ✅ Bearer token authentication required
- ✅ Ownership verification on mutations
- ✅ Input validation on all fields
- ✅ File type/size restrictions
- ✅ SQL injection prevention
- ✅ CORS protection
- ✅ Account number masking
- ✅ Exception message logging

---

## 📈 Performance Metrics

- **Eager Loading**: All relationships loaded in single query
- **N+1 Prevention**: Explicit relation loading in getProfile()
- **Transaction Support**: Critical operations wrapped in DB transactions
- **Query Optimization**: Ordered results for better UX

---

## 🐛 Troubleshooting

### Token Issues
- Ensure Sanctum is installed and configured
- Verify token is passed in Authorization header
- Check token hasn't expired

### File Upload Issues
- Verify file size is under 5MB
- Ensure file type is allowed (PDF/JPG/JPEG/PNG)
- Check storage disk has write permissions

### Relationship Issues
- Ensure all migrations have been run
- Verify foreign key constraints exist
- Check Employee model has relationship methods

### Validation Errors
- Check required fields are provided
- Verify field formats match validation rules
- Review detailed error messages in response

---

## 📞 Support

For issues or questions:
1. Check EMPLOYEE_PROFILE_API.md for detailed endpoint documentation
2. Review error messages in response body
3. Check Laravel logs in storage/logs/laravel.log
4. Verify database migrations have been applied

---

## 📝 License

This API is part of the payroll application system.

---

## 🎉 Summary

The Employee Profile API provides a robust, production-ready system for managing employee self-service profile data. With comprehensive error handling, security measures, file management, and detailed documentation, it's ready for immediate deployment and integration with Android and web applications.

**Status: ✅ Production Ready**
