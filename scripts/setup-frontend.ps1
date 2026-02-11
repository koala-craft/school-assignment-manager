# School Assignment Manager - Frontend Setup Script (PowerShell)

# Move to frontend directory
Set-Location (Join-Path $PSScriptRoot "..\frontend")

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Frontend Setup - School Assignment Manager" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

function Test-CommandSuccess {
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Error occurred. Exiting script." -ForegroundColor Red
        Read-Host "Press Enter to exit"
        exit 1
    }
}

Write-Host "[1/6] Creating Vue 3 project..." -ForegroundColor Yellow
if (Test-Path "package.json") {
    Write-Host "Vue project already exists - skipping creation" -ForegroundColor Yellow
} else {
    "y", "n", "n" | npm create vite@5 frontend-temp -- --template vue-ts
    Test-CommandSuccess
    Move-Item -Path "frontend-temp\*" -Destination "." -Force
    Move-Item -Path "frontend-temp\.*" -Destination "." -Force -ErrorAction SilentlyContinue
    Remove-Item "frontend-temp" -Force
}
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[2/6] Installing base dependencies..." -ForegroundColor Yellow
npm install
Test-CommandSuccess
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[3/6] Installing Vue Router and Pinia..." -ForegroundColor Yellow
npm install vue-router@4 pinia
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[4/6] Installing Axios..." -ForegroundColor Yellow
npm install axios
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[5/6] Installing Vuetify..." -ForegroundColor Yellow
npm install vuetify@next @mdi/font
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "[6/6] Installing additional packages..." -ForegroundColor Yellow
npm install vee-validate yup dayjs
npm install -D @vue/eslint-config-typescript eslint prettier eslint-plugin-vue @types/node
Write-Host "Done" -ForegroundColor Green
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Configure environment files" -ForegroundColor White
Write-Host "2. Set up Vuetify plugin" -ForegroundColor White
Write-Host "3. Configure Vue Router" -ForegroundColor White
Write-Host "4. Run: npm run dev" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
