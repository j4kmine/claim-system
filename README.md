## Installation
```
composer install
npm install && npm run dev
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

## How to Test
- Make sure [SQLite](https://www.sqlite.org/download.html) is installed on local machine
```sudo apt-get install php-sqlite3``` (Linux)
- Run All test suite
```composer test``` or ```vendor/phpunit/phpunit/phpunit --testsuite Feature Tests```
- Run one test class
``` vendor/bin/phpunit tests/Feature/AuthenticationTest.php```
- VS Code extension for easily running test class [Better PHPUnit](https://marketplace.visualstudio.com/items?itemName=calebporzio.better-phpunit)