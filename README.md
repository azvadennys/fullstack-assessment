# TaskFlow - Full Stack Task Management Platform

Aplikasi manajemen tugas modern yang dibangun menggunakan **Laravel 12 (Backend)** dan **Nuxt 3 + Tailwind CSS (Frontend)**. Proyek ini mendukung fitur real-time, file upload, video streaming, dan background processing.

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
