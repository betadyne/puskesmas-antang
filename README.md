# Sistem Informasi Antrian Pasien Puskesmas Antang

Sistem informasi antrian pasien untuk Puskesmas Antang Makassar dengan dukungan real-time broadcasting menggunakan Laravel 11, MySQL, dan Pusher.

## Fitur Utama

- Pendaftaran pasien online & offline
- Penomoran antrean otomatis per poli per hari (format: A001, B025, G001)
- Dashboard petugas untuk panggil nomor, lewati, panggil ulang, selesai
- Tampilan antrean real-time untuk TV display (publik)
- Broadcast perubahan antrean secara real-time
- Sistem role & permission yang ketat
- Laporan waktu tunggu & statistik harian

## Tech Stack

- **Backend**: Laravel 11.x
- **Database**: MySQL 8
- **Authentication**: Laravel Sanctum
- **Permission**: Spatie Laravel-Permission
- **Real-time**: Pusher/Laravel WebSockets
- **Documentation**: Laravel Scribe

## Setup & Installation

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8+
- Node.js (untuk frontend development)

### Langkah-langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd puskesmas-antang
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup Database**
   
   Edit `.env` file untuk konfigurasi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DATABASE=puskesmas_antang
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Setup Pusher** (untuk real-time features)
   
   Register akun Pusher dan update `.env`:
   ```env
   PUSHER_APP_ID=your_pusher_app_id
   PUSHER_APP_KEY=your_pusher_key
   PUSHER_APP_SECRET=your_pusher_secret
   PUSHER_APP_CLUSTER=mt1
   ```

6. **Run Migration & Seeder**
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   npm run dev
   ```

## Roles & Permissions

### Roles yang Tersedia

1. **admin** - Akses penuh ke seluruh sistem
2. **petugas** - Akses dashboard dan manage antrean di poli yang ditugaskan
3. **pasien** - Mendaftar online dan melihat status antrean
4. **public** - Akses tampilan TV display publik

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

## Default Accounts

### Admin
- **Email**: admin@puskesmas-antang.com
- **Password**: password
- **Role**: Admin

### Petugas
- **Email**: petugas@puskesmas-antang.com  
- **Password**: password
- **Role**: Petugas

## Format Nomor Antrean

Nomor antrean menggunakan format: `[Kode Poli][Nomor 3 digit]`

Contoh:
- A001 - Antrean Poli Umum nomor 1
- B025 - Antrean Poli Gigi nomor 25
- G001 - Antrean Poli KIA nomor 1

Reset setiap hari berdasarkan tanggal registrasi.

## API Endpoints

### Authentication
- `POST /api/login` - Login user

### Public
- `POST /api/register` - Pendaftaran online pasien
- `GET /api/display/{poli}` - Tampilan TV display
- `GET /api/queue/status/{nomor}` - Cek status antrean

### Dashboard (Petugas/Admin)
- `GET /api/dashboard/queues` - Data antrean poli sendiri
- `POST /api/queue/call-next` - Panggil nomor berikutnya
- `POST /api/queue/{id}/call` - Panggil manual (recall)
- `POST /api/queue/{id}/skip` - Lewati antrean
- `POST /api/queue/{id}/serve` - Mulai layani
- `POST /api/queue/{id}/finish` - Selesai

### Reports
- `GET /api/reports/daily` - Laporan harian
- `GET /api/reports/statistics` - Statistik

## Real-time Events

System menggunakan Pusher untuk real-time broadcasting:

### Channels
- `queue.{poli_id}` - Dashboard petugas & TV display
- `display.{poli_id}` - TV display publik

### Events
- `registration.created` - Pasien baru mendaftar
- `queue.called` - Antrean dipanggil
- `queue.skipped` - Antrean dilewati
- `queue.recalled` - Antrean dipanggil ulang
- `queue.finished` - Antrean selesai
- `queue.updated` - Status antrean diperbarui

## Cara Testing Real-time

1. Buka 2 browser tabs:
   - Tab 1: Login sebagai petugas (dashboard)
   - Tab 2: Buka tampilan display untuk poli yang sama

2. Di dashboard, panggil nomor antrean

3. Di tab display, update otomatis akan muncul

## Testing dengan Postman

Lihat file `puskesmas-queue.postman_collection.json` untuk Postman collection lengkap yang mencakup 90%+ API endpoints.

## Documentation

API documentation dapat diakses via:
```bash
php artisan scribe:generate
```

Kemudian buka `http://localhost:8000/docs`

## Troubleshooting

### Common Issues

1. **Migration Error**: Pastikan database MySQL sudah dibuat dan credentials di .env benar
2. **Real-time tidak bekerja**: Check konfigurasi Pusher di .env
3. **Permission denied**: Pastikan user sudah memiliki role yang tepat
4. **Foreign key constraint**: Run `php artisan migrate:fresh --seed` untuk clean setup

## Kontribusi

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## License

Project ini menggunakan license MIT. See LICENSE file untuk details.
