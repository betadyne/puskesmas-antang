# Sistem Informasi Antrian Puskesmas Antang

Aplikasi manajemen antrian pasien berbasis web untuk Puskesmas Antang. Sistem ini memungkinkan pasien mendaftar antrian secara online, memantau status antrian real-time, dan membantu petugas mengelola pelayanan dengan lebih efisien.

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Struktur Project](#struktur-project)
- [API Endpoints](#api-endpoints)
- [Akun Default](#akun-default)

## Fitur

### Untuk Pasien (Public)
- Pendaftaran antrian online tanpa perlu membuat akun
- Cetak dan simpan tiket antrian sebagai gambar
- Cek status antrian real-time
- Estimasi waktu tunggu

### Untuk Petugas
- Dashboard manajemen antrian
- Panggil, layani, dan selesaikan antrian
- Lewati pasien yang tidak hadir
- Laporan harian dan statistik pelayanan

### Untuk Admin
- Manajemen pengguna (petugas dan admin)
- Manajemen poli/unit pelayanan
- Data pasien terdaftar
- Dashboard statistik keseluruhan

### TV Display
- Tampilan nomor antrian untuk monitor/TV
- Update real-time menggunakan WebSocket
- Pengumuman suara otomatis (Text-to-Speech)
- Dukungan fullscreen

## Teknologi

### Backend
- PHP 8.2+
- Laravel 11
- MySQL/MariaDB
- Laravel Sanctum (Authentication)
- Spatie Permission (Role & Permission)
- Laravel Echo + Pusher (Real-time)

### Frontend
- Nuxt 3 (Vue 3)
- TypeScript
- TailwindCSS
- Nuxt UI
- Pinia (State Management)
- Laravel Echo Client
- html2canvas (Save as Image)

## Persyaratan Sistem

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.0
- npm >= 9.0
- MySQL >= 8.0 atau MariaDB >= 10.4
- Git

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/puskesmas-antang.git
cd puskesmas-antang
```

### 2. Setup Backend (Laravel)

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Buat database MySQL dengan nama 'puskesmas_antang'

# Jalankan migrasi dan seeder
php artisan migrate --seed
```

### 3. Setup Frontend (Nuxt)

```bash
# Masuk ke folder frontend
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env
```

## Konfigurasi

### Backend (.env)

```env
APP_NAME="Puskesmas Antang"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puskesmas_antang
DB_USERNAME=root
DB_PASSWORD=

# Pusher (untuk real-time updates)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

BROADCAST_CONNECTION=pusher
```

### Frontend (frontend/.env)

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api
NUXT_PUBLIC_PUSHER_KEY=your_pusher_key
NUXT_PUBLIC_PUSHER_CLUSTER=mt1
```

## Menjalankan Aplikasi

### Development

Terminal 1 - Backend:
```bash
php artisan serve
```

Terminal 2 - Frontend:
```bash
cd frontend
npm run dev
```

Akses aplikasi:
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api

### Production

Backend:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Frontend:
```bash
cd frontend
npm run build
npm run preview
```

## Struktur Project

```
puskesmas-antang/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── QueueController.php
│   │   │   ├── RegistrationController.php
│   │   │   ├── DisplayController.php
│   │   │   ├── ReportController.php
│   │   │   ├── UserController.php
│   │   │   ├── PoliController.php
│   │   │   └── PatientController.php
│   │   ├── Middleware/
│   │   └── Resources/
│   └── Models/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── frontend/
│   ├── assets/css/
│   ├── components/
│   ├── composables/
│   ├── layouts/
│   ├── middleware/
│   ├── pages/
│   │   ├── index.vue (Landing)
│   │   ├── register.vue (Pendaftaran)
│   │   ├── status.vue (Cek Status)
│   │   ├── login.vue (Login Petugas)
│   │   ├── dashboard/ (Dashboard Petugas)
│   │   ├── admin/ (Panel Admin)
│   │   └── display/ (TV Display)
│   ├── plugins/
│   ├── public/
│   ├── stores/
│   ├── types/
│   └── nuxt.config.ts
└── README.md
```

## API Endpoints

### Public (Tanpa Autentikasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | /api/login | Login pengguna |
| POST | /api/register | Registrasi antrian baru |
| GET | /api/poli | Daftar poli |
| GET | /api/poli/queue-stats | Statistik antrian per poli |
| GET | /api/display/{poli} | Data display untuk TV |
| GET | /api/queue/status/{nomor} | Cek status antrian |

### Protected (Memerlukan Autentikasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | /api/user | Data user yang login |
| POST | /api/logout | Logout |
| GET | /api/dashboard/queues | Daftar antrian dashboard |
| POST | /api/queue/call-next | Panggil antrian berikutnya |
| POST | /api/queue/{id}/call | Panggil antrian spesifik |
| POST | /api/queue/{id}/finish | Selesaikan pelayanan |
| POST | /api/queue/{id}/skip | Lewati antrian |
| POST | /api/queue/{id}/recall | Panggil ulang |
| GET | /api/reports/daily | Laporan harian |
| GET | /api/reports/statistics | Statistik periode |

### Admin Only

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | /api/admin/stats | Statistik dashboard admin |
| GET/POST/PUT/DELETE | /api/admin/users | CRUD pengguna |
| GET/POST/PUT/DELETE | /api/admin/polis | CRUD poli |
| GET/PUT/DELETE | /api/admin/patients | Manajemen pasien |

## Akun Default

Setelah menjalankan seeder, akun berikut tersedia:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@puskesmas-antang.com | password |
| Petugas | petugas@puskesmas-antang.com | password |

## Halaman Aplikasi

### Public
- `/` - Halaman utama dengan informasi antrian per poli
- `/register` - Form pendaftaran antrian online
- `/status` - Cek status antrian berdasarkan nomor

### Display (TV Monitor)
- `/display` - Pilihan poli untuk ditampilkan
- `/display/{poli_id}` - Display antrian poli tertentu

### Dashboard Petugas
- `/login` - Halaman login
- `/dashboard` - Manajemen antrian (panggil, selesai, lewati)
- `/dashboard/reports` - Laporan dan statistik

### Admin Panel
- `/admin` - Dashboard admin
- `/admin/users` - Kelola pengguna
- `/admin/poli` - Kelola poli
- `/admin/patients` - Data pasien

## Lisensi

Hak Cipta 2024 Puskesmas Antang. Seluruh hak dilindungi.
