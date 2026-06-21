# 🎫 EVENT TICKETING SYSTEM - DETAILED IMPLEMENTATION PLAN

## Executive Summary

**Project:** Event Ticketing Web Application with Microservices Architecture  
**Complexity:** Medium (includes payment integration, file upload, JWT auth)  
**Timeline Estimate:** 2-3 weeks for full implementation  
**Services:** 5 services (1 Frontend + 1 API Gateway + 3 Backend Microservices)

---

## Tech Stack Summary

| Component       | Technology            | Database | Port |
| --------------- | --------------------- | -------- | ---- |
| Frontend        | Laravel 11 + Blade    | -        | 3000 |
| API Gateway     | Express.js (latest)   | -        | 8000 |
| Auth Service    | Express.js + JWT      | MySQL    | 8001 |
| Event Service   | Express.js + Multer   | MySQL    | 8002 |
| Booking Service | Laravel 11 + Midtrans | MySQL    | 8003 |

**Versions:**

- Node.js: v20.x LTS (latest)
- PHP: v8.2+
- Laravel: v11.x
- MySQL: v8.0+

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT/BROWSER                       │
│                      (http://localhost:3000)                 │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      │ HTTP Requests
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                      API GATEWAY (Express)                   │
│                    http://localhost:8000                     │
│                                                              │
│  Responsibilities:                                           │
│  - Route requests to appropriate services                    │
│  - JWT validation (except auth endpoints)                    │
│  - Request forwarding                                        │
└──────┬──────────────────┬──────────────────┬────────────────┘
       │                  │                  │
       │                  │                  │
       ▼                  ▼                  ▼
┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│ Auth Service │   │Event Service │   │Booking Service│
│  (Express)   │   │  (Express)   │   │  (Laravel)   │
│   :8001      │   │   :8002      │   │   :8003      │
│              │   │              │   │              │
│ - Register   │   │ - Event CRUD │   │ - Create     │
│ - Login      │   │ - Upload img │   │   Booking    │
│ - Get Profile│   │ - List       │   │ - Midtrans   │
│              │   │ - Search     │   │ - My Tickets │
└──────┬───────┘   └──────┬───────┘   └──────┬───────┘
       │                  │                  │
       │                  │                  │ (Direct call)
       │                  │                  └─────────┐
       │                  │                            │
       ▼                  ▼                            ▼
┌──────────────┐   ┌──────────────┐          ┌──────────────┐
│   auth_db    │   │  event_db    │          │  booking_db  │
│   (MySQL)    │   │  (MySQL)     │          │  (MySQL)     │
└──────────────┘   └──────────────┘          └──────────────┘

Service-to-Service Communication:
- Booking Service → Event Service (check availability, get event details)
```

---

## Database Schemas (ERD)

### 1. auth_db (Auth Service)

```sql
-- users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_role ON users(role);
```

**Relations:** None (standalone)

---

### 2. event_db (Event Service)

```sql
-- categories table
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- events table
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  image_path VARCHAR(500),
  date DATETIME NOT NULL,
  location VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quota INT NOT NULL,
  available_tickets INT NOT NULL,
  created_by INT NOT NULL COMMENT 'user_id from auth service',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_category ON events(category_id);
CREATE INDEX idx_date ON events(date);
CREATE INDEX idx_created_by ON events(created_by);
```

**Relations:**

- events.category_id → categories.id
- events.created_by → users.id (from auth_db, reference only)

---

### 3. booking_db (Booking Service)

```sql
-- bookings table
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(50) NOT NULL UNIQUE,
  user_id INT NOT NULL COMMENT 'from auth service',
  event_id INT NOT NULL COMMENT 'from event service',
  event_title VARCHAR(255) NOT NULL COMMENT 'cached from event service',
  quantity INT NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'paid', 'cancelled', 'failed') DEFAULT 'pending',
  payment_url TEXT NULL,
  midtrans_order_id VARCHAR(255) NULL,
  midtrans_transaction_id VARCHAR(255) NULL,
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_user ON bookings(user_id);
CREATE INDEX idx_event ON bookings(event_id);
CREATE INDEX idx_status ON bookings(status);
CREATE INDEX idx_booking_code ON bookings(booking_code);
CREATE INDEX idx_midtrans_order ON bookings(midtrans_order_id);
```

**Relations:**

- bookings.user_id → users.id (from auth_db, reference only)
- bookings.event_id → events.id (from event_db, reference only)

**Note:** We cache `event_title` to avoid extra API calls for display purposes.

---

## API Contracts

### Gateway Routes

All client requests go through API Gateway first.

**Base URL:** `http://localhost:8000/api`

**Gateway Behavior:**

- Public routes (no JWT validation): `/auth/*`
- Protected routes (JWT required): All others
- Forwards requests to appropriate services
- Adds `X-User-Id` and `X-User-Role` headers to forwarded requests

---

### 1. Auth Service API

**Base URL (internal):** `http://localhost:8001/api/auth`

#### POST /register

**Description:** Register new user

**Request:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response Success (201):**

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

**Response Error (400):**

```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["Email already exists"]
  }
}
```

---

#### POST /login

**Description:** User login

**Request:**

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response Success (200):**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

**Response Error (401):**

```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

#### GET /profile

**Description:** Get current user profile  
**Auth:** Required

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2026-06-20T10:00:00Z"
  }
}
```

---

#### PUT /profile

**Description:** Update user profile  
**Auth:** Required

**Request:**

```json
{
  "name": "John Updated",
  "email": "johnupdated@example.com"
}
```

**Response Success (200):**

```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Updated",
    "email": "johnupdated@example.com",
    "role": "user"
  }
}
```

---

### 2. Event Service API

**Base URL (internal):** `http://localhost:8002/api/events`

#### GET /categories

**Description:** Get all categories

**Response Success (200):**

```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Music" },
    { "id": 2, "name": "Sport" },
    { "id": 3, "name": "Seminar" },
    { "id": 4, "name": "Festival" }
  ]
}
```

---

#### GET /

**Description:** Get all events with filters

**Query Parameters:**

- `page` (int, default: 1)
- `limit` (int, default: 10)
- `search` (string, optional) - search by title
- `category_id` (int, optional)
- `date_from` (date, optional) - format: YYYY-MM-DD
- `date_to` (date, optional)

**Example:** `GET /events?page=1&limit=10&search=concert&category_id=1`

**Response Success (200):**

```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": 1,
        "category_id": 1,
        "category_name": "Music",
        "title": "Rock Concert 2026",
        "description": "Amazing rock concert...",
        "image_url": "http://localhost:8002/uploads/events/1719345678-image.jpg",
        "date": "2026-07-15T19:00:00Z",
        "location": "Stadium Jakarta",
        "price": 500000,
        "quota": 1000,
        "available_tickets": 850,
        "created_at": "2026-06-20T10:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 50,
      "items_per_page": 10
    }
  }
}
```

---

#### GET /:id

**Description:** Get event by ID

**Response Success (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "category_id": 1,
    "category_name": "Music",
    "title": "Rock Concert 2026",
    "description": "Amazing rock concert...",
    "image_url": "http://localhost:8002/uploads/events/1719345678-image.jpg",
    "date": "2026-07-15T19:00:00Z",
    "location": "Stadium Jakarta",
    "price": 500000,
    "quota": 1000,
    "available_tickets": 850,
    "created_by": 1,
    "created_at": "2026-06-20T10:00:00Z"
  }
}
```

**Response Error (404):**

```json
{
  "success": false,
  "message": "Event not found"
}
```

---

#### POST /

**Description:** Create new event (Admin only)  
**Auth:** Required (admin role)

**Content-Type:** `multipart/form-data`

**Request (FormData):**

```
category_id: 1
title: "Rock Concert 2026"
description: "Amazing rock concert..."
image: [File] (jpg/png/jpeg, max 5MB)
date: "2026-07-15T19:00:00Z"
location: "Stadium Jakarta"
price: 500000
quota: 1000
```

**Response Success (201):**

```json
{
  "success": true,
  "message": "Event created successfully",
  "data": {
    "id": 1,
    "category_id": 1,
    "title": "Rock Concert 2026",
    "image_url": "http://localhost:8002/uploads/events/1719345678-image.jpg",
    "date": "2026-07-15T19:00:00Z",
    "location": "Stadium Jakarta",
    "price": 500000,
    "quota": 1000,
    "available_tickets": 1000
  }
}
```

---

#### PUT /:id

**Description:** Update event (Admin only)  
**Auth:** Required (admin role)

**Content-Type:** `multipart/form-data`

**Request (FormData):**

```
category_id: 1
title: "Updated Title"
description: "Updated description"
image: [File] (optional)
date: "2026-07-15T19:00:00Z"
location: "Stadium Jakarta"
price: 550000
quota: 1200
```

**Response Success (200):**

```json
{
  "success": true,
  "message": "Event updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Title",
    "price": 550000
  }
}
```

---

#### DELETE /:id

**Description:** Delete event (Admin only)  
**Auth:** Required (admin role)

**Response Success (200):**

```json
{
  "success": true,
  "message": "Event deleted successfully"
}
```

---

#### POST /check-availability (Internal API)

**Description:** Check if tickets are available (called by Booking Service)

**Request:**

```json
{
  "event_id": 1,
  "quantity": 5
}
```

**Response Success (200):**

```json
{
  "success": true,
  "data": {
    "available": true,
    "event": {
      "id": 1,
      "title": "Rock Concert 2026",
      "price": 500000,
      "available_tickets": 850
    }
  }
}
```

**Response Error (400):**

```json
{
  "success": false,
  "message": "Not enough tickets available",
  "data": {
    "available": false,
    "requested": 1000,
    "available_tickets": 850
  }
}
```

---

#### POST /reduce-quota (Internal API)

**Description:** Reduce available tickets after successful booking

**Request:**

```json
{
  "event_id": 1,
  "quantity": 5
}
```

**Response Success (200):**

```json
{
  "success": true,
  "message": "Quota reduced successfully",
  "data": {
    "available_tickets": 845
  }
}
```

---

### 3. Booking Service API

**Base URL (internal):** `http://localhost:8003/api/bookings`

#### POST /

**Description:** Create new booking  
**Auth:** Required

**Request:**

```json
{
  "event_id": 1,
  "quantity": 2
}
```

**Flow:**

1. Validate user authentication
2. Call Event Service to check availability
3. Calculate total price
4. Create booking with status 'pending'
5. Call Midtrans to create payment
6. Return booking with payment URL

**Response Success (201):**

```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 1,
    "booking_code": "BKG-1719345678-ABC123",
    "user_id": 1,
    "event_id": 1,
    "event_title": "Rock Concert 2026",
    "quantity": 2,
    "total_price": 1000000,
    "status": "pending",
    "payment_url": "https://app.sandbox.midtrans.com/snap/v2/...",
    "created_at": "2026-06-20T10:00:00Z"
  }
}
```

**Response Error (400):**

```json
{
  "success": false,
  "message": "Not enough tickets available"
}
```

---

#### GET /my-bookings

**Description:** Get current user's bookings  
**Auth:** Required

**Query Parameters:**

- `page` (int, default: 1)
- `limit` (int, default: 10)
- `status` (string, optional) - pending/paid/cancelled/failed

**Response Success (200):**

```json
{
  "success": true,
  "data": {
    "bookings": [
      {
        "id": 1,
        "booking_code": "BKG-1719345678-ABC123",
        "event_id": 1,
        "event_title": "Rock Concert 2026",
        "quantity": 2,
        "total_price": 1000000,
        "status": "paid",
        "paid_at": "2026-06-20T10:15:00Z",
        "created_at": "2026-06-20T10:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 2,
      "total_items": 15,
      "items_per_page": 10
    }
  }
}
```

---

#### GET /:id

**Description:** Get booking detail  
**Auth:** Required (own booking or admin)

**Response Success (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "booking_code": "BKG-1719345678-ABC123",
    "user_id": 1,
    "event_id": 1,
    "event_title": "Rock Concert 2026",
    "quantity": 2,
    "total_price": 1000000,
    "status": "paid",
    "payment_url": "https://app.sandbox.midtrans.com/snap/v2/...",
    "midtrans_order_id": "ORDER-1719345678",
    "midtrans_transaction_id": "TXN-1719345678",
    "paid_at": "2026-06-20T10:15:00Z",
    "created_at": "2026-06-20T10:00:00Z"
  }
}
```

---

#### POST /midtrans-notification

**Description:** Webhook for Midtrans payment notification  
**Auth:** None (verified by signature)

**Request (from Midtrans):**

```json
{
  "transaction_status": "settlement",
  "order_id": "ORDER-1719345678",
  "gross_amount": "1000000.00",
  "signature_key": "..."
}
```

**Flow:**

1. Verify Midtrans signature
2. Find booking by order_id
3. Update booking status based on transaction_status
4. If success, reduce event quota via Event Service
5. Return response

**Response Success (200):**

```json
{
  "success": true,
  "message": "Notification processed"
}
```

---

#### POST /:id/cancel

**Description:** Cancel booking  
**Auth:** Required (own booking or admin)

**Response Success (200):**

```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled"
  }
}
```

---

#### GET /admin/all

**Description:** Get all bookings for admin  
**Auth:** Required (admin role)

**Query Parameters:**

- `page`, `limit`, `status` (same as my-bookings)
- `user_id` (int, optional) - filter by user

**Response:** Same structure as my-bookings

---

## File Structure (Monorepo)

```
event-ticketing-microservices/
|
+-- frontend-web/                    # Laravel Frontend
|   +-- app/
|   |   +-- Http/
|   |   |   +-- Controllers/
|   |   |   |   +-- AuthController.php
|   |   |   |   +-- EventController.php
|   |   |   |   +-- BookingController.php
|   |   |   |   +-- DashboardController.php
|   |   |   +-- Middleware/
|   |   |       +-- AuthCheckMiddleware.php
|   |   +-- Services/
|   |       +-- ApiClient.php
|   |       +-- AuthServiceClient.php
|   |       +-- EventServiceClient.php
|   |       +-- BookingServiceClient.php
|   +-- resources/
|   |   +-- views/
|   |       +-- layouts/
|   |       |   +-- app.blade.php
|   |       |   +-- guest.blade.php
|   |       +-- auth/
|   |       |   +-- login.blade.php
|   |       |   +-- register.blade.php
|   |       +-- events/
|   |       |   +-- index.blade.php
|   |       |   +-- show.blade.php
|   |       |   +-- create.blade.php
|   |       |   +-- edit.blade.php
|   |       +-- bookings/
|   |       |   +-- index.blade.php
|   |       |   +-- show.blade.php
|   |       +-- dashboard/
|   |           +-- user.blade.php
|   |           +-- admin.blade.php
|   +-- routes/
|   |   +-- web.php
|   +-- public/
|   |   +-- css/
|   |   +-- js/
|   +-- .env.example
|   +-- composer.json
|   +-- README.md
|
+-- api-gateway/                     # Express.js API Gateway
|   +-- src/
|   |   +-- index.js
|   |   +-- config/
|   |   |   +-- services.js
|   |   +-- routes/
|   |   |   +-- index.js
|   |   +-- middleware/
|   |   |   +-- jwtValidation.js
|   |   |   +-- errorHandler.js
|   |   |   +-- logger.js
|   |   +-- proxy/
|   |   |   +-- authProxy.js
|   |   |   +-- eventProxy.js
|   |   |   +-- bookingProxy.js
|   |   +-- utils/
|   |       +-- httpClient.js
|   |       +-- response.js
|   +-- .env.example
|   +-- package.json
|   +-- README.md
|
+-- auth-service/                    # Express.js Auth Service
|   +-- src/
|   |   +-- index.js
|   |   +-- config/
|   |   |   +-- database.js
|   |   +-- models/
|   |   |   +-- User.js
|   |   +-- routes/
|   |   |   +-- auth.js
|   |   +-- controllers/
|   |   |   +-- authController.js
|   |   +-- middleware/
|   |   |   +-- authMiddleware.js
|   |   |   +-- validation.js
|   |   |   +-- errorHandler.js
|   |   +-- utils/
|   |   |   +-- jwt.js
|   |   |   +-- bcrypt.js
|   |   |   +-- response.js
|   |   +-- seeders/
|   |       +-- userSeeder.js
|   +-- .env.example
|   +-- package.json
|   +-- README.md
|
+-- event-service/                   # Express.js Event Service
|   +-- src/
|   |   +-- index.js
|   |   +-- config/
|   |   |   +-- database.js
|   |   |   +-- multer.js
|   |   +-- models/
|   |   |   +-- Event.js
|   |   |   +-- Category.js
|   |   +-- routes/
|   |   |   +-- events.js
|   |   +-- controllers/
|   |   |   +-- eventController.js
|   |   +-- middleware/
|   |   |   +-- authMiddleware.js
|   |   |   +-- upload.js
|   |   |   +-- validation.js
|   |   |   +-- errorHandler.js
|   |   +-- utils/
|   |   |   +-- fileHandler.js
|   |   |   +-- response.js
|   |   +-- seeders/
|   |       +-- categorySeeder.js
|   |       +-- eventSeeder.js
|   +-- storage/
|   |   +-- uploads/
|   |       +-- events/
|   |           +-- .gitkeep
|   +-- .env.example
|   +-- package.json
|   +-- README.md
|
+-- booking-service/                 # Laravel Booking Service
|   +-- app/
|   |   +-- Http/
|   |   |   +-- Controllers/
|   |   |   |   +-- BookingController.php
|   |   |   +-- Middleware/
|   |   |       +-- AuthMiddleware.php
|   |   +-- Models/
|   |   |   +-- Booking.php
|   |   +-- Services/
|   |   |   +-- EventServiceClient.php
|   |   |   +-- MidtransService.php
|   |   +-- Helpers/
|   |       +-- BookingCodeGenerator.php
|   +-- database/
|   |   +-- migrations/
|   |   |   +-- 2026_06_20_000001_create_bookings_table.php
|   |   +-- seeders/
|   |       +-- BookingSeeder.php
|   +-- routes/
|   |   +-- api.php
|   +-- .env.example
|   +-- composer.json
|   +-- README.md
|
+-- docs/                            # Documentation
|   +-- architecture-diagram.png
|   +-- erd-auth.png
|   +-- erd-event.png
|   +-- erd-booking.png
|
+-- .gitignore
+-- README.md                        # Main project README
```

---

## Environment Configuration

### Frontend Web (.env.example)

```env
APP_NAME="Event Ticketing"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:3000

LOG_CHANNEL=stack

# API Gateway
API_GATEWAY_URL=http://localhost:8000/api

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

### API Gateway (.env.example)

```env
PORT=8000
NODE_ENV=development

# JWT Secret (must match Auth Service)
JWT_SECRET=your_super_secret_jwt_key_change_this_in_production

# Microservices URLs
AUTH_SERVICE_URL=http://localhost:8001
EVENT_SERVICE_URL=http://localhost:8002
BOOKING_SERVICE_URL=http://localhost:8003

# CORS
CORS_ORIGIN=http://localhost:3000
```

---

### Auth Service (.env.example)

```env
PORT=8001
NODE_ENV=development

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=auth_db
DB_USER=root
DB_PASSWORD=

# JWT
JWT_SECRET=your_super_secret_jwt_key_change_this_in_production
JWT_EXPIRES_IN=7d

# CORS
CORS_ORIGIN=http://localhost:8000
```

---

### Event Service (.env.example)

```env
PORT=8002
NODE_ENV=development

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=event_db
DB_USER=root
DB_PASSWORD=

# File Upload
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=image/jpeg,image/jpg,image/png
UPLOAD_PATH=./storage/uploads/events

# Base URL for file access
BASE_URL=http://localhost:8002

# CORS
CORS_ORIGIN=http://localhost:8000
```

---

### Booking Service (.env.example)

```env
APP_NAME=BookingService
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8003

LOG_CHANNEL=stack

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_db
DB_USERNAME=root
DB_PASSWORD=

# Event Service
EVENT_SERVICE_URL=http://localhost:8002

# Midtrans Configuration
MIDTRANS_SERVER_KEY=your_midtrans_server_key_here
MIDTRANS_CLIENT_KEY=your_midtrans_client_key_here
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/v1
MIDTRANS_API_URL=https://api.sandbox.midtrans.com/v2

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:8000
```

---

## Seed Data Details

### Auth Service Seeds

**File:** `auth-service/src/seeders/userSeeder.js`

**Admin User:**

- Email: `admin@ticketing.com`
- Password: `admin123`
- Name: `Admin User`
- Role: `admin`

**Regular Users (5 users):**

- Email: `user1@example.com` to `user5@example.com`
- Password: `password123`
- Names: `User One`, `User Two`, etc.
- Role: `user`

---

### Event Service Seeds

**File:** `event-service/src/seeders/categorySeeder.js`

**Categories (4 categories):**

1. Music
2. Sport
3. Seminar
4. Festival

**File:** `event-service/src/seeders/eventSeeder.js`

**Events (10 events):**

1. **Rock Concert 2026**
   - Category: Music
   - Date: 2026-07-15 19:00
   - Location: Stadium Jakarta
   - Price: 500000
   - Quota: 1000

2. **Jazz Night**
   - Category: Music
   - Date: 2026-07-20 20:00
   - Location: Blue Note Jazz Club
   - Price: 350000
   - Quota: 200

3. **Football Match - Final**
   - Category: Sport
   - Date: 2026-08-10 16:00
   - Location: Gelora Bung Karno
   - Price: 150000
   - Quota: 50000

4. **Basketball Tournament**
   - Category: Sport
   - Date: 2026-08-15 18:00
   - Location: Basket Hall Jakarta
   - Price: 100000
   - Quota: 5000

5. **Tech Conference 2026**
   - Category: Seminar
   - Date: 2026-09-05 09:00
   - Location: Convention Center
   - Price: 750000
   - Quota: 500

6. **Startup Pitch Day**
   - Category: Seminar
   - Date: 2026-09-12 10:00
   - Location: Co-working Space
   - Price: 250000
   - Quota: 150

7. **Summer Music Festival**
   - Category: Festival
   - Date: 2026-10-01 12:00
   - Location: Beach Area Ancol
   - Price: 600000
   - Quota: 3000

8. **Food Festival**
   - Category: Festival
   - Date: 2026-10-15 10:00
   - Location: City Park
   - Price: 50000
   - Quota: 10000

9. **Electronic Music Festival**
   - Category: Festival
   - Date: 2026-11-05 18:00
   - Location: Open Field Sentul
   - Price: 800000
   - Quota: 5000

10. **Marathon Event**
    - Category: Sport
    - Date: 2026-11-20 05:00
    - Location: Start at Monas
    - Price: 200000
    - Quota: 2000

**Note:** All events created_by admin user (id: 1), with placeholder image URLs or no images initially.

---

### Booking Service Seeds

**File:** `booking-service/database/seeders/BookingSeeder.php`

**Sample Bookings (5 bookings):**

1. User 1 books Event 1 (Rock Concert), 2 tickets, status: paid
2. User 2 books Event 3 (Football Match), 4 tickets, status: paid
3. User 3 books Event 5 (Tech Conference), 1 ticket, status: pending
4. User 4 books Event 7 (Summer Festival), 3 tickets, status: paid
5. User 5 books Event 2 (Jazz Night), 2 tickets, status: cancelled

**Note:** Bookings with status 'paid' should have reduced the event's available_tickets accordingly.

---

## Implementation Roadmap

### Phase 1: Setup & Infrastructure (Day 1-2)

#### Step 1.1: Project Initialization

1. Create project root directory: `event-ticketing-microservices/`
2. Initialize git repository: `git init`
3. Create `.gitignore` file (exclude node_modules, .env, vendor, storage)
4. Create main `README.md` with project overview

#### Step 1.2: Database Setup

1. Install MySQL 8.0
2. Create three databases:
   ```sql
   CREATE DATABASE auth_db;
   CREATE DATABASE event_db;
   CREATE DATABASE booking_db;
   ```
3. Verify connection for each database

#### Step 1.3: Setup Auth Service

1. Create `auth-service/` directory
2. Initialize Node.js project: `npm init -y`
3. Install dependencies:
   ```bash
   npm install express mysql2 bcryptjs jsonwebtoken dotenv cors express-validator
   npm install --save-dev nodemon
   ```
4. Create folder structure (src/, config/, models/, controllers/, routes/, middleware/, utils/, seeders/)
5. Copy `.env.example` and create `.env` with actual config
6. Create database connection in `src/config/database.js`
7. Create User model (`src/models/User.js`)
8. Implement auth controllers (register, login, profile)
9. Create JWT utility functions
10. Setup routes and middleware
11. Create user seeder script
12. Test: Run service on port 8001, test endpoints with Postman

#### Step 1.4: Setup Event Service

1. Create `event-service/` directory
2. Initialize Node.js project: `npm init -y`
3. Install dependencies:
   ```bash
   npm install express mysql2 multer dotenv cors express-validator
   npm install --save-dev nodemon
   ```
4. Create folder structure (including storage/uploads/events/)
5. Copy `.env.example` and create `.env`
6. Create database connection
7. Create Category and Event models
8. Implement event controllers (CRUD, upload, internal APIs)
9. Setup multer for file upload (5MB limit, jpg/png/jpeg only)
10. Create seeders for categories and events
11. Setup static file serving for `/uploads/*`
12. Test: Run service on port 8002, test all endpoints

#### Step 1.5: Setup Booking Service

