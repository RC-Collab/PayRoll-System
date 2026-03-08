# Employee Profile API Documentation

This document provides complete information about the Employee Profile API endpoints for managing employee self-service profile information.

## Base URL
```
https://your-domain.com/api/employee/profile
```

## Authentication
All endpoints require Bearer token authentication from Sanctum.

```
Authorization: Bearer {token}
```

## Response Format
All API responses follow a standardized format:

### Success Response
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error description",
  "error": "Exception message (on server error)",
  "errors": { ... } (validation errors)
}
```

---

## Endpoints

### 1. Get Complete Profile
Get all employee profile information including personal, address, qualifications, experiences, emergency contacts, employment details, and salary information.

**Endpoint:** `GET /api/employee/profile`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response Example:**
```json
{
  "status": "success",
  "message": "Profile retrieved successfully",
  "data": {
    "personal": {
      "id": 1,
      "employee_code": "EMP001",
      "first_name": "Roshan",
      "middle_name": "Kumar",
      "last_name": "Chaudhary",
      "full_name": "Roshan Kumar Chaudhary",
      "email": "roshan@company.com",
      "mobile_number": "9841234567",
      "alternative_number": "9840000000",
      "date_of_birth": "1995-05-15",
      "gender": "male",
      "marital_status": "single",
      "blood_group": "O+",
      "nationality": "Nepali",
      "religion": "Hindu",
      "profile_image": "https://url/to/image.jpg"
    },
    "address": {
      "present_address": "Kathmandu",
      "permanent_address": "Bhaktapur",
      "city": "Kathmandu",
      "state": "Bagmati",
      "country": "Nepal",
      "postal_code": "44600"
    },
    "emergency_contacts": [
      {
        "id": 1,
        "name": "John Doe",
        "relationship": "Brother",
        "phone": "9841234567",
        "phone2": "9840000000",
        "email": "john@example.com",
        "address": "123 Street, Kathmandu",
        "is_primary": true
      }
    ],
    "qualifications": [
      {
        "id": 1,
        "degree": "Bachelor",
        "institution": "Tribhuvan University",
        "board": "TU",
        "year": 2020,
        "percentage": 75.5,
        "grade": "A",
        "specialization": "Computer Science",
        "start_date": "2016-01-01",
        "end_date": "2020-12-31",
        "is_pursuing": false,
        "certificate_url": "https://url/to/certificate.pdf"
      }
    ],
    "experiences": [
      {
        "id": 1,
        "company": "Tech Company",
        "position": "Senior Developer",
        "location": "Kathmandu",
        "start_date": "2021-01-01",
        "end_date": null,
        "is_current": true,
        "description": "Working as Senior Developer",
        "achievements": "Led 5 projects successfully",
        "certificate_url": "https://url/to/certificate.pdf"
      }
    ],
    "documents": [
      {
        "id": 1,
        "type": "citizenship",
        "document_number": "123456789",
        "issue_date": "2010-05-15",
        "expiry_date": "2025-05-15",
        "issue_place": "Kathmandu",
        "file_url": "https://url/to/document.pdf",
        "is_verified": true
      }
    ],
    "employment": {
      "designation": "Senior Developer",
      "department": [
        {
          "id": 1,
          "name": "IT Department",
          "role": "Team Lead"
        }
      ],
      "employee_type": "permanent",
      "employment_status": "active",
      "joining_date": "2021-01-01",
      "confirmation_date": "2021-06-15",
      "probation_end_date": "2021-06-15",
      "contract_end_date": null,
      "work_shift": "Morning",
      "reporting_to": {
        "id": 5,
        "name": "Manager Name",
        "designation": "Manager"
      }
    },
    "salary": {
      "basic_salary": 50000,
      "bank_name": "Bank Name",
      "account_number": "****1234",
      "account_holder": "Roshan Chaudhary",
      "ifsc_code": "BANK0001",
      "pan_number": "PAN123456789",
      "uan_number": "UAN123456789",
      "esi_number": "ESI123456789"
    }
  }
}
```

---

### 2. Update Personal Information
Update personal details including name, contact, gender, blood group, religion, and nationality.

**Endpoint:** `PUT /api/employee/profile/personal`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "first_name": "Roshan",
  "middle_name": "Kumar",
  "last_name": "Chaudhary",
  "mobile_number": "9841234567",
  "email": "roshan@example.com",
  "gender": "male",
  "marital_status": "single",
  "blood_group": "O+",
  "nationality": "Nepali",
  "religion": "Hindu"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Personal information updated successfully",
  "data": { ... }
}
```

---

### 3. Update Address Information
Update address details including present address, permanent address, and location information.

