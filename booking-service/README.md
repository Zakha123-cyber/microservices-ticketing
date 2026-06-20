# Booking Service

Laravel service for bookings, tickets, and Midtrans sandbox payment flow.

## Run

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --port=8003
```
