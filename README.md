## Install AdminLTE in laravel 8

Step 1: Clone repository using `git clone git@github.com:ckpanchal/l8-user-api.git`

Step 2: Run `composer install` command to install all dependency packages

Step 3: Copy .env.example to .env using this command.
`cp .env.example .env`

Step 4: Setup database in `.env` file

Step 5: Run `php artisan migrate` to generate database tables from migrations

Step 6: Generate new key using this command
`php artisan key:generate`

Step 7: Generate JWT token
`php artisan jwt:secret`

Step 8: Finally start server
`php artisan serve`
