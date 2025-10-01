# 🎯 Mini-Trello - Kanban Task Manager

Interactive Kanban board with Laravel 9 + Livewire 2, drag & drop, authentication, audit system and role management.

## Quick Start

### Development (Laravel Sail)

```bash
./start-dev.sh
```

Open http://localhost

To stop:

```bash
./stop-dev.sh
```

### Production (Docker)

```bash
./setup-production.sh
```

Open http://localhost

### Without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install && npm run build
php artisan serve
```

## 👤 Credentials

| Email               | Password   | Role                 |
| ------------------- | ---------- | -------------------- |
| `admin@example.com` | `password` | Admin (audit access) |

## ✨ Tech Stack

-   Laravel 9.52 + Livewire 2.12
-   SQLite + TailwindCSS + Vite
-   SortableJS (drag & drop)
-   Docker + Laravel Sail

## 🧪 Tests

```bash
php artisan test  # 151 tests ✅
```

## ⚙️ Features

✅ Complete authentication (Laravel Breeze)  
✅ CRUD for private user tasks  
✅ Kanban Board: Pending → In Progress → Completed  
✅ Drag & drop without reload  
✅ Complete audit system  
✅ Admin panel (`/admin/audits`)  
✅ Granular authorization with Policies  
✅ Robust testing (151 tests)

---

**Technical demo project** - Laravel 9 + Livewire 2