1. Create `booking-service/` directory
2. Initialize Laravel project: `composer create-project laravel/laravel booking-service`
3. Install Midtrans package: `composer require midtrans/midtrans-php`
4. Install Guzzle (if not included): `composer require guzzlehttp/guzzle`
5. Configure `.env` with database and Midtrans credentials
6. Create Booking model and migration
7. Run migration: `php artisan migrate`
8. Create BookingController with all methods
9. Create MidtransService class
10. Create EventServiceClient for service-to-service communication
11. Setup API routes in `routes/api.php`
12. Create booking seeder
13. Test: Run service on port 8003 (`php artisan serve --port=8003`)

---

### Phase 2: API Gateway (Day 3)

#### Step 2.1: Setup API Gateway

1. Create `api-gateway/` directory
2. Initialize Node.js project: `npm init -y`
3. Install dependencies:
   ```bash
   npm install express axios jsonwebtoken dotenv cors express-rate-limit
   npm install --save-dev nodemon
   ```
4. Create folder structure (src/, config/, routes/, middleware/, proxy/)
5. Copy `.env.example` and create `.env`

#### Step 2.2: Implement Gateway Logic

1. Create JWT validation middleware (verify token, extract user info)
2. Create proxy functions for each service (auth, event, booking)
3. Setup routing:
   - `/api/auth/*` ? Auth Service (public)
   - `/api/events/*` ? Event Service (protected)
   - `/api/bookings/*` ? Booking Service (protected)
4. Implement request forwarding with headers (X-User-Id, X-User-Role)
5. Add error handling middleware
6. Add request logging
7. Test: Run on port 8000, verify token validation and forwarding

---

### Phase 3: Frontend Web (Day 4-5)

#### Step 3.1: Setup Laravel Frontend

1. Create `frontend-web/` directory
2. Initialize Laravel project: `composer create-project laravel/laravel frontend-web`
3. Install Guzzle: `composer require guzzlehttp/guzzle`
4. Configure `.env` (set APP_URL to localhost:3000, API_GATEWAY_URL)
5. Create folder structure in `app/Services/`

#### Step 3.2: Create Service Clients

1. Create `ApiClient.php` (base HTTP client with JWT handling)
2. Create `AuthServiceClient.php` (login, register, profile methods)
3. Create `EventServiceClient.php` (list, detail, create, update, delete)
4. Create `BookingServiceClient.php` (create, my-bookings, detail)

#### Step 3.3: Create Controllers

1. `AuthController.php`:
   - showLogin(), login(), showRegister(), register(), logout()
2. `EventController.php`:
   - index(), show(), create(), store(), edit(), update(), destroy()
3. `BookingController.php`:
   - index(), show(), store()
4. `DashboardController.php`:
   - userDashboard(), adminDashboard()

#### Step 3.4: Create Blade Views

1. Create layouts:
   - `layouts/app.blade.php` (authenticated layout with navbar)
   - `layouts/guest.blade.php` (guest layout for login/register)
2. Create auth views:
   - `auth/login.blade.php`
   - `auth/register.blade.php`
3. Create event views:
   - `events/index.blade.php` (list with search/filter)
   - `events/show.blade.php` (detail with booking form)
   - `events/create.blade.php` (admin only)
   - `events/edit.blade.php` (admin only)
4. Create booking views:
   - `bookings/index.blade.php` (my tickets)
   - `bookings/show.blade.php` (ticket detail)
5. Create dashboard views:
   - `dashboard/user.blade.php`
   - `dashboard/admin.blade.php`

#### Step 3.5: Setup Routes & Middleware

1. Define routes in `routes/web.php`
2. Create `AuthCheckMiddleware` to verify JWT from session/localStorage
3. Group routes by authentication requirement
4. Add admin-only route groups
5. Test: Run on port 3000 (`php artisan serve --port=3000`)

---

### Phase 4: Integration & Testing (Day 6-7)

#### Step 4.1: Run All Services

1. Start MySQL server
2. Run Auth Service: `cd auth-service && npm run dev`
3. Run Event Service: `cd event-service && npm run dev`
4. Run Booking Service: `cd booking-service && php artisan serve --port=8003`
5. Run API Gateway: `cd api-gateway && npm run dev`
6. Run Frontend: `cd frontend-web && php artisan serve --port=3000`

#### Step 4.2: Seed All Databases

1. Auth Service: `node src/seeders/userSeeder.js`
2. Event Service:
   - `node src/seeders/categorySeeder.js`
   - `node src/seeders/eventSeeder.js`
3. Booking Service: `php artisan db:seed --class=BookingSeeder`

#### Step 4.3: End-to-End Testing

1. **User Registration & Login:**
   - Register new user via frontend
   - Login and verify JWT token storage
   - Check profile page

2. **Browse Events:**
   - View event list
   - Use search and filters
   - View event details

3. **Create Booking:**
   - Select event and quantity
   - Create booking
   - Verify redirect to Midtrans payment page
   - Complete payment in sandbox mode
   - Verify booking status updates to 'paid'
   - Check available tickets decreased

4. **Admin Functions:**
   - Login as admin
   - Create new event with image upload
   - Verify image is accessible
   - Edit event
   - View all bookings

5. **Service-to-Service Communication:**
   - Monitor logs during booking creation
   - Verify Booking Service calls Event Service
   - Verify quota reduction after payment

#### Step 4.4: Midtrans Integration Testing

1. Get Midtrans Sandbox credentials from dashboard.midtrans.com
2. Configure in Booking Service `.env`
3. Setup webhook URL (use ngrok for local testing):
   - `ngrok http 8003`
   - Add webhook URL to Midtrans dashboard: `https://<ngrok-url>/api/bookings/midtrans-notification`
4. Test payment flow:
   - Use test card: 4811 1111 1111 1114
   - Complete payment
   - Verify webhook received
   - Verify booking status updated

---

### Phase 5: Documentation (Day 7-8)

#### Step 5.1: Create Technical Documentation

1. Create `docs/` folder
2. Generate architecture diagram (use draw.io or similar)
3. Create ERD for each database (use MySQL Workbench or dbdiagram.io)
4. Compile API contracts in `docs/api-contract.md`

#### Step 5.2: Create README Files

1. Main `README.md`:
   - Project description
   - Architecture overview
   - Setup instructions
   - How to run all services
   - API Gateway endpoints
   - Testing guide
   - Screenshots

2. Service-specific READMEs:
   - Each service folder should have installation & run instructions
   - Environment variables explanation
   - Seeding instructions

