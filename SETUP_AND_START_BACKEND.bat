@echo off
cd /d "%~dp0"
echo ============================================
echo Submit Mate BD Backend Setup + Start
echo ============================================
echo.
echo Make sure XAMPP MySQL is running.
echo.

IF EXIST C:\xampp\mysql\bin\mysql.exe (
    echo Creating database submit_mate_db if not exists...
    C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS submit_mate_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) ELSE (
    echo XAMPP mysql.exe not found at C:\xampp\mysql\bin\mysql.exe
    echo If migrate fails, create database submit_mate_db manually in phpMyAdmin.
)

echo.
echo Installing Composer dependencies if vendor folder is missing...
IF NOT EXIST vendor (
    composer install
) ELSE (
    echo vendor folder already exists. Skipping composer install.
)

echo.
echo Clearing cache...
php artisan optimize:clear

echo.
echo Generating app key if needed...
php artisan key:generate --force

echo.
echo Migrating and seeding database...
php artisan migrate:fresh --seed

echo.
echo Creating storage link...
php artisan storage:link

echo.
echo Backend is starting at http://127.0.0.1:8000
echo Health check: http://127.0.0.1:8000/api/health
echo.
php artisan serve
pause
