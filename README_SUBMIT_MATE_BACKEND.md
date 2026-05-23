# Submit Mate BD Backend — Ready Folder

This is a cleaned Laravel REST API backend for Submit Mate BD.

## Easiest Start

1. Start **XAMPP MySQL**.
2. Extract this backend folder to:

```text
F:\submitmatebd\backend
```

3. Double click:

```text
SETUP_AND_START_BACKEND.bat
```

The script will try to create the database, install Composer dependencies if `vendor` is missing, run migrations, seed demo data, create the storage link, and start Laravel.

## Demo Login

Admin:

```text
email: admin@submitmatebd.test
password: 12345678
```

Student:

```text
email: student@submitmatebd.test
password: 12345678
```

## Main API URLs

Base URL:

```text
http://127.0.0.1:8000/api
```

Health:

```text
GET /health
```

Public:

```text
POST /register
POST /login
GET /services
GET /packages
GET /testimonials
```

Student/Admin authenticated:

```text
GET /profile
POST /orders
GET /my-orders
GET /orders/{id}
POST /orders/{orderId}/upload-file
GET /orders/{orderId}/files
GET /order-files/{fileId}/download
POST /orders/{orderId}/payments
GET /orders/{orderId}/payments
GET /orders/{orderId}/messages
POST /orders/{orderId}/messages
```

Admin:

```text
GET /admin/orders
PATCH /admin/orders/{id}/status
POST /admin/orders/{orderId}/upload-final-file
PATCH /admin/payments/{paymentId}/verify
GET/POST/PUT/DELETE /admin/services
GET/POST/PUT/DELETE /admin/packages
GET/POST/PUT/DELETE /admin/testimonials
```

## File Upload Note

Thunder Client free version may block file sending. Use **Postman** or `curl.exe` for file upload tests.

Postman setup:

```text
Body -> form-data
Key: file
Type: File
Value: choose your file
```

Header:

```text
Accept: application/json
Authorization: Bearer YOUR_LOGIN_TOKEN
```
