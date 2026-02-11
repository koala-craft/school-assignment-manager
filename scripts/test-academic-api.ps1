# Academic Year & Term Management API Test Script
# Usage: .\scripts\test-academic-api.ps1

Set-Location (Join-Path $PSScriptRoot "..")

Write-Host "=== Academic Year & Term Management API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/9] Testing Admin Login..." -ForegroundColor Yellow
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

    # Step 2: Get all academic years
    Write-Host "`n[2/9] Testing GET /api/admin/academic-years..." -ForegroundColor Yellow
    $yearsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($yearsResponse.data.Count) academic years" -ForegroundColor Green

    # Step 3: Create new academic year
    Write-Host "`n[3/9] Testing POST /api/admin/academic-years (Create Year)..." -ForegroundColor Yellow
    $newYearBody = @{
        year = 2028
        name = "Academic Year 2028"
        start_date = "2028-04-01"
        end_date = "2029-03-31"
        is_active = $false
    } | ConvertTo-Json

    $createYearResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years" `
        -Method POST `
        -Headers $authHeaders `
        -Body $newYearBody

    $newYearId = $createYearResponse.data.id
    Write-Host "  ✓ Academic year created (ID: $newYearId, Year: $($createYearResponse.data.year))" -ForegroundColor Green

    # Step 4: Get specific academic year
    Write-Host "`n[4/9] Testing GET /api/admin/academic-years/{id}..." -ForegroundColor Yellow
    $yearResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years/$newYearId" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved academic year: $($yearResponse.data.name)" -ForegroundColor Green

    # Step 5: Create terms for the academic year
    Write-Host "`n[5/9] Testing POST /api/admin/terms (Create Term 1)..." -ForegroundColor Yellow
    $term1Body = @{
        academic_year_id = $newYearId
        name = "First Term 2028"
        start_date = "2028-04-01"
        end_date = "2028-09-30"
    } | ConvertTo-Json

    $createTerm1Response = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/terms" `
        -Method POST `
        -Headers $authHeaders `
        -Body $term1Body

    $term1Id = $createTerm1Response.data.id
    Write-Host "  ✓ Term 1 created (ID: $term1Id, Name: $($createTerm1Response.data.name))" -ForegroundColor Green

    Write-Host "`n[6/9] Testing POST /api/admin/terms (Create Term 2)..." -ForegroundColor Yellow
    $term2Body = @{
        academic_year_id = $newYearId
        name = "Second Term 2028"
        start_date = "2028-10-01"
        end_date = "2029-03-31"
    } | ConvertTo-Json

    $createTerm2Response = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/terms" `
        -Method POST `
        -Headers $authHeaders `
        -Body $term2Body

    $term2Id = $createTerm2Response.data.id
    Write-Host "  ✓ Term 2 created (ID: $term2Id, Name: $($createTerm2Response.data.name))" -ForegroundColor Green

    # Step 7: Get all terms
    Write-Host "`n[7/9] Testing GET /api/admin/terms?academic_year_id=$newYearId..." -ForegroundColor Yellow
    $termsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/terms?academic_year_id=$newYearId" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($termsResponse.data.Count) terms for academic year" -ForegroundColor Green

    # Step 8: Update academic year
    Write-Host "`n[8/9] Testing PUT /api/admin/academic-years/{id} (Update Year)..." -ForegroundColor Yellow
    $updateYearBody = @{
        name = "2027年度 (Updated)"
    } | ConvertTo-Json

    $updateYearResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years/$newYearId" `
        -Method PUT `
        -Headers $authHeaders `
        -Body $updateYearBody

    Write-Host "  ✓ Academic year updated: $($updateYearResponse.data.name)" -ForegroundColor Green

    # Step 9: Delete term
    Write-Host "`n[9/9] Testing DELETE /api/admin/terms/{id}..." -ForegroundColor Yellow
    $deleteTerm1Response = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/terms/$term1Id" `
        -Method DELETE `
        -Headers $authHeaders

    Write-Host "  ✓ Term deleted successfully" -ForegroundColor Green

    Write-Host "`n=== All Academic Year & Term API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
