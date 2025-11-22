# Postman Collection - Puskesmas Antang Queue System

## Environment Variables
```
BASE_URL = http://localhost:8000
```

## Authentication

### 1. Login (Public)
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/login`
- **Headers**: 
  - Content-Type: application/json
- **Body** (Raw JSON):
```json
{
    "email": "admin@puskesmas-antang.com",
    "password": "password"
}
```
- **Response**:
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

### 2. Login as Petugas
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/login`
- **Body**:
```json
{
    "email": "petugas@puskesmas-antang.com",
    "password": "password"
}
```

## Patient Registration

### 3. Register Patient (Public)
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/register`
- **Headers**: 
  - Content-Type: application/json
- **Body**:
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

## Display (Public Access)

### 4. TV Display - Show Queue for Specific Poli
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/display/A` (or poli code: B, C, D, E)
- **Headers**: 
  - Content-Type: application/json
- **Response**:
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

### 5. Check Queue Status by Number
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/queue/status/A001`
- **Response**:
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

## Dashboard (Authenticated - Petugas/Admin)

### 6. Get Dashboard Queues
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/dashboard/queues`
- **Headers**: 
  - Authorization: Bearer {{token}}
  - Content-Type: application/json
- **Response**:
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

### 7. Get Dashboard Statistics
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/dashboard/stats`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

### 8. Get My Queue (Authenticated Patient)
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/my-queue`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

## Queue Management (Authenticated - Petugas/Admin)

### 9. Call Next Queue
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/queue/call-next`
- **Headers**: 
  - Authorization: Bearer {{token}}
  - Content-Type: application/json
- **Response**:
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

### 10. Call Specific Queue (Recall)
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/queue/1/call`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**: Same as Call Next

### 11. Skip Queue
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/queue/1/skip`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

### 12. Start Serving Queue
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/queue/1/serve`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

### 13. Finish Queue
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/queue/1/finish`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

### 14. Recall Queue
- **Method**: POST
- **URL**: `{{BASE_URL}}/api/queue/1/recall`
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

## Reports (Authenticated - Admin/Petugas)

### 15. Daily Report
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/reports/daily`
- **Query Parameters** (optional):
  - `date`: YYYY-MM-DD (defaults to today)
  - `poli_id`: 1,2,3,4,5
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

### 16. Statistics Report
- **Method**: GET
- **URL**: `{{BASE_URL}}/api/reports/statistics`
- **Query Parameters** (optional):
  - `period`: week|month|custom
  - `start_date`: YYYY-MM-DD
  - `end_date`: YYYY-MM-DD
  - `poli_id`: 1,2,3,4,5
- **Headers**: 
  - Authorization: Bearer {{token}}
- **Response**:
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

## Error Responses

### Common Error Formats
**401 Unauthorized**:
```json
{
    "message": "Unauthenticated."
}
```

**403 Forbidden**:
```json
{
    "message": "Anda tidak memiliki akses ke poli ini",
    "errors": {
        "poli": ["Unauthorized access to poli"]
    }
}
```

**422 Validation Error**:
```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

**404 Not Found**:
```json
{
    "message": "Antrean tidak ditemukan",
    "data": null
}
```

## WebSocket Events (Real-time)

### Channels to Listen:
1. **Queue Updates**: `queue.{poli_id}` (e.g., `queue.1`)
   - Events: `queue.called`, `queue.skipped`, `queue.finished`, `queue.updated`
   - For dashboard and TV display

2. **Display Updates**: `display.{poli_id}` (e.g., `display.1`)
   - Events: `queue.called`, `queue.skipped`, `queue.finished`, `registration.created`
   - For TV display only

3. **Individual Queue**: `queue.{queue_id}.updates`
   - Events: All queue updates for specific queue
   - For individual patient tracking

### Sample Event Payload:
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

## Testing Tips

1. **Get Token First**: Always login first and copy the token for authenticated requests
2. **Test Different Roles**: Test with admin and petugas accounts to see different access levels
3. **Real-time Testing**: Open multiple browser tabs - one for dashboard, one for TV display
4. **Queue Flow**: Register → Call → Serve → Finish to test complete workflow
5. **Error Cases**: Try accessing cross-poli data with petugas account to test middleware

## Default Test Accounts

- **Admin**: admin@puskesmas-antang.com / password
- **Petugas**: petugas@puskesmas-antang.com / password

## Poli Codes

- **A**: Poli Umum
- **B**: Poli Gigi
- **C**: Poli KIA
- **D**: Poli Lansia
- **E**: Poli Gizi

Use these codes for display endpoints and testing.