#### Step 5.3: Video Demonstration

1. Plan video structure (10-20 minutes)
2. Record sections:
   - Introduction & architecture explanation (2-3 min)
   - Demo user flow: register, browse, book, pay (3-4 min)
   - Demo admin flow: create event, view bookings (2-3 min)
   - Code walkthrough: routing, middleware, service communication (5-8 min)
   - Database show: 3 separate databases, ERDs (2-3 min)
   - API testing with Postman (2-3 min)
3. Edit and upload to YouTube (unlisted) or Google Drive
4. Add link to main README and technical report

---

## Testing Checklist

### Functional Testing

- [ ] **Auth Service**
  - [ ] Register new user (success & validation errors)
  - [ ] Login with correct credentials
  - [ ] Login with wrong credentials (should fail)
  - [ ] Access profile with valid JWT
  - [ ] Access profile with invalid JWT (should fail)
  - [ ] Update profile information

- [ ] **Event Service**
  - [ ] Get all categories
  - [ ] List events with pagination
  - [ ] Search events by title
  - [ ] Filter events by category and date
  - [ ] Get single event details
  - [ ] Create event as admin with image upload
  - [ ] Create event as regular user (should fail)
  - [ ] Update event as admin
  - [ ] Delete event as admin
  - [ ] Check availability (internal API)
  - [ ] Reduce quota (internal API)
  - [ ] Access uploaded image via URL

- [ ] **Booking Service**
  - [ ] Create booking with sufficient tickets
  - [ ] Create booking with insufficient tickets (should fail)
  - [ ] View my bookings with pagination
  - [ ] View booking detail (own booking)
  - [ ] View booking detail (other user's booking, should fail)
  - [ ] Cancel booking
  - [ ] Admin view all bookings
  - [ ] Midtrans notification processing (webhook)
  - [ ] Verify quota reduced after successful payment

- [ ] **API Gateway**
  - [ ] Public routes accessible without token
  - [ ] Protected routes require valid JWT
  - [ ] Invalid JWT rejected
  - [ ] Expired JWT rejected
  - [ ] Request forwarding to correct service
  - [ ] User ID and role passed in headers

- [ ] **Frontend**
  - [ ] User registration form
  - [ ] User login form
  - [ ] Logout functionality
  - [ ] Event list page with search/filter
  - [ ] Event detail page
  - [ ] Booking creation
  - [ ] My tickets page
  - [ ] Admin event creation (with image upload)
  - [ ] Admin event editing
  - [ ] Admin dashboard

### Non-Functional Testing

- [ ] **Security**
  - [ ] Passwords hashed in database
  - [ ] JWT token properly signed
  - [ ] CORS configured correctly
  - [ ] File upload validates file type and size
  - [ ] SQL injection prevention (parameterized queries)

- [ ] **Database Isolation**
  - [ ] Each service has separate database
  - [ ] No direct database access between services
  - [ ] All inter-service communication via API

- [ ] **Error Handling**
  - [ ] Services return proper error messages
  - [ ] HTTP status codes used correctly
  - [ ] Frontend displays user-friendly errors

---

## Final Deliverables Checklist

- [ ] **Source Code**
  - [ ] All 5 services implemented and working
  - [ ] Code pushed to repository (GitHub/GitLab)
  - [ ] `.env.example` files in all services
  - [ ] `.env` files NOT committed
  - [ ] README files in all services
  - [ ] Main README with complete setup guide

- [ ] **Documentation**
  - [ ] Architecture diagram (PNG/PDF)
  - [ ] ERD for auth_db (PNG/PDF)
  - [ ] ERD for event_db (PNG/PDF)
  - [ ] ERD for booking_db (PNG/PDF)
  - [ ] API contract document (Markdown/PDF)
  - [ ] Technical report covering all requirements

- [ ] **Video Demonstration**
  - [ ] 10-20 minutes duration
  - [ ] Face visible (webcam/camera)
  - [ ] Architecture explanation included
  - [ ] Live demo of all features
  - [ ] Code walkthrough included
  - [ ] Database shown (3 separate databases)
  - [ ] API testing with Postman/Insomnia
  - [ ] Uploaded to YouTube (unlisted) or Google Drive
  - [ ] Link included in documentation

- [ ] **Requirements Compliance**
  - [ ] Minimum 3 microservices ? (Auth, Event, Booking)
  - [ ] At least 1 Express.js service ? (Auth, Event, Gateway = 3)
  - [ ] At least 1 Laravel service ? (Booking, Frontend = 2)
  - [ ] Each service has own database ? (3 databases)
  - [ ] No direct database access between services ?
  - [ ] Communication via API ?
  - [ ] JWT authentication implemented ?
  - [ ] Architecture diagram created ?
  - [ ] ERD for each service ?
  - [ ] API contracts documented ?

---

## Conclusion

This detailed implementation plan provides a complete roadmap for building the Event Ticketing Microservices system. Follow the phases sequentially, test each service independently before integration, and maintain clear documentation throughout the development process.

**Estimated Total Time:** 7-8 days of focused development

**Key Success Factors:**

- Each service must be independently testable
- Maintain strict database isolation
- Use proper JWT authentication flow
- Test service-to-service communication thoroughly
- Document all APIs clearly
- Demonstrate end-to-end functionality in video

**Good luck with your Software Construction project!**

---

## Implementation Updates & Enhancements

The following features and improvements were added during actual implementation beyond the original specification:

### 1. Design System Overhaul

**Spotify-Inspired Dark Theme**

- Complete UI redesign based on Spotify's design language
- Color palette: Near-black backgrounds (#121212), Spotify Green (#1ed760)
- Typography: Pill-shaped buttons, uppercase labels, heavy shadows
- Sidebar layout: Fixed navigation sidebar + main content area (like Spotify web player)
- Status badges: Semantic colors (green=paid, yellow=pending, red=failed/cancelled)
- CSS files: public/css/app.css and
  esources/css/app.css (kept in sync)

**Design Documentation**

- Created DESIGN.md with complete design system specification

### 2. GIS / Map Integration

**Leaflet.js + OpenStreetMap**

- Event location map viewer on event detail pages
- Interactive map with markers and popups
- Geocoding via Nominatim API (free, no API key required)

**OSRM Routing**

- "Petunjuk Arah dari Lokasi Saya" button
- Calculates driving route from user's GPS location to event venue
- Displays route on map with distance (km) and estimated duration (minutes)
- Green route lines matching Spotify theme

**Map Picker (Admin)**

- Map-based location selector for event creation/editing
- Search address + click-to-select + draggable marker
- Stores location as text (address) in database

**Integration Points**

- events/show.blade.php: Map viewer + routing (height: 420px)
- ookings/show.blade.php: Map viewer + routing for ticket location
- events/create.blade.php & events/edit.blade.php: Map picker

### 3. Landing Page

**Public Landing Page**

- Route: GET / (no authentication required)
- Hero section with Spotify green gradient
- Event cards grid showing available events
- "Get Started" / "Login" CTA buttons
- Controller: LandingPageController
- Gateway: /api/events made public (skip JWT validation)

### 4. Admin Features & Data Scoping

**Admin Dashboard Enhancements**

- Real-time stats: Total Events, Total Bookings, Total Revenue, Total Users
- Chart.js bar chart: Bookings per day (last 14 days)
- Top Events panel: Most booked events with booking counts
- Recent Transactions table: Latest 10 bookings
- **Data scoping**: Admin only sees events they created (created_by filter)

**Admin Layout**

- Dedicated layouts/admin.blade.php with custom sidebar
- Admin badge in header
- Navigation: Dashboard, Manajemen Event, Manajemen Tiket, Manajemen Transaksi, Verifikasi Tiket

**Admin Event Management** (/admin/events)

- Card grid view of admin's own events
- Shows tickets sold count and total bookings per event
- CRUD buttons on each card
- Only events where created_by = admin_user_id

**Admin Ticket Management** (/admin/tickets)

- Total tickets sold summary
- Table showing tickets sold per event (sorted by count)
- Recent ticket sales history (last 20)
- Filtered by admin's events only

**Admin Transaction Management** (/admin/transactions)

- Paginated transaction list (20 per page)
- Filter by status and event
- Total transactions count and total revenue
- Transactions per event breakdown
- Only shows bookings for admin's events

**Data Scoping Implementation**

- Event Service: Added created_by query parameter support in Event.findAll()
- Booking Service: Added event_ids query parameter (comma-separated) in ll() endpoint
- Frontend: DashboardController and AdminController filter data by admin's user_id
- Flow: Fetch admin's events ? extract event IDs ? fetch bookings for those event IDs

### 5. QR Code Ticket System

**Ticket Design**

- Redesigned ookings/show.blade.php with realistic ticket appearance
- Branded header (green), event info, QR code, booking code, status
- Dashed separators for tear-off effect
- Footer shows creation date and usage status

**QR Code Generation**

- Client-side generation using qrcodejs library (CDN)
- QR encodes the ooking_code (e.g., BKG-1718000000-ABC123)
- White background with black QR, 140x140px

**Database Changes**

- Added used_at TIMESTAMP field to ookings table
- Migration: 2026_06_20_000002_add_used_at_to_bookings.php

**Verification System**

- New endpoint: POST /api/bookings/verify (admin only)
- Validates booking_code, checks status=paid, checks not yet used
- Sets used_at = now() on successful verification
- Frontend: AdminController@verifyPage() and AdminController@verifyTicket()

### 6. Admin Ticket Verification Page

**Verification Methods** (/admin/verify)

1. **Camera QR Scanner**
   - Auto-starts back camera
   - Uses html5-qrcode library for real-time scanning
   - Toggle camera on/off button
2. **Upload QR Image**
   - File input for uploading screenshot/photo of QR
   - Uses Html5Qrcode.scanFile() to decode static images
   - Supports jpg, png, etc.

3. **Manual Input**
   - Text input for typing booking code directly
   - Submit button

**Result Display**

- Success: Green card with checkmark, "Tiket Terverifikasi!" message
- Failure: Red card with error icon and specific error message
- Booking detail panel: Shows code, event, user, quantity, status, used_at timestamp

**JavaScript Implementation**

- Form ID: #verify-form (to avoid conflict with logout form in sidebar)
- Decoded QR code auto-fills input and submits form
- Handles camera permissions, errors, and file upload failures

### 7. Enhanced Payment Flow

**Payment Finish Page**

- Route: /bookings/payment-finish/{booking} (public)
- Displays success/failure modal based on Midtrans redirect
- JavaScript reads URL hash parameters (?transaction_status=...)
- Calls backend to update payment status if needed
- Shows booking details after processing

**Payment Callback**

- POST /bookings/{booking}/payment-callback endpoint
- Alternative to webhook for browser-based status updates
- Frontend: BookingController@updatePayment() proxies to booking service

### 8. Date Filtering Enhancement

**Flatpickr Integration**

- Date range picker on events index page
- Dark theme styling to match Spotify design
- Query params: date_from and date_to
- CDN: https://cdn.jsdelivr.net/npm/flatpickr

### 9. Admin Role-Based Redirect

**Login/Register Redirect Logic**

- AuthController@login() and
  egister() check user role
- Admin users: Redirect to /admin (dashboard)
- Regular users: Redirect to /events (browse events)
- Implemented in: rontend-web/app/Http/Controllers/AuthController.php

### 10. Chart Improvements

**Dashboard Chart**

- Changed from line chart to bar chart (clearer daily view)
- Fixed chart container height: position:relative; height:200px
- Chart.js configuration:
  esponsive:true, maintainAspectRatio:false
- Data: Last 14 days of booking activity

---

## Updated Database Schema

### bookings table (addition)

`sql
ALTER TABLE bookings ADD COLUMN used_at TIMESTAMP NULL AFTER paid_at;
`

---

## Updated API Contracts

### Event Service - Additional Query Parameters

**GET /events**

- Added: created_by (int, optional) - Filter events by creator user_id

### Booking Service - Additional Endpoints

**GET /bookings/admin/all** (updated)

- Added query params:
  - event_ids (string, optional) - Comma-separated event IDs
  - event_id (int, optional) - Single event ID filter

**POST /bookings/verify** (new)

- **Description:** Verify ticket and mark as used (admin only)
- **Auth:** Required (admin role)
- **Request:**
  `json
{
  "booking_code": "BKG-1718000000-ABC123"
}
`
- **Response Success (200):**
  `json
{
  "success": true,
  "message": "Ticket verified successfully",
  "data": {
    "id": 1,
    "booking_code": "BKG-1718000000-ABC123",
    "status": "paid",
    "used_at": "2026-06-21T09:00:00Z",
    ...
  }
}
`
- **Response Error (400):**
  `json
{
  "success": false,
  "message": "Ticket already used at 21 Jun 2026 09:00",
  "data": { ... }
}
`

---

## Updated File Structure

### Frontend New Files

`frontend-web/
+-- app/
|   +-- Http/
|   |   +-- Controllers/
|   |       +-- AdminController.php        # NEW: Admin management pages
|   |       +-- LandingPageController.php  # NEW: Public landing page
+-- resources/
    +-- views/
        +-- layouts/
        |   +-- admin.blade.php           # NEW: Admin layout with custom sidebar
        +-- landing.blade.php             # NEW: Public landing page
        +-- admin/                        # NEW: Admin views directory
            +-- events.blade.php          # NEW: Event management
            +-- tickets.blade.php         # NEW: Ticket management
            +-- transactions.blade.php    # NEW: Transaction management
            +-- verify.blade.php          # NEW: QR verification page`

### Booking Service New Files

`booking-service/
+-- database/
    +-- migrations/
        +-- 2026_06_20_000002_add_used_at_to_bookings.php  # NEW`

---

## Updated Dependencies

### Frontend (CDN)

`html

<!-- Leaflet.js (Map) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Chart.js (Dashboard charts) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- QRCode.js (Ticket QR generation) -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<!-- HTML5 QR Code (Admin QR scanner) -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<!-- Flatpickr (Date picker) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
`

### External APIs Used

- **Nominatim** (OpenStreetMap): Geocoding for address ? lat/lon
- **OSRM** (Open Source Routing Machine): Route calculation
- **Midtrans Sandbox**: Payment gateway

---

## Testing Additions

### Additional Features to Test

- [ ] **Landing Page**
  - [ ] Access without login
  - [ ] View event cards
  - [ ] Click "Get Started" redirects to register
- [ ] **GIS Features**
  - [ ] Event detail shows map with location marker
  - [ ] "Petunjuk Arah" button gets user location
  - [ ] Route displayed on map with distance/duration
  - [ ] Ticket detail shows event location map

- [ ] **Admin Dashboard**
  - [ ] Stats display correct totals
  - [ ] Chart shows bookings per day
  - [ ] Top events sorted by booking count
  - [ ] Recent transactions table displays

- [ ] **Admin Management Pages**
  - [ ] Event management shows only admin's events
  - [ ] Ticket sold counts are accurate
  - [ ] Transaction pagination works (20 per page)
  - [ ] Filters by status and event

- [ ] **QR Verification**
  - [ ] Camera scanner opens and scans QR
  - [ ] Upload image decodes QR correctly
  - [ ] Manual input verifies booking
  - [ ] Success: booking marked as used (used_at set)
  - [ ] Error: Already used ticket shows timestamp
  - [ ] Error: Pending/cancelled tickets rejected

- [ ] **Admin Data Scoping**
  - [ ] Admin A cannot see Admin B's events
  - [ ] Dashboard stats only count admin's events
  - [ ] Transactions filtered by admin's event ownership

---

## Performance Considerations

**Map Loading**

- Maps load asynchronously via geocoding API
- display:none until geocoding succeeds (prevents flash)
- map.invalidateSize() called after 200ms to fix rendering

**Chart Rendering**

- Chart container has fixed height to prevent growth
- IIFE wrapper prevents global variable pollution
- Chart instance destroyed before recreation (prevents memory leaks)

**QR Scanner**

- Camera can be toggled on/off to save battery
- Scanner stopped when QR detected (prevents duplicate submits)
- File upload scanner creates new instance (no state leak)

**Data Scoping**

- Admin endpoints fetch limited data (e.g., limit=100 for events)
- Bookings fetched with limit=1000 for aggregation (consider pagination for large datasets)
- Frontend aggregates data (counting, grouping) to reduce backend complexity

---

## Security Notes

**QR Verification**

- Admin role check enforced in BookingController@verify()
- Booking code must match exactly (case-sensitive)
- Status validation: Only paid tickets can be verified
- Idempotent: Re-verifying shows already-used timestamp

**Admin Data Isolation**

- Events filtered by created_by on backend
- Bookings filtered by event_ids derived from admin's events
- No way for Admin A to access Admin B's data via API

**Map/GIS**

- Geocoding uses public OpenStreetMap API (no auth required)
- OSRM routing uses public API (no auth required)
- Location data stored as plain text addresses (no sensitive coords)

**Payment Webhook**

- Midtrans signature verification via \Midtrans\Notification() class
- Fallback to manual signature check for simulator/mock transactions
- Webhook endpoint (/api/bookings/midtrans-notification) public but verified

---

## Known Limitations & Future Improvements

**Current Limitations**

1. Event ownership (created_by) is set once and never changed
2. Admin management pages use limit=100 for events (pagination not implemented)
3. Dashboard aggregations done in PHP (could be moved to database queries for performance)
4. Map geocoding uses free Nominatim API (rate-limited to 1 req/sec)
5. QR scanner requires HTTPS in production (browser security policy)

**Future Enhancements**

1. Real-time dashboard updates (WebSocket/Pusher)
2. Event attendance tracking (scan-in/scan-out with QR)
3. Ticket transfer between users
4. Email notifications for bookings/verifications
5. PDF ticket generation with embedded QR
6. Multi-admin event ownership (collaborators)
7. Advanced analytics (revenue trends, popular categories, conversion rates)
8. Mobile app for QR scanning (native camera access)

---

## Deployment Considerations

**Production Checklist**

- [ ] Use HTTPS for all services (required for camera access)
- [ ] Set up ngrok/tunnel for Midtrans webhook or deploy booking service publicly
- [ ] Configure proper CORS for production domains
- [ ] Use environment-specific JWT secrets
- [ ] Set up proper MySQL users with limited privileges per service
- [ ] Configure file upload storage (S3/CDN for images)
- [ ] Set up process manager (PM2 for Node services)
- [ ] Configure reverse proxy (nginx) for all services
- [ ] Set up SSL certificates (Let's Encrypt)
- [ ] Implement rate limiting on Gateway
- [ ] Add logging and monitoring (Winston, Sentry)
- [ ] Database backups and replication
- [ ] CDN for static assets (CSS, JS libraries)

**Environment Variables for Production**

- Change all localhost URLs to production domains
- Use strong JWT secrets (32+ characters)
- Set NODE_ENV=production for Express services
- Set APP_ENV=production for Laravel services
- Configure Midtrans production keys
- Set up proper database credentials
- Configure mail server for notifications

---

**Document Version:** 2.0 (Updated: 2026-06-21)  
**Original Spec:** DETAILED_IMPLEMENTATION.md v1.0  
**Implementation Status:** ? Complete with enhancements