**Endpoint:** `PUT /api/employee/profile/address`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "present_address": "Kathmandu, Nepal",
  "permanent_address": "Bhaktapur, Nepal",
  "city": "Kathmandu",
  "state": "Bagmati",
  "country": "Nepal",
  "postal_code": "44600"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Address updated successfully",
  "data": { ... }
}
```

---

### 4. Upload Profile Image
Upload a profile image for the employee.

**Endpoint:** `POST /api/employee/profile/profile-image`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
- `profile_image` (file): Image file (jpg, jpeg, png, max 5MB)

**Response:**
```json
{
  "status": "success",
  "message": "Profile image uploaded successfully",
  "data": {
    "profile_image": "https://url/to/uploaded/image.jpg"
  }
}
```

---

### 5. Create/Update Qualification
Add a new qualification or update an existing one.

**Endpoint:** `POST /api/employee/profile/qualifications`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
degree: Bachelor
institution: Tribhuvan University
board: TU
year: 2020
percentage: 75.5
grade: A
specialization: Computer Science
start_date: 2016-01-01
end_date: 2020-12-31
is_pursuing: false
certificate: (file) - optional
id: (optional) - for update

```

**Response:**
```json
{
  "status": "success",
  "message": "Qualification saved successfully",
  "data": {
    "id": 1,
    "degree": "Bachelor",
    "institution": "Tribhuvan University",
    ...
  }
}
```

---

### 6. Delete Qualification
Delete a qualification record.

**Endpoint:** `DELETE /api/employee/profile/qualifications/:id`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "message": "Qualification deleted successfully"
}
```

---

### 7. Get All Qualifications
Retrieve a list of all qualifications for the employee (ordered by year descending).

**Endpoint:** `GET /api/employee/profile/qualifications`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": "success",
  "message": "Qualifications retrieved successfully",
  "data": [
    {
      "id": 1,
      "degree": "Bachelor",
      "institution": "Tribhuvan University",
      ...
    }
  ]
}
```

---

### 8. Create/Update Work Experience
Add a new work experience or update an existing one.

**Endpoint:** `POST /api/employee/profile/experiences`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
company: Tech Company
position: Senior Developer
location: Kathmandu
start_date: 2021-01-01
end_date: (optional)
is_current: true
description: Professional description
achievements: Achievements and accomplishments
certificate: (file) - optional
id: (optional) - for update
```

**Response:**
```json
{
  "status": "success",
  "message": "Experience saved successfully",
  "data": {
    "id": 1,
    "company": "Tech Company",
    ...
  }
}
```

---

### 9. Delete Work Experience
Delete a work experience record.

**Endpoint:** `DELETE /api/employee/profile/experiences/:id`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "message": "Experience deleted successfully"
}
```

---

### 10. Get All Work Experiences
Retrieve a list of all work experiences (ordered by start_date descending).

**Endpoint:** `GET /api/employee/profile/experiences`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": "success",
  "message": "Experiences retrieved successfully",
  "data": [
    {
      "id": 1,
      "company": "Tech Company",
      ...
    }
  ]
}
```

---

### 11. Create/Update Emergency Contact
Add a new emergency contact or update an existing one.

**Endpoint:** `POST /api/employee/profile/emergency-contacts`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "John Doe",
  "relationship": "Brother",
  "phone": "9841234567",
  "phone2": "9840000000",
  "email": "john@example.com",
  "address": "123 Street",
  "is_primary": true,
  "id": null
}
```

**Note:** If `is_primary` is set to `true`, other contacts will automatically be marked as non-primary.

**Response:**
```json
{
  "status": "success",
  "message": "Emergency contact saved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    ...
  }
}
```

---

### 12. Delete Emergency Contact
Delete an emergency contact record.

**Endpoint:** `DELETE /api/employee/profile/emergency-contacts/:id`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "message": "Emergency contact deleted successfully"
}
```

---

### 13. Get All Emergency Contacts
Retrieve a list of all emergency contacts.

**Endpoint:** `GET /api/employee/profile/emergency-contacts`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": "success",
  "message": "Emergency contacts retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "relationship": "Brother",
      "is_primary": true,
      ...
    }
  ]
}
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 401 | Unauthorized (missing/invalid token) |
| 403 | Forbidden (unauthorized access) |
| 404 | Not Found (resource doesn't exist) |
| 422 | Validation Error |
| 500 | Server Error |

---

## Error Examples

### Validation Error
```json
{
  "status": "error",
  "errors": {
    "first_name": ["The first name field is required."],
    "email": ["The email must be a valid email address."]
  }
}
```

### Not Found
```json
{
  "status": "error",
  "message": "Qualification not found"
}
```

### Unauthorized Access
```json
{
  "status": "error",
  "message": "Unauthorized access to this resource"
}
```

---

## Testing the API

### Using Postman
1. Import the Postman collection provided
2. Set the Bearer token in the authorization header
3. Test each endpoint with sample data

### Using cURL
```bash
# Get complete profile
curl -X GET https://your-domain.com/api/employee/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Update personal info
curl -X PUT https://your-domain.com/api/employee/profile/personal \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Roshan",
    "last_name": "Chaudhary",
    "mobile_number": "9841234567"
  }'

