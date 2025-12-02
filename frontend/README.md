# Puskesmas Antang - Frontend

Sistem Informasi Antrian Pasien Puskesmas Antang - Frontend Application

## Tech Stack

- **Framework:** Nuxt 3
- **Styling:** TailwindCSS + Nuxt UI
- **State Management:** Pinia
- **Real-time:** Laravel Echo + Pusher

## Setup

```bash
# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start development server
npm run dev
```

## Environment Variables

```env
NUXT_PUBLIC_API_BASE=http://localhost:8000/api
NUXT_PUBLIC_PUSHER_KEY=your_pusher_key
NUXT_PUBLIC_PUSHER_CLUSTER=mt1
```

## Project Structure

```
frontend/
├── assets/css/        # Global styles
├── components/        # Vue components
├── composables/       # Composable functions
├── layouts/           # Page layouts
├── middleware/        # Route middleware
├── pages/             # Application pages
├── plugins/           # Nuxt plugins
├── public/            # Static assets
├── stores/            # Pinia stores
├── types/             # TypeScript types
└── nuxt.config.ts     # Nuxt configuration
```

## Pages

### Public
- `/` - Landing page
- `/register` - Queue registration
- `/status` - Check queue status

### Display (TV Monitor)
- `/display` - Poli selection
- `/display/[poli_id]` - Queue display for specific poli

### Dashboard (Staff)
- `/login` - Staff login
- `/dashboard` - Queue management
- `/dashboard/reports` - Reports & statistics

### Admin
- `/admin` - Admin dashboard
- `/admin/users` - User management
- `/admin/poli` - Poli management
- `/admin/patients` - Patient data

## Build

```bash
# Build for production
npm run build

# Preview production build
npm run preview
```
