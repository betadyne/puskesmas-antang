# TECHNICAL SPECIFICATION DOCUMENT  
**Sistem Informasi Antrian Pasien Puskesmas Antang Makassar**  
**Backend Development Scope – Junior Backend Developer**  

**Versi Dokumen**: 1.0  
**Tanggal**: 19 November 2025  
**Tech Stack**: Laravel 11.x + MySQL 8 + TailwindCSS (untuk Blade jika ada) + Laravel Sanctum + Spatie Laravel-Permission + Pusher/Laravel WebSockets (Real-time)

### 1. Tujuan Dokumen  
Dokumen ini mendefinisikan seluruh lingkup pekerjaan backend (base system, authentication, role & permission, middleware, API, real-time, database) yang harus diselesaikan oleh Junior Backend Developer sebelum backend diserahkan ke tim Frontend.

### 2. Fitur Utama yang Harus Didukung Backend  
1. Pendaftaran pasien online & offline  
2. Penomoran antrean otomatis per poli per hari (contoh: A001, B025, G001)  
3. Dashboard petugas (panggil nomor, lewati, panggil ulang, selesai)  
4. Tampilan antrean real-time untuk TV display (publik)  
5. Broadcast perubahan antrean secara real-time  
6. Sistem role & permission yang ketat  
7. Laporan waktu tunggu & statistik harian  
8. API yang aman dan terdokumentasi dengan baik

### 3. Database Design (ERD Minimal)

| Tabel                | Kolom Utama                                                                                  | Keterangan                                      |
|----------------------|----------------------------------------------------------------------------------------------|-------------------------------------------------|
| users                | id, name, email, password, role_id, poli_id (nullable), remember_token                      | Laravel default + extensi                       |
| roles & permissions  | (dikelola Spatie)                                                                            |                                                 |
| role_has_permissions | (dikelola Spatie)                                                                            |                                                 |
| model_has_roles      | (dikelola Spatie)                                                                            |                                                 |
| patients             | id, nik, nama, no_bpjs, tgl_lahir, jenis_kelamin, no_hp, alamat                             | Data pasien                                     |
| registrations        | id, patient_id, tanggal_daftar, poli_tujuan, cara_daftar (online/offline), status           | Pendaftaran                                     |
| queues               | id, registration_id, nomor_antrean, poli, status (menunggu/dipanggil/sedang dilayani/selesai/dilewati), called_at, served_at, finished_at, petugas_id | Inti antrean                                    |
| poli                 | id, kode_poli, nama_poli                                                                     | Master poli (Umum, Gigi, KIA, dll)              |
| queue_histories      | (opsional) untuk audit trail                                                                 |                                                 |

### 4. Roles & Permissions (Spatie)

| Role             | Permissions Utama (minimal)                                                                                   |
|------------------|---------------------------------------------------------------------------------------------------------------|
| admin            | semua permission (gunakan givePermissionTo('*') atau assign semua)                                           |
| petugas          | view-dashboard, call-queue, skip-queue, recall-queue, finish-queue, view-own-poli-queue, view-reports        |
| pasien           | register-online, view-own-queue-status                                                                        |
| public           | view-display-tv, check-queue-by-nomor                                                                         |

### 5. Custom Middleware
1. `Role`          → cek role user (contoh: role:admin,petugas)
2. `PoliAccess`    → petugas hanya bisa akses data poli yang ditugaskan
3. `QueueOwnership` → petugas hanya boleh memanggil antrean poli sendiri

### 6. Authentication & API
- Laravel Sanctum (SPA token)
- Login return Sanctum token + user data + permissions
- Semua route API (kecuali publik) → middleware `auth:sanctum`

### 7. Real-Time Engine
- Pusher (rekomendasi termudah) atau Laravel WebSockets
- Events yang wajib dibroadcast:
  - NewRegistration
  - QueueCalled
  - QueueSkipped
  - QueueRecalled
  - QueueFinished
  - QueueUpdated
- Channels:
  - `queue.{poli_slug}`      → didengar dashboard petugas & TV display
  - `presence-dashboard`    → untuk deteksi petugas online (opsional)

### 8. Penomoran Antrean (Business Logic)
- Format: [Kode Poli][Nomor 3 digit] → contoh A001, B025
- Reset setiap hari (berdasarkan tanggal_daftar)
- Diambil dari max nomor hari ini + 1, dengan locking database (pessimistic lock) untuk menghindari race condition

### 9. API Routes (api.php)

| Method | Route                              | Middleware                     | Deskripsi                                      |
|--------|------------------------------------|--------------------------------|------------------------------------------------|
| POST   | /api/login                         | guest                          | Return token + user                            |
| POST   | /api/register                      | public (tanpa auth)            | Pendaftaran online pasien                      |
| GET    | /api/display/{poli}                | public                         | Untuk TV display (return 5 antrean terakhir + sedang dipanggil) |
| GET    | /api/queue/status/{nomor}          | public                         | Cek status antrean oleh pasien                 |
| GET    | /api/dashboard/queues              | auth + role:petugas + PoliAccess | Data antrean poli sendiri                   |
| POST   | /api/queue/call-next               | auth + role:petugas            | Panggil nomor berikutnya                       |
| POST   | /api/queue/{id}/call               | auth + role:petugas            | Panggil manual (recall)                        |
| POST   | /api/queue/{id}/skip               | auth + role:petugas            | Lewati                                         |
| POST   | /api/queue/{id}/serve              | auth + role:petugas            | Mulai layani (sedang dilayani)                 |
| POST   | /api/queue/{id}/finish             | auth + role:petugas            | Selesai                                        |
| GET    | /api/reports/daily                 | auth + role:admin,petugas      | Laporan waktu tunggu harian                    |

### 10. Deliverables yang Harus Diserahkan ke Tim Frontend
1. Repository Git (branch `backend-ready`) yang bisa di-clone & langsung jalan
2. `.env.example` lengkap
3. Postman Collection + Environment (WAJIB, minimal 90% coverage API)
4. Dokumentasi Event Broadcasting (channel & event name yang harus di-listen)
5. Swagger / Laravel Scribe (bonus)
6. README.md berisi:
   - Cara install & setup
   - Urutan migration & seeder
   - Penjelasan format nomor antrean
   - List roles & permissions
   - Cara test real-time (buka 2 tab)

### 11. Final Acceptance Checklist (Harus 100% Centang)
- [ ] Semua migration & seeder jalan tanpa error  
- [ ] Role & permission sudah lengkap  
- [ ] Login → token Sanctum berhasil  
- [ ] Pendaftaran online → nomor antrean masuk + broadcast  
- [ ] Petugas panggil nomor → update real-time di tab lain  
- [ ] TV display endpoint bisa diakses publik & update otomatis  
- [ ] Postman collection lengkap & sudah dishare  
- [ ] Nomor antrean reset per hari per poli  
- [ ] Laporan waktu tunggu sudah akurat  
- [ ] Tidak ada error 500 di log saat testing intensif