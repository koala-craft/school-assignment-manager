# Dashboard API Test Script
# Usage: .\scripts\test-dashboard-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Dashboard API Test ===" -ForegroundColor Cyan

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

    # Step 2: Admin dashboard
    Write-Host "`n[2/4] Testing GET /api/dashboard/admin..." -ForegroundColor Yellow
    $adminResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/admin" `
        -Method GET `
        -Headers $authHeaders

    if ($adminResponse.success -and $adminResponse.data) {
        Write-Host "  ✓ Admin dashboard: users=$($adminResponse.data.total_users), subjects=$($adminResponse.data.total_subjects), assignments=$($adminResponse.data.total_assignments)" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Admin dashboard failed" -ForegroundColor Red
        exit 1
    }

    # Step 3: Teacher dashboard (login as teacher)
    Write-Host "`n[3/4] Testing GET /api/dashboard/teacher..." -ForegroundColor Yellow
    $teacherLoginBody = @{
        email = "yamada@school.local"
        password = "password"
    } | ConvertTo-Json

    $teacherLoginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $headers `
        -Body $teacherLoginBody

    $teacherToken = $teacherLoginResponse.data.token
    $teacherAuthHeaders = @{
        "Authorization" = "Bearer $teacherToken"
        "Accept" = "application/json"
    }

    $teacherResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/teacher" `
        -Method GET `
        -Headers $teacherAuthHeaders

    if ($teacherResponse.success -and $teacherResponse.data) {
        Write-Host "  ✓ Teacher dashboard: subjects=$($teacherResponse.data.my_subjects)" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Teacher dashboard failed" -ForegroundColor Red
        exit 1
    }

    # Step 4: Student dashboard - login as student
    Write-Host "`n[4/4] Testing GET /api/dashboard/student..." -ForegroundColor Yellow
    $studentLoginBody = @{
        email = "suzuki@school.local"
        password = "password"
    } | ConvertTo-Json

    $studentLoginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $headers `
        -Body $studentLoginBody

    $studentToken = $studentLoginResponse.data.token
    $studentAuthHeaders = @{
        "Authorization" = "Bearer $studentToken"
        "Accept" = "application/json"
    }

    $studentResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/dashboard/student" `
        -Method GET `
        -Headers $studentAuthHeaders

    if ($studentResponse.success -and $studentResponse.data) {
        Write-Host "  ✓ Student dashboard: enrolled=$($studentResponse.data.enrolled_subjects), assignments=$($studentResponse.data.total_assignments)" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Student dashboard failed" -ForegroundColor Red
        exit 1
    }

    Write-Host "`n=== All Dashboard API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
