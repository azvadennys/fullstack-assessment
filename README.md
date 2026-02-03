# TaskFlow - Full Stack Task Management Platform

Aplikasi manajemen tugas modern yang dibangun menggunakan **Laravel 12 (Backend)** dan **Nuxt 4 + Tailwind CSS (Frontend)**. Proyek ini mendukung fitur real-time, file upload, video streaming, dan background processing.

## Demo Video


https://github.com/user-attachments/assets/8269f179-f2b0-4825-a2a0-d257b981d1df


## 📋 Prasyarat Sistem (Prerequisites)

Pastikan komputer Anda sudah terinstall:
- **PHP** >= 8.2
- **Composer**
- **Node.js** (LTS version, v22) & **NPM**
- **MySQL** Database

---

## 🛠️ Instalasi & Setup

### Langkah 1: Clone Project
```bash
git clone https://github.com/azvadennys/fullstack-assessment.git
cd fullstack-assessment
```

### Langkah 2: Setup Backend (Laravel)
#### 1. Masuk ke folder backend:

```bash
cd backend
```

#### 2. Install dependency PHP:

```bash
composer install
```

#### 3. Setup Environment Variable: Duplikat file .env.example menjadi .env

```bash
cp .env.example .env
```

#### 4. Konfigurasi Database di file .env: Buka file .env dan sesuaikan koneksi database Anda:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management  # Pastikan buat database ini di MySQL
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Queue & Filesystem
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

#### 5. Generate Application Key:

```bash
php artisan key:generate
```

#### 6. Setup API & Storage Link:

```bash
php artisan install:api
php artisan storage:link
```

#### 7. Migrasi & Seeding Database: Ini akan membuat tabel dan mengisi data dummy (User Admin & Tasks).

```bash
php artisan migrate:fresh --seed
```

### Langkah 3: Setup Frontend (Nuxt.js)
#### 1. Buka terminal baru, masuk ke folder frontend:

```bash
cd frontend
```

#### 2. Install dependency Node:

```bash
npm install
```
Jika ada error terkait @tailwindcss/forms, jalankan:
```bash
npm install -D @tailwindcss/forms
```

#### 3. Konfigurasi URL API (Opsional): Pastikan di nuxt.config.ts, apiBase mengarah ke backend local Anda:

```TypeScript
runtimeConfig: {
  public: {
    apiBase: 'http://localhost:8000/api'
  }
}
```


## Cara Menjalankan Aplikasi
Anda membutuhkan 3 Terminal yang berjalan secara bersamaan agar seluruh fitur (API, Upload, Queue) berfungsi.

### Terminal 1: Backend Server
Menjalankan server Laravel.
```bash
cd backend
php artisan serve
```

### Terminal 2: Queue Worker
Menjalankan background job untuk proses upload file, video thumbnail, dll. Tanpa ini, status upload akan stuck.
```bash
cd backend
php artisan queue:work
```

### Terminal 3: Frontend Server
Menjalankan aplikasi Nuxt.js.
```bash
cd frontend
npm run dev
```

## Akun Login (Default)
Gunakan akun berikut untuk login pertama kali:
- Email: admin@example.com
- Password: password


## Fitur Utama
1. Authentication: Login menggunakan Token (Sanctum).
2. Dashboard: CRUD Task dengan filter Status & Priority.
3. File Upload:
   - Drag & Drop interface.
   - Progress bar upload.
   - Support Gambar & Video.
4. Video Player: Preview otomatis untuk file video (Bonus Challenge).
5. Comments: Sistem komentar simulasi real-time.


# 📚 TaskFlow API Documentation

Dokumentasi teknis untuk endpoint API TaskFlow.

- **Base URL:** `http://localhost:8000/api`
- **Content-Type:** `application/json` (Kecuali Upload File)
- **Accept:** `application/json`

## 🔐 Authentication & Headers

Setiap request ke endpoint tertutup (Protected) **wajib** menyertakan header berikut:

| Key | Value |
| :--- | :--- |
| `Accept` | `application/json` |
| `Authorization` | `Bearer <access_token>` |

---

## 1. Auth Endpoints

### 🟢 Login User
Membuat sesi baru dan mendapatkan token akses.

- **Endpoint:** `POST /auth/login`
- **Auth:** Public

**Request Body:**
```json
{
  "email": "admin@example.com",  // Required, Valid Email
  "password": "password"         // Required
}
```
**Response (200 OK):**
```json
{
  "message": "Login success",
  "access_token": "1|ExamplEToKeN...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "created_at": "2024-02-01T10:00:00.000000Z",
    "updated_at": "2024-02-01T10:00:00.000000Z"
  }
}
```
**Response (401 Unauthorized):**

```json
{
  "message": "Invalid login details"
}

```

### 🟢 Get Current User (Me)
Mendapatkan profil user pemilik token.
- Endpoint: GET /auth/me
- Auth: Bearer Token

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "email_verified_at": null,
  "role": "admin",
  "created_at": "2024-02-01T10:00:00.000000Z",
  "updated_at": "2024-02-01T10:00:00.000000Z"
}
```

### 🟢 Logout
Menghapus token akses saat ini (Invalidate Token).
- Endpoint: POST /auth/logout
- Auth: Bearer Token
  
```json
{
  "message": "Logged out successfully"
}
```

## 2. Task Management Endpoints
### 🟢 List Tasks
Mendapatkan daftar tugas dengan pagination, filter, dan sorting.
- Endpoint: GET /tasks
- Auth: Bearer Token

**Query Parameters (Input):**
| Param | Type | Required | Description | Example |
| :--- | :--- | :---: | :--- | :--- |
| `page` | int | No | Halaman pagination | `1` |
| `status` | string | No | Filter status: `pending`, `in_progress`, `completed` | `pending` |
| `priority` | string | No | Filter priority: `low`, `medium`, `high` | `high` |
| `search` | string | No | Cari text di Title/Description | `bug fix` |
| `sort_by` | string | No | Kolom sorting (Default: `created_at`) | `due_date` |
| `sort_dir` | string | No | Arah sorting (`asc`, `desc`) | `desc` |

**Response (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 10,
      "title": "Fix Login Bug",
      "description": "User cannot login via mobile app",
      "status": "pending",
      "priority": "high",
      "assigned_user_id": 2,
      "created_by": 1,
      "due_date": "2024-12-31",
      "created_at": "2024-10-01T12:00:00.000000Z",
      "updated_at": "2024-10-01T12:00:00.000000Z",
      "assignee": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "creator": {
        "id": 1,
        "name": "Admin User"
      },
      "attachments": [
        {
          "id": 5,
          "task_id": 10,
          "file_name": "error_log.txt",
          "file_path": "attachments/10/xyz.txt",
          "file_size": 1024,
          "mime_type": "text/plain",
          "uploaded_at": "2024-10-01T12:05:00.000000Z"
        }
      ]
    }
  ],
  "first_page_url": "http://localhost:8000/api/tasks?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://localhost:8000/api/tasks?page=5",
  "next_page_url": "http://localhost:8000/api/tasks?page=2",
  "path": "http://localhost:8000/api/tasks",
  "per_page": 8,
  "prev_page_url": null,
  "to": 8,
  "total": 40
}
```

### 🟢 Create Task
Membuat tugas baru.
- Endpoint: POST /tasks
- Auth: Bearer Token

**Request Body:**
```
{
  "title": "Design Database Schema",   // Required, String
  "description": "ERD for project X",  // Optional, String
  "status": "pending",                 // Optional, Enum: pending, in_progress, completed
  "priority": "high",                  // Optional, Enum: low, medium, high
  "assigned_user_id": 2,               // Optional, Int (User ID)
  "due_date": "2024-11-20"             // Optional, Date (YYYY-MM-DD)
}
```

**Response (201 Created):**
```
{
  "id": 15,
  "title": "Design Database Schema",
  "description": "ERD for project X",
  "status": "pending",
  "priority": "high",
  "assigned_user_id": 2,
  "created_by": 1,
  "due_date": "2024-11-20",
  "created_at": "2024-10-05T10:00:00.000000Z",
  "updated_at": "2024-10-05T10:00:00.000000Z"
}
```

### 🟢 Update Task
Mengubah data tugas (Partial Update).
- Endpoint: PUT /tasks/{id}
- Auth: Bearer Token

**Request Body (Kirim field yang ingin diubah saja):**
```
{
  "status": "completed",
  "priority": "low"
}
```
**Response (200 OK):**
```
{
  "id": 15,
  "title": "Design Database Schema",
  "status": "completed",
  "priority": "low",
  "updated_at": "2024-10-06T15:00:00.000000Z",
  ... // Sisa field lainnya
}
```
