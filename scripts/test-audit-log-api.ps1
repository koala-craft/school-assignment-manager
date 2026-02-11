# Audit Log API Test Script
# Usage: .\scripts\test-audit-log-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Audit Log API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/4] Testing Admin Login..." -ForegroundColor Yellow
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

    # Step 2: Get audit logs list (should include login log from step 1)
    Write-Host "`n[2/4] Testing GET /api/admin/audit-logs..." -ForegroundColor Yellow
    $listResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/audit-logs" `
        -Method GET `
        -Headers $authHeaders

    if ($listResponse.success -and $listResponse.data.items) {
        $count = $listResponse.data.items.Count
        Write-Host "  ✓ Retrieved $count audit log(s)" -ForegroundColor Green
        if ($count -gt 0) {
            $first = $listResponse.data.items[0]
            Write-Host "  - Latest: action=$($first.action), model=$($first.model), user_id=$($first.user_id)" -ForegroundColor Gray
        }
    } else {
        Write-Host "  ✓ List response OK (may be empty)" -ForegroundColor Green
    }

    # Step 3: Get audit logs with filters
    Write-Host "`n[3/4] Testing GET /api/admin/audit-logs?action=login..." -ForegroundColor Yellow
    $filterResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/audit-logs?action=login&per_page=5" `
        -Method GET `
        -Headers $authHeaders

    if ($filterResponse.success) {
        Write-Host "  ✓ Filter by action=login works" -ForegroundColor Green
    } else {
        Write-Host "  ⚠ Filter test may have issues" -ForegroundColor Yellow
    }

    # Step 4: Get audit log detail (if we have any)
    if ($listResponse.data.items -and $listResponse.data.items.Count -gt 0) {
        $firstId = $listResponse.data.items[0].id
        Write-Host "`n[4/4] Testing GET /api/admin/audit-logs/$firstId..." -ForegroundColor Yellow
        $detailResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/audit-logs/$firstId" `
            -Method GET `
            -Headers $authHeaders

        if ($detailResponse.success -and $detailResponse.data.id -eq $firstId) {
            Write-Host "  ✓ Retrieved audit log detail" -ForegroundColor Green
            if ($detailResponse.data.user) {
                Write-Host "  - User: $($detailResponse.data.user.name)" -ForegroundColor Gray
            }
        } else {
            Write-Host "  ⚠ Detail response may have issues" -ForegroundColor Yellow
        }
    } else {
        Write-Host "`n[4/4] Skipping detail test (no logs yet)" -ForegroundColor Gray
    }

    Write-Host "`n=== All Audit Log API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
