# Quick API Testing Guide

## 1. Login Student

POST `http://127.0.0.1:8000/api/login`

```json
{
  "email": "student@submitmatebd.test",
  "password": "12345678"
}
```

Copy the returned `token`.

## 2. Create Order

POST `http://127.0.0.1:8000/api/orders`

Headers:

```text
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

Body:

```json
{
  "service_id": 1,
  "package_id": 1,
  "title": "Need help with research proposal structure",
  "instructions": "I need guidance for proposal outline, objectives, citation formatting, and presentation structure.",
  "deadline": "2026-05-25"
}
```

## 3. My Orders

GET `http://127.0.0.1:8000/api/my-orders`

## 4. Upload Student File with Postman

POST `http://127.0.0.1:8000/api/orders/1/upload-file`

Headers:

```text
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

Body:

```text
form-data -> key: file -> type: File -> choose file
```

## 5. Submit Manual Payment

POST `http://127.0.0.1:8000/api/orders/1/payments`

```json
{
  "method": "bkash",
  "amount": 500,
  "sender_number": "01700000000",
  "transaction_id": "TXN123456"
}
```

## 6. Admin Login

POST `http://127.0.0.1:8000/api/login`

```json
{
  "email": "admin@submitmatebd.test",
  "password": "12345678"
}
```

## 7. Admin Verify Payment

PATCH `http://127.0.0.1:8000/api/admin/payments/1/verify`

```json
{
  "status": "verified",
  "admin_note": "Payment checked and verified."
}
```
