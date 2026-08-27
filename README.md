# MapBiomas Indonesia Alerta - Controlsheet

`User role = [ 'admin' => 0, 'auditor' => 1, 'validator' => 2]`

### Technology stack

- Laravel
- Laravel-Livewire
- AlpineJS
- TailwindCss
- MySQL

### How to use

- clone this repository
- composer install & npm install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan storage:link
- create user using `php artisan tinker`

### Deployment performance

- after each deploy, warm the framework caches: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- keep a queue worker running (`php artisan queue:work --tries=1`) — CACHE_STORE and broadcasting use the database queue, so jobs stall without it
- make sure the `cache` and `sessions` tables exist in production (`php artisan migrate`) — dashboard caching degrades gracefully without the cache table, but should not be left that way
- if the alerts-test table has a different name in production, set `DB_TABLE_ALERTS_TEST=<table>` in the production `.env` (default: `alerts_backup_terbaru`) — `alerts` and `auditorlog` are used as-is everywhere

## Screenshot

|                   Light Mode                   |                   Dark Mode                   |
| :--------------------------------------------: | :-------------------------------------------: |
| ![Light Mode](https://i.imgur.com/86fG7g0.png) | ![Dark Mode](https://i.imgur.com/xKYXofG.png) |
