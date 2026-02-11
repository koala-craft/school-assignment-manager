# Student Grades PDF API Test Script
# Usage: .\scripts\test-student-grades-pdf.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Student Grades PDF API Test ===" -ForegroundColor Cyan

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
        "Accept" = "application/pdf"
    }

    # Step 2: Get academic year ID (first active year)
    Write-Host "`n[2/3] Fetching academic year..." -ForegroundColor Yellow
    $authHeadersJson = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
    }
    $yearsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years/current/active" `
        -Method GET `
        -Headers $authHeadersJson `
        -ErrorAction SilentlyContinue

    $academicYearId = 1
    if ($yearsResponse -and $yearsResponse.data) {
        $academicYearId = $yearsResponse.data.id
    } elseif ($yearsResponse -and $yearsResponse.data -is [array]) {
        $academicYearId = $yearsResponse.data[0].id
    }

    # Step 3: Download PDF (student_id=5 is typical from seeder, or use first student)
    Write-Host "`n[3/3] Testing GET /api/reports/student-grades/pdf..." -ForegroundColor Yellow
    $pdfUrl = "http://localhost:8000/api/reports/student-grades/pdf?student_id=5&academic_year_id=$academicYearId"
    $pdfPath = Join-Path $env:TEMP "student_grades_test_$(Get-Date -Format 'yyyyMMddHHmmss').pdf"

    Invoke-WebRequest -Uri $pdfUrl `
        -Method GET `
        -Headers @{ "Authorization" = "Bearer $token" } `
        -OutFile $pdfPath `
        -UseBasicParsing

    if (Test-Path $pdfPath) {
        $size = (Get-Item $pdfPath).Length
        Write-Host "  ✓ PDF downloaded: $pdfPath ($size bytes)" -ForegroundColor Green
        if ($size -gt 100) {
            Write-Host "  ✓ PDF appears valid (size > 100 bytes)" -ForegroundColor Green
        }
        Remove-Item $pdfPath -Force -ErrorAction SilentlyContinue
    } else {
        Write-Host "  ✗ PDF download failed" -ForegroundColor Red
        exit 1
    }

    Write-Host "`n=== All Student Grades PDF API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
