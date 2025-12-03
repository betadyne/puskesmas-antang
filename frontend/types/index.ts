export interface User {
  id: number
  name: string
  email: string
  roles: string[]
  poli?: UserPoli
  permissions?: string[]
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface UserPoli {
  id: number
  kode_poli: string
  nama_poli: string
  slug: string
}

export interface Poli {
  id: number
  name?: string
  nama_poli?: string
  code?: string
  kode_poli?: string
  slug?: string
  description?: string
  is_active?: boolean
  created_at?: string
  updated_at?: string
}

export interface Pasien {
  id: number
  nik: string
  name: string
  alamat?: string
  tanggal_lahir?: string
  jenis_kelamin?: 'L' | 'P'
  no_hp?: string
  created_at: string
  updated_at: string
}

export interface Queue {
  id: number
  nomor_antrean?: string
  nomor_antrian?: string
  registration_id?: number
  poli_id: number
  petugas_id?: number
  status: QueueStatus
  called_at?: string
  served_at?: string
  finished_at?: string
  patient?: Patient
  pasien?: Pasien
  poli?: Poli
  petugas?: User
  wait_time?: number
  service_time?: number
  created_at: string
  updated_at: string
}

export interface Patient {
  id: number
  nik: string
  nama: string
  no_bpjs?: string
  tgl_lahir?: string
  jenis_kelamin?: 'L' | 'P'
  no_hp?: string
  alamat?: string
  created_at?: string
}

export type QueueStatus = 'menunggu' | 'dipanggil' | 'dilayani' | 'selesai' | 'dilewati'

export interface QueueStats {
  total_waiting: number
  total_served: number
  total_skipped: number
  current_number?: string
  average_service_time?: number
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterQueuePayload {
  nik: string
  name: string
  poli_id: number
  alamat?: string
}

export interface QueueTicket {
  nomor_antrian: string
  poli_name: string
  estimasi_waktu?: number
  created_at: string
}

export interface WebsocketQueueEvent {
  queue: Queue
  action: 'created' | 'called' | 'updated' | 'finished'
}
