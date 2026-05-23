@echo off
cd /d "%~dp0"
echo Starting Submit Mate BD Backend...
php artisan optimize:clear
php artisan serve
pause