# Create qualification
curl -X POST https://your-domain.com/api/employee/profile/qualifications \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "degree=Bachelor" \
  -F "institution=Tribhuvan University" \
  -F "year=2020" \
  -F "certificate=@/path/to/certificate.pdf"
```

---

## Implementation Notes

1. **File Upload**: Maximum file size is 5MB. Supported formats: PDF, JPG, JPEG, PNG
2. **Cascading Deletes**: When a qualification/experience is deleted, the associated certificate file is automatically removed from storage
3. **Primary Contact**: Only one emergency contact can be marked as primary. Setting a new contact as primary automatically marks others as non-primary
4. **Transaction Support**: Qualification and experience operations use database transactions to ensure data consistency
5. **Date Format**: All dates are returned and should be submitted in `YYYY-MM-DD` format
6. **Account Number**: Returned value shows only last 4 digits for security (masked as ****)
7. **Ownership Verification**: Users can only access and modify their own profile data
8. **Authorization**: All endpoints require authenticated user with valid Sanctum bearer token

---

## Database Schema

### Qualifications Table
```sql
CREATE TABLE qualifications (
  id BIGINT PRIMARY KEY,
  employee_id BIGINT,
  degree VARCHAR(255),
  institution VARCHAR(255),
  board VARCHAR(255),
  year INT,
  percentage FLOAT,
  grade VARCHAR(50),
  specialization VARCHAR(255),
  start_date DATE,
  end_date DATE,
  is_pursuing BOOLEAN,
  certificate_path VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);
```

### Experiences Table
```sql
CREATE TABLE experiences (
  id BIGINT PRIMARY KEY,
  employee_id BIGINT,
  company VARCHAR(255),
  position VARCHAR(255),
  location VARCHAR(255),
  start_date DATE,
  end_date DATE,
  is_current BOOLEAN,
  description TEXT,
  achievements TEXT,
  certificate_path VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);
```

### Emergency Contacts Table
```sql
CREATE TABLE emergency_contacts (
  id BIGINT PRIMARY KEY,
  employee_id BIGINT,
  name VARCHAR(255),
  relationship VARCHAR(255),
  phone VARCHAR(255),
  phone2 VARCHAR(255),
  email VARCHAR(255),
  address TEXT,
  is_primary BOOLEAN,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);
```

---

## Models and Relationships

### Employee Model
```php
public function qualifications() // One-to-Many
public function experiences() // One-to-Many
public function emergencyContacts() // One-to-Many
public function documents() // One-to-Many
public function manager() // Belongs-to (self-referential via reports_to)
public function subordinates() // One-to-Many (reverse of manager)
public function departments() // Many-to-Many
public function salaryStructure() // Has-One
```

### Relationships in Controller
The `getProfile()` endpoint eagerly loads all relationships to prevent N+1 query problems:
- Departments with role information
- Qualifications (ordered by year descending)
- Experiences (ordered by start_date descending)
- Emergency Contacts
- Documents (verified only)

---

## Validation Rules

### Personal Information
- `first_name`: Required, string, max 255
- `mobile_number`: Required, string, format: phone number
- `email`: Required, email format, unique
- `gender`: Enum: male, female, other
- `religion`: Optional, string

### Qualification
- `degree`: Required, string, max 255
- `institution`: Required, string, max 255
- `year`: Required, integer, 1900 to current year + 5
- `percentage`: Optional, numeric, 0-100
- `start_date`: Optional, date format
- `end_date`: Optional, date, must be after start_date
- `certificate`: Optional, file, PDF/JPG/PNG, max 5MB

### Experience
- `company`: Required, string, max 255
- `position`: Required, string, max 255
- `start_date`: Required, date format
- `end_date`: Optional, date, must be after start_date
- `is_current`: Boolean (if true, end_date is null)
- `certificate`: Optional, file, PDF/JPG/PNG, max 5MB

### Emergency Contact
- `name`: Required, string, max 255
- `relationship`: Required, string
- `phone`: Required, string
- `email`: Optional, email format
- `is_primary`: Optional, boolean

---

## Version Information
- API Version: 1.0
- Last Updated: March 2026
- Laravel Version: 10.x
- PHP Version: 8.5+

