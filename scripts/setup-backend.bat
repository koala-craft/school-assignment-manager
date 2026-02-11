@echo off
cd /d %~dp0\..

echo ========================================
echo Backend Setup - School Assignment Manager
echo ========================================
echo.

echo [1/8] Building Docker image...
docker compose build app
if %errorlevel% neq 0 (
    echo ERROR: Failed to build Docker image
    pause
    exit /b 1
)
echo.

echo [2/8] Starting Docker containers...
docker compose up -d
if %errorlevel% neq 0 (
    echo ERROR: Failed to start Docker containers
    pause
    exit /b 1
)
echo Waiting for startup...
timeout /t 10 /nobreak > nul
echo.

echo [3/8] Installing Laravel Sanctum...
docker compose exec app composer require laravel/sanctum
if %errorlevel% neq 0 (
    echo ERROR: Failed to install Sanctum
    pause
    exit /b 1
)
echo.

echo [4/8] Publishing Sanctum config...
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
echo.

echo [5/8] Running database migrations...
docker compose exec app php artisan migrate --force
if %errorlevel% neq 0 (
    echo WARNING: Some migrations already exist - continuing...
)
echo.

echo [6/8] Installing Laravel Pint...
docker compose exec app composer require laravel/pint --dev
echo.

echo [7/8] Installing Laravel Telescope...
docker compose exec app composer require laravel/telescope --dev
docker compose exec app php artisan telescope:install
docker compose exec app php artisan migrate --force
if %errorlevel% neq 0 (
    echo WARNING: Some migrations already exist - continuing...
)
echo.

echo [8/8] Installing PHPStan...
docker compose exec app composer require phpstan/phpstan --dev
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Access URLs:
echo - Backend API: http://localhost:8000
echo - Mailpit: http://localhost:8025
echo - Telescope: http://localhost:8000/telescope
echo.
pause
