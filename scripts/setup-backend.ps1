# School Assignment Manager - Backend Setup Script (PowerShell)

# Move to project root
Set-Location (Join-Path $PSScriptRoot "..")

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "School Assignment Manager - Backend Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

function Test-CommandSuccess {
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Error occurred. Exiting script." -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
}

Write-Host "[1/8] Building Docker image..." -ForegroundColor Yellow
docker compose build app
Test-CommandSuccess
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[2/8] Starting Docker containers..." -ForegroundColor Yellow
docker compose up -d
Test-CommandSuccess
Write-Host "Waiting for startup..."
Start-Sleep -Seconds 10
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[3/8] Installing Laravel Sanctum..." -ForegroundColor Yellow
docker compose exec app composer require laravel/sanctum
Test-CommandSuccess
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[4/8] Publishing Sanctum config..." -ForegroundColor Yellow
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[5/8] Running database migrations..." -ForegroundColor Yellow
docker compose exec app php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "WARNING: Some migrations already exist - continuing..." -ForegroundColor Yellow
}
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[6/8] Installing Laravel Pint..." -ForegroundColor Yellow
docker compose exec app composer require laravel/pint --dev
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[7/8] Installing Laravel Telescope..." -ForegroundColor Yellow
docker compose exec app composer require laravel/telescope --dev
docker compose exec app php artisan telescope:install
docker compose exec app php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "WARNING: Some migrations already exist - continuing..." -ForegroundColor Yellow
}
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[8/8] Installing PHPStan..." -ForegroundColor Yellow
docker compose exec app composer require phpstan/phpstan --dev
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Access the application at:"
Write-Host "- Backend API: http://localhost:8000" -ForegroundColor White
Write-Host "- Mailpit: http://localhost:8025" -ForegroundColor White
Write-Host "- Telescope: http://localhost:8000/telescope" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
