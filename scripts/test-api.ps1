# API Test Script
# Usage: .\scripts\test-api.ps1

Set-Location (Join-Path $PSScriptRoot "..")

Write-Host "=== Testing Login API ===" -ForegroundColor Cyan

$loginBody = @{
    email = "admin@school.local"
    password = "password"
} | ConvertTo-Json

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $headers `
        -Body $loginBody

    Write-Host "Login successful!" -ForegroundColor Green
    Write-Host "User: $($response.data.user.name)" -ForegroundColor White
    Write-Host "Role: $($response.data.user.role)" -ForegroundColor White
    Write-Host "Token: $($response.data.token.Substring(0, 50))..." -ForegroundColor White
    
    $token = $response.data.token
    
    Write-Host "`n=== Testing /me API ===" -ForegroundColor Cyan
    
    $authHeaders = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
    }
    
    $meResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/me" `
        -Method GET `
        -Headers $authHeaders
    
    Write-Host "User info retrieved!" -ForegroundColor Green
    Write-Host "Name: $($meResponse.data.name)" -ForegroundColor White
    Write-Host "Email: $($meResponse.data.email)" -ForegroundColor White
    
    Write-Host "`n=== Testing Logout API ===" -ForegroundColor Cyan
    
    $logoutResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/logout" `
        -Method POST `
        -Headers $authHeaders
    
    Write-Host "Logout successful!" -ForegroundColor Green
    Write-Host $logoutResponse.message -ForegroundColor White
    
    Write-Host "`n=== All tests passed! ===" -ForegroundColor Green
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
