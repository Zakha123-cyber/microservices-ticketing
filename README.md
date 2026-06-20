# Event Ticketing Microservices

Monorepo for a Software Construction final project using microservices architecture.

## Services

- `frontend-web` - Laravel Blade frontend on port 3000
- `api-gateway` - Express.js API Gateway on port 8000
- `auth-service` - Express.js Auth Service on port 8001
- `event-service` - Express.js Event Service on port 8002
- `booking-service` - Laravel Booking Service on port 8003

## Local Run Order

1. Create MySQL databases: `auth_db`, `event_db`, `booking_db`
2. Copy each `.env.example` to `.env` and adjust credentials
3. Run Auth Service: `cd auth-service && npm run dev`
4. Run Event Service: `cd event-service && npm run dev`
5. Run Booking Service: `cd booking-service && php artisan serve --port=8003`
6. Run API Gateway: `cd api-gateway && npm run dev`
7. Run Frontend: `cd frontend-web && php artisan serve --port=3000`

## Documentation

See `../DETAILED_IMPLEMENTATION.md` for the complete implementation plan.
