# Backup API Test Script
# Usage: .\scripts\test-backup-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Backup API Test ===" -ForegroundColor Cyan

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

    # Step 2: Get backups list (before create)
    Write-Host "`n[2/3] Testing GET /api/admin/system/backups..." -ForegroundColor Yellow
    $listResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/system/backups" `
        -Method GET `
        -Headers $authHeaders

    if ($listResponse.success -and $null -ne $listResponse.data) {
        $beforeCount = $listResponse.data.Count
        Write-Host "  ✓ Retrieved $beforeCount backup(s)" -ForegroundColor Green
    } else {
        Write-Host "  ✓ List response OK" -ForegroundColor Green
    }

    # Step 3: Create backup
    Write-Host "`n[3/3] Testing POST /api/admin/system/backup..." -ForegroundColor Yellow
    $createResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/system/backup" `
        -Method POST `
        -Headers $authHeaders

    if ($createResponse.success -and $createResponse.data) {
        Write-Host "  ✓ Backup created successfully" -ForegroundColor Green
        Write-Host "  - File: $($createResponse.data.backup_file)" -ForegroundColor Gray
        Write-Host "  - Size: $($createResponse.data.size) bytes" -ForegroundColor Gray
    } else {
        throw "Backup creation failed"
    }

    # Verify list includes new backup
    $listResponse2 = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/system/backups" `
        -Method GET `
        -Headers $authHeaders
    if ($listResponse2.success -and $listResponse2.data.Count -gt 0) {
        $latest = $listResponse2.data[0]
        Write-Host "  ✓ List includes new backup: $($latest.filename)" -ForegroundColor Green
    }

    Write-Host "`n=== All Backup API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
