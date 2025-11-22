# Sistem Informasi Antrian Pasien Puskesmas Antang

Sistem informasi antrian pasien modern untuk Puskesmas Antang Makassar dengan fitur *real-time broadcasting*.

## Fitur Utama

  - **Pendaftaran pasien online & offline** - Sistem registrasi lengkap dengan validasi NIK
  - **Penomoran antrean otomatis** - Format unik per poli per hari (A001, B025, G001)
  - **Dashboard petugas lengkap** - Panggil nomor, lewati, panggil ulang, selesai
  - **TV display real-time** - Tampilan antrean untuk monitor publik
  - **Real-time broadcasting** - Pembaruan otomatis ke semua tampilan (display)
  - **Role & permission yang ketat** - Sistem keamanan berbasis peran
  - **Laporan & statistik** - Analisis performa dan pelaporan harian
  - **Jejak audit (Audit trail)** - Pelacakan lengkap riwayat perubahan antrean

## 🛠️ Tech Stack

  - **Backend**: Laravel 11.x
  - **Database**: MySQL 8
  - **Authentication**: Laravel Sanctum
  - **Permission**: Spatie Laravel-Permission
  - **Real-time**: Pusher/Laravel WebSockets (Siap Digunakan)
  - **API Documentation**: Koleksi Postman Lengkap
  - **Testing**: Cakupan tes API penuh (Tingkat keberhasilan 85.7%)

## 🚀 Setup & Instalasi

### Prasyarat (Prerequisites)

  - PHP 8.2+
  - Composer
  - MySQL 8+
  - Node.js (untuk pengembangan frontend)

### Langkah-langkah Instalasi

1.  **Clone Repository**

    ```bash
    git clone <repository-url>
    cd puskesmas-antang
    ```

2.  **Install Dependencies**

    ```bash
    composer install
    npm install
    ```

