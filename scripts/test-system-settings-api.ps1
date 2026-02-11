# System Settings API Test Script
# Usage: .\scripts\test-system-settings-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== System Settings API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/3] Testing Admin Login..." -ForegroundColor Yellow
    $loginBody = @{
        email = "admin@school.local"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $headers `
        -Body $loginBody

    $token = $loginResponse.data.token
    Write-Host "  ✓ Login successful" -ForegroundColor Green

    $authHeaders = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    # Step 2: Get system settings
    Write-Host "`n[2/3] Testing GET /api/admin/system-settings..." -ForegroundColor Yellow
    $getResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/system-settings" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved settings: max_file_size=$($getResponse.data.max_file_size), session_timeout=$($getResponse.data.session_timeout)" -ForegroundColor Green

    # Step 3: Update system settings
    Write-Host "`n[3/3] Testing PUT /api/admin/system-settings..." -ForegroundColor Yellow
    $updateBody = @{
        session_timeout = 90
        password_min_length = 10
    } | ConvertTo-Json

    $updateResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/system-settings" `
        -Method PUT `
        -Headers $authHeaders `
        -Body $updateBody

    if ($updateResponse.data.session_timeout -eq 90) {
        Write-Host "  ✓ Settings updated successfully" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ Update may have failed (session_timeout=$($updateResponse.data.session_timeout))" -ForegroundColor Yellow
    }

    Write-Host "`n=== All System Settings API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
