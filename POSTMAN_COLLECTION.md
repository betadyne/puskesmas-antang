# Postman Collection - Sistem Antrean Puskesmas Antang

## Environment Variables

```
BASE_URL = http://localhost:8000
```

## Autentikasi (Authentication)

### 1\. Login (Publik)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/login`
  - **Headers**:
      - Content-Type: application/json
  - **Body** (Raw JSON):

<!-- end list -->

```json
{
    "email": "admin@puskesmas-antang.com",
    "password": "password"
}
```

  - **Response**:

<!-- end list -->

```json
{
    "message": "Login successful",
    "data": {
        "token": "1|xxxxxxxxxxxxxxxxxxx",
        "user": {...},
        "permissions": [...],
        "roles": [...]
    }
}
```

### 2\. Login sebagai Petugas

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/login`
  - **Body**:

<!-- end list -->

```json
{
    "email": "petugas@puskesmas-antang.com",
    "password": "password"
}
```

## Pendaftaran Pasien

### 3\. Daftar Pasien (Publik)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/register`
  - **Headers**:
      - Content-Type: application/json
  - **Body**:

<!-- end list -->

```json
{
    "nik": "1234567890123456",
    "nama": "Ahmad Wijaya",
    "no_bpjs": "1234567890123",
    "tgl_lahir": "1990-05-15",
    "jenis_kelamin": "L",
    "no_hp": "08123456789",
    "email": "ahmad@example.com",
    "alamat": "Jl. Contoh No. 123, Makassar",
    "poli_tujuan": 1,
    "cara_daftar": "online"
}
```

  - **Response**:

<!-- end list -->

```json
{
    "message": "Registration successful",
    "data": {
        "registration": {...},
        "queue": {
            "id": 1,
            "nomor_antrean": "A001",
            "status": "menunggu",
            "poli": {...}
        },
        "patient": {...},
        "poli": {...}
    }
}
```

## Tampilan / Display (Akses Publik)

### 4\. TV Display - Tampilkan Antrean per Poli

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/display/A` (atau kode poli: B, C, D, E)
  - **Headers**:
      - Content-Type: application/json
  - **Response**:

<!-- end list -->

```json
{
    "message": "Display data retrieved successfully",
    "data": {
        "poli": {...},
        "current_queue": {...},
        "waiting_queues": [...],
        "recent_queues": [...],
        "statistics": {
            "total_waiting": 5,
            "total_served": 10,
            "total_skipped": 2
        }
    }
}
```

### 5\. Cek Status Antrean berdasarkan Nomor

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/queue/status/A001`
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue status retrieved successfully",
    "data": {
        "queue": {...},
        "status_text": "Menunggu",
        "position_in_queue": 3,
        "estimated_wait_time": 45
    }
}
```

## Dashboard (Terautentikasi - Petugas/Admin)

### 6\. Ambil Data Antrean Dashboard

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/dashboard/queues`
  - **Headers**:
      - Authorization: Bearer {{token}}
      - Content-Type: application/json
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue data retrieved successfully",
    "data": [
        {
            "id": 1,
            "nomor_antrean": "A001",
            "status": "menunggu",
            "poli": {...},
            "patient": {...}
        }
    ]
}
```

### 7\. Ambil Statistik Dashboard

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/dashboard/stats`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Statistics retrieved successfully",
    "data": {
        "total_waiting": 5,
        "total_being_served": 1,
        "total_finished": 10,
        "total_skipped": 2,
        "avg_wait_time": 25.5
    }
}
```

### 8\. Ambil Antrean Saya (Pasien Terautentikasi)

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/my-queue`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "User queue retrieved successfully",
    "data": {
        "queue": {...},
        "status_text": "Menunggu",
        "position_in_queue": 3,
        "estimated_wait_time": 45
    }
}
```

## Manajemen Antrean (Terautentikasi - Petugas/Admin)

### 9\. Panggil Antrean Berikutnya

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/queue/call-next`
  - **Headers**:
      - Authorization: Bearer {{token}}
      - Content-Type: application/json
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue called successfully",
    "data": {
        "id": 1,
        "nomor_antrean": "A001",
        "status": "dipanggil",
        "called_at": "14:30"
    }
}
```

### 10\. Panggil Antrean Spesifik (Panggil Ulang/Manual)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/queue/1/call`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**: Sama seperti Panggil Berikutnya (Call Next)

### 11\. Lewati Antrean (Skip)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/queue/1/skip`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue skipped successfully",
    "data": {
        "id": 1,
        "nomor_antrean": "A001",
        "status": "dilewati"
    }
}
```

### 12\. Mulai Layani Antrean (Serve)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/queue/1/serve`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue serving started",
    "data": {
        "id": 1,
        "nomor_antrean": "A001",
        "status": "sedang dilayani",
        "served_at": "14:35"
    }
}
```

### 13\. Selesaikan Antrean (Finish)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/queue/1/finish`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue finished successfully",
    "data": {
        "id": 1,
        "nomor_antrean": "A001",
        "status": "selesai",
        "finished_at": "14:45"
    }
}
```

### 14\. Panggil Ulang Antrean (Recall)

  - **Method**: POST
  - **URL**: `{{BASE_URL}}/api/queue/1/recall`
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Queue recalled successfully",
    "data": {
        "id": 1,
        "nomor_antrean": "A001",
        "status": "dipanggil",
        "called_at": "14:50"
    }
}
```

## Laporan (Terautentikasi - Admin/Petugas)

### 15\. Laporan Harian

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/reports/daily`
  - **Parameter Query** (opsional):
      - `date`: YYYY-MM-DD (default hari ini)
      - `poli_id`: 1,2,3,4,5
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Daily report generated successfully",
    "data": {
        "statistics": {
            "total_queues": 15,
            "total_waiting": 5,
            "total_finished": 10,
            "avg_wait_time": 25.5
        },
        "by_poli": {...},
        "queues": [...]
    }
}
```

### 16\. Laporan Statistik

  - **Method**: GET
  - **URL**: `{{BASE_URL}}/api/reports/statistics`
  - **Parameter Query** (opsional):
      - `period`: week|month|custom
      - `start_date`: YYYY-MM-DD
      - `end_date`: YYYY-MM-DD
      - `poli_id`: 1,2,3,4,5
  - **Headers**:
      - Authorization: Bearer {{token}}
  - **Response**:

<!-- end list -->

```json
{
    "message": "Statistics generated successfully",
    "data": {
        "overall": {
            "period": {...},
            "total_registrations": 100,
            "completion_rate": 85.5
        },
        "daily_breakdown": [...],
        "by_poli": {...}
    }
}
```

## Respons Error

### Format Error Umum

**401 Unauthorized (Tidak Terautentikasi)**:

```json
{
    "message": "Unauthenticated."
}
```

**403 Forbidden (Terlarang)**:

```json
{
    "message": "Anda tidak memiliki akses ke poli ini",
    "errors": {
        "poli": ["Unauthorized access to poli"]
    }
}
```

**422 Validation Error (Validasi Gagal)**:

```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

**404 Not Found (Tidak Ditemukan)**:

```json
{
    "message": "Antrean tidak ditemukan",
    "data": null
}
```

## Event WebSocket (Real-time)

### Channel untuk Didengarkan (Listen):

1.  **Update Antrean**: `queue.{poli_id}` (cth: `queue.1`)

      - Events: `queue.called` (dipanggil), `queue.skipped` (dilewati), `queue.finished` (selesai), `queue.updated` (diperbarui)
      - Digunakan untuk dashboard dan TV display.

2.  **Update Display**: `display.{poli_id}` (cth: `display.1`)

      - Events: `queue.called`, `queue.skipped`, `queue.finished`, `registration.created`
      - Khusus untuk TV display.

3.  **Antrean Individu**: `queue.{queue_id}.updates`

      - Events: Semua update antrean untuk antrean spesifik
      - Digunakan untuk pelacakan pasien individu.

### Contoh Payload Event:

```json
{
    "event": "queue.called",
    "data": {
        "queue": {
            "id": 1,
            "nomor_antrean": "A001",
            "poli": {...},
            "status": "dipanggil",
            "patient": {
                "nama": "Patient Name"
            }
        },
        "message": "Nomor antrean A001 dipanggil"
    }
}
```

## Tips Pengujian (Testing)

1.  **Dapatkan Token Terlebih Dahulu**: Selalu login dulu dan salin token untuk request yang membutuhkan autentikasi.
2.  **Tes Role Berbeda**: Tes dengan akun admin dan petugas untuk melihat level akses yang berbeda.
3.  **Testing Real-time**: Buka beberapa tab browser - satu untuk dashboard, satu untuk TV display.
4.  **Alur Antrean**: Lakukan urutan Daftar → Panggil → Layani → Selesai untuk mengetes alur kerja lengkap.
5.  **Kasus Error**: Coba akses data lintas-poli dengan akun petugas untuk mengetes middleware.

## Akun Tes Default

  - **Admin**: admin@puskesmas-antang.com / password
  - **Petugas**: petugas@puskesmas-antang.com / password

## Kode Poli

  - **A**: Poli Umum
  - **B**: Poli Gigi
  - **C**: Poli KIA
  - **D**: Poli Lansia
  - **E**: Poli Gizi

Gunakan kode ini untuk endpoint display dan testing.
