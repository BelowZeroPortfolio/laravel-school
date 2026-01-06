# Tech Stack

## Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL (SQLite for development)
- **Authentication**: Laravel built-in auth with session driver
- **Real-time**: Laravel Reverb (WebSockets)
- **PDF Generation**: barryvdh/laravel-dompdf
- **QR Codes**: simplesoftwareio/simple-qrcode

## Frontend
- **CSS**: Tailwind CSS 4.0
- **Build Tool**: Vite 7
- **JS**: Vanilla JS with Laravel Echo for WebSocket client
- **Views**: Blade templates with components

## Testing
- **Framework**: PHPUnit 11
- **Property Testing**: Eris (giorgiosironi/eris)
- **Factories**: Laravel model factories with Faker

## Common Commands

```bash
# Setup project
composer setup

# Run development servers (app, queue, logs, vite)
composer dev

# Run tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Property/StudentAttendanceServicePropertyTest.php

# Database migrations
php artisan migrate
php artisan migrate:fresh --seed

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Code formatting
./vendor/bin/pint
```

## Environment

Key `.env` variables:
- `DB_CONNECTION`: mysql (or sqlite for dev)
- `BROADCAST_CONNECTION`: reverb
- `QUEUE_CONNECTION`: database
- `SESSION_DRIVER`: database