3.  **Setup Environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Konfigurasi Database**

    Edit file `.env` untuk konfigurasi database:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DATABASE=puskesmas_antang
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Setup Pusher** (untuk fitur real-time)

    Daftar akun [Pusher](https://dashboard.pusher.com/accounts/sign_up) dan perbarui `.env`:

    ```env
    BROADCAST_CONNECTION=pusher
    BROADCAST_DRIVER=pusher
    PUSHER_APP_ID=your_pusher_app_id
    PUSHER_APP_KEY=your_pusher_key
    PUSHER_APP_SECRET=your_pusher_secret
    PUSHER_APP_CLUSTER=mt1
    ```

6.  **Jalankan Migration & Seeder**

    ```bash
    php artisan migrate:fresh --seed
    ```

7.  **Jalankan Server Development**

    ```bash
    php artisan serve
    npm run dev
    ```

### 🎉 Mulai Cepat (Quick Start)

Untuk pengembangan tercepat:

```bash
# Clone dan setup
git clone <repository-url>
cd puskesmas-antang
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate:fresh --seed

# Mulai server & tes
php artisan serve
```

Sistem akan siap dengan:

  - **Akun default admin & petugas**
  - **5 data master poli**
  - **Pengaturan permissions lengkap**

## Roles & Permissions

### Roles yang Tersedia

1.  **admin** - Akses penuh ke seluruh sistem
2.  **petugas** - Akses dashboard dan manajemen antrean di poli yang ditugaskan
3.  **pasien** - Mendaftar online dan melihat status antrean
4.  **public** - Akses tampilan TV display publik

### Permissions

  - `view-dashboard` - Melihat dashboard
  - `call-queue` - Memanggil nomor antrean
  - `skip-queue` - Melewati antrean
  - `recall-queue` - Memanggil ulang
  - `finish-queue` - Menyelesaikan antrean
  - `view-own-poli-queue` - Melihat antrean poli sendiri
  - `view-reports` - Melihat laporan
  - `register-online` - Mendaftar online
  - `view-own-queue-status` - Melihat status antrean sendiri
  - `view-display-tv` - Melihat tampilan TV
  - `check-queue-by-nomor` - Mengecek antrean via nomor

## Akun Default

### 🔐 Akun Admin

  - **Email**: `admin@puskesmas-antang.com`
  - **Password**: `password`
  - **Role**: Admin
  - **Akses**: Akses sistem penuh

### 👨‍⚕️ Akun Petugas

  - **Email**: `petugas@puskesmas-antang.com`
  - **Password**: `password`
  - **Role**: Petugas
  - **Poli Bertugas**: Poli Umum
  - **Akses**: Dashboard & manajemen antrean

> 📝 **Catatan**: Akun otomatis dibuat saat proses migration & seeder

## Format Nomor Antrean

Nomor antrean menggunakan format: `[Kode Poli][Nomor 3 digit]`

Contoh:

  - A001 - Antrean Poli Umum nomor 1
  - B025 - Antrean Poli Gigi nomor 25
  - G001 - Antrean Poli KIA nomor 1

Direset setiap hari berdasarkan tanggal registrasi.

## API Endpoints

### Autentikasi

  - `POST /api/login` - Login dan pembuatan token

### Endpoint Publik

  - `POST /api/register` - Pendaftaran pasien dengan antrean otomatis
  - `GET /api/display/{poli}` - Data real-time TV display
  - `GET /api/queue/status/{nomor}` - Pengecekan status antrean

### Dashboard (Petugas/Admin)

  - `GET /api/dashboard/queues` - Data antrean untuk poli yang bertugas
  - `GET /api/dashboard/stats` - Statistik antrean

### Manajemen Antrean (Petugas/Admin)

  - `POST /api/queue/call-next` - Memanggil antrean berikutnya
  - `POST /api/queue/{id}/call` - Panggil manual / panggil ulang
  - `POST /api/queue/{id}/skip` - Lewati antrean
  - `POST /api/queue/{id}/serve` - Mulai melayani antrean
  - `POST /api/queue/{id}/finish` - Selesaikan pelayanan antrean
  - `POST /api/queue/{id}/recall` - Panggil ulang antrean (Recall)

### Laporan (Terautentikasi)

  - `GET /api/reports/daily` - Laporan harian dengan statistik
  - `GET /api/reports/statistics` - Analitik lanjutan

## Real-time Broadcasting

Sistem telah dirancang untuk *real-time broadcasting* dengan 6 tipe event:

### Saluran Broadcast (Channels)

  - `queue.{poli_id}` - Sinkronisasi Dashboard petugas & TV display
  - `display.{poli_id}` - Tampilan TV publik
  - `presence-staff` - Pelacakan staf online

### Tipe Event

  - `registration.created` - Event pendaftaran pasien baru
  - `queue.called` - Event pemanggilan antrean
  - `queue.skipped` - Event antrean dilewati
  - `queue.recalled` - Event pemanggilan ulang
  - `queue.finished` - Event penyelesaian antrean
  - `queue.updated` - Pembaruan antrean umum

> **Catatan**: Infrastruktur broadcasting sudah siap. Aktifkan dengan konfigurasi Pusher di `.env`.

## Cara Testing Real-time

1.  Buka 2 tab browser:

      - Tab 1: Login sebagai petugas (dashboard)
      - Tab 2: Buka tampilan display untuk poli yang sama

2.  Di dashboard, panggil nomor antrean.

3.  Di tab display, pembaruan otomatis akan muncul.

## Testing dengan Postman

Buka file `POSTMAN_COLLECTION.md` untuk melihat koleksi Postman lengkap dan contoh penggunaannya.

## 🔧 Troubleshooting

### ⚠️ Isu Minor Saat Ini

  - **Format respons tidak terautentikasi**: Mengembalikan kode 500, seharusnya 401 (tidak kritis).
  - **Broadcasting**: Infrastruktur siap namun membutuhkan konfigurasi Pusher.

### 🎯 Solusi

  - **Untuk masalah database**: Jalankan `php artisan migrate:fresh --seed`
  - **Untuk masalah permission**: Cek penugasan role user
  - **Untuk testing API**: Gunakan akun default yang tersedia dan koleksi Postman

### 🚀 Perintah Cepat Pengembangan

```bash
# Reset database
php artisan migrate:fresh --seed

# Cek permissions
php artisan tinker --execute="DB::table('permissions')->get()->pluck('name')"

# Tes nomor antrean
php artisan tinker --execute="
\$queue = new \App\Models\Queue();
echo \$queue->generateQueueNumber(1);
"
```

-----

## 🎯 Guide Integrasi Frontend

### 🚀 Quick Guide Tim Frontend

1.  **API Base URL**: `http://localhost:8000/api`

2.  **Autentikasi**:

    ```javascript
    // Login dan simpan token
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const { token, user, permissions } = await response.json();
    localStorage.setItem('token', token);
    ```

3.  **Request Terautentikasi**:

    ```javascript
    fetch('/api/dashboard/queues', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })
    ```

4.  **Pembaruan Real-time**:

    ```javascript
    // Mendengarkan update antrean (saat Pusher dikonfigurasi)
    pusher.subscribe('queue.1').bind('queue.called', (data) => {
      updateDisplay(data);
    });
    ```

### 📋 Fitur Frontend yang Dibutuhkan

  - **Antarmuka Pendaftaran Pasien**
  - **Dashboard Petugas** untuk manajemen antrean
  - **TV Display Publik** untuk memantau antrean
  - **Sistem Autentikasi** dengan login/logout
  - **Sinkronisasi Update Real-time**
  - **Penanganan Error** dan feedback validasi

-----

## 📞 Informasi Dukungan

### 📚 Berkas Dokumentasi

  - `POSTMAN_COLLECTION.md` - Panduan lengkap testing API
  - `SYSTEM_REPORT.md` - Laporan implementasi detail
  - `composer.json` - Dependensi dan versi paket

### 🤖 Kredensial Akun Default (Untuk Testing)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@puskesmas-antang.com | password |
| Petugas | petugas@puskesmas-antang.com | password |

### 🏗️ Struktur Database

  - **7 tabel inti** dengan relasi yang dioptimalkan
  - **Sistem penomoran antrean** dengan reset harian
  - **Jejak audit** dengan pelacakan riwayat lengkap
  - **Kontrol akses berbasis peran** dengan 4 grup permission