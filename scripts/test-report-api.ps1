# Report/Export API Test Script
# Usage: .\scripts\test-report-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Report/Export API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/5] Testing Admin Login..." -ForegroundColor Yellow
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
    }

    # Step 2: Get subject and assignment
    Write-Host "`n[2/5] Getting subject and assignment..." -ForegroundColor Yellow
    $subjectsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects?per_page=1" `
        -Method GET `
        -Headers $authHeaders

    $assignmentsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments?subject_id=$($subjectsResponse.data[0].id)&per_page=1" `
        -Method GET `
        -Headers $authHeaders

    $subjectId = $subjectsResponse.data[0].id
    $assignmentId = $assignmentsResponse.data[0].id
    Write-Host "  ✓ Subject ID: $subjectId, Assignment ID: $assignmentId" -ForegroundColor Green

    # Step 3: 提出状況CSV
    Write-Host "`n[3/5] Testing GET /api/reports/submissions/csv..." -ForegroundColor Yellow
    $csvPath = Join-Path $env:TEMP "report_submissions_$(Get-Random).csv"
    Invoke-WebRequest -Uri "http://localhost:8000/api/reports/submissions/csv?subject_id=$subjectId&assignment_id=$assignmentId" `
        -Method GET `
        -Headers $authHeaders `
        -OutFile $csvPath

    $csvContent = Get-Content $csvPath -Raw -Encoding UTF8
    $lineCount = ($csvContent -split "`n").Count
    Write-Host "  ✓ Submissions CSV downloaded ($lineCount lines)" -ForegroundColor Green
    Remove-Item $csvPath -Force -ErrorAction SilentlyContinue

    # Step 4: 成績一覧CSV
    Write-Host "`n[4/5] Testing GET /api/reports/grades/csv..." -ForegroundColor Yellow
    $gradesPath = Join-Path $env:TEMP "report_grades_$(Get-Random).csv"
    Invoke-WebRequest -Uri "http://localhost:8000/api/reports/grades/csv?subject_id=$subjectId" `
        -Method GET `
        -Headers $authHeaders `
        -OutFile $gradesPath

    $gradesContent = Get-Content $gradesPath -Raw -Encoding UTF8
    $gradesLineCount = ($gradesContent -split "`n").Count
    Write-Host "  ✓ Grades CSV downloaded ($gradesLineCount lines)" -ForegroundColor Green
    Remove-Item $gradesPath -Force -ErrorAction SilentlyContinue

    # Step 5: 未提出者リストCSV
    Write-Host "`n[5/5] Testing GET /api/reports/not-submitted/csv..." -ForegroundColor Yellow
    $notSubmittedPath = Join-Path $env:TEMP "report_not_submitted_$(Get-Random).csv"
    Invoke-WebRequest -Uri "http://localhost:8000/api/reports/not-submitted/csv?assignment_id=$assignmentId" `
        -Method GET `
        -Headers $authHeaders `
        -OutFile $notSubmittedPath

    $notSubmittedContent = Get-Content $notSubmittedPath -Raw -Encoding UTF8
    Write-Host "  ✓ Not-submitted CSV downloaded successfully" -ForegroundColor Green
    Remove-Item $notSubmittedPath -Force -ErrorAction SilentlyContinue

    Write-Host "`n=== All Report API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
