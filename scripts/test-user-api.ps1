# User Management API Test Script
# Usage: .\scripts\test-user-api.ps1

Set-Location (Join-Path $PSScriptRoot "..")

Write-Host "=== User Management API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/7] Testing Admin Login..." -ForegroundColor Yellow
    $loginBody = @{
        email = "admin@school.local"
        password = "password"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $headers `
        -Body $loginBody

    $token = $loginResponse.data.token
    Write-Host "  ✓ Login successful (Token: $($token.Substring(0, 30))...)" -ForegroundColor Green

    $authHeaders = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    # Step 2: Get all users
    Write-Host "`n[2/7] Testing GET /api/admin/users..." -ForegroundColor Yellow
    $usersResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($usersResponse.data.Count) users" -ForegroundColor Green

    # Step 3: Create new user
    Write-Host "`n[3/7] Testing POST /api/admin/users (Create User)..." -ForegroundColor Yellow
    $newUserBody = @{
        name = "Test Student"
        email = "test.student@school.local"
        password = "password123"
        role = "student"
        student_number = "2026001"
        is_active = $true
    } | ConvertTo-Json

    $createResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users" `
        -Method POST `
        -Headers $authHeaders `
        -Body $newUserBody

    $newUserId = $createResponse.data.id
    Write-Host "  ✓ User created (ID: $newUserId, Name: $($createResponse.data.name))" -ForegroundColor Green

    # Step 4: Get specific user
    Write-Host "`n[4/7] Testing GET /api/admin/users/{id}..." -ForegroundColor Yellow
    $userResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users/$newUserId" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved user: $($userResponse.data.name)" -ForegroundColor Green

    # Step 5: Update user
    Write-Host "`n[5/7] Testing PUT /api/admin/users/{id} (Update User)..." -ForegroundColor Yellow
    $updateBody = @{
        name = "Updated Test Student"
        student_number = "2026002"
    } | ConvertTo-Json

    $updateResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users/$newUserId" `
        -Method PUT `
        -Headers $authHeaders `
        -Body $updateBody

    Write-Host "  ✓ User updated: $($updateResponse.data.name)" -ForegroundColor Green

    # Step 6: Toggle active status
    Write-Host "`n[6/7] Testing PATCH /api/admin/users/{id}/toggle-active..." -ForegroundColor Yellow
    $toggleResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users/$newUserId/toggle-active" `
        -Method PATCH `
        -Headers $authHeaders

    Write-Host "  ✓ Active status toggled: $($toggleResponse.data.is_active)" -ForegroundColor Green

    # Step 7: Delete user
    Write-Host "`n[7/7] Testing DELETE /api/admin/users/{id} (Soft Delete)..." -ForegroundColor Yellow
    $deleteResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users/$newUserId" `
        -Method DELETE `
        -Headers $authHeaders

    Write-Host "  ✓ User deleted successfully" -ForegroundColor Green

    Write-Host "`n=== All User Management API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
