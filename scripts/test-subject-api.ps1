# Subject Management API Test Script
# Usage: .\scripts\test-subject-api.ps1

Set-Location (Join-Path $PSScriptRoot "..")

Write-Host "=== Subject Management API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/10] Testing Admin Login..." -ForegroundColor Yellow
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

    # Step 2: Get all subjects
    Write-Host "`n[2/10] Testing GET /api/admin/subjects..." -ForegroundColor Yellow
    $subjectsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($subjectsResponse.data.Count) subjects" -ForegroundColor Green

    # Step 3: Get active academic year
    Write-Host "`n[3/10] Getting active academic year..." -ForegroundColor Yellow
    $activeYearResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years?is_active=1&with_terms=1" `
        -Method GET `
        -Headers $authHeaders

    if ($activeYearResponse.data.Count -eq 0) {
        # Get first academic year if no active one
        $allYearsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/academic-years?with_terms=1&per_page=1" `
            -Method GET `
            -Headers $authHeaders
        $academicYearId = $allYearsResponse.data[0].id
        $termId = $allYearsResponse.data[0].terms[0].id
    } else {
        $academicYearId = $activeYearResponse.data[0].id
        $termId = $activeYearResponse.data[0].terms[0].id
    }
    Write-Host "  ✓ Academic Year ID: $academicYearId, Term ID: $termId" -ForegroundColor Green

    # Step 4: Create new subject
    Write-Host "`n[4/10] Testing POST /api/admin/subjects (Create Subject)..." -ForegroundColor Yellow
    $randomCode = "TEST$(Get-Random -Minimum 100 -Maximum 999)"
    $newSubjectBody = @{
        code = $randomCode
        name = "Web Development Test"
        academic_year_id = $academicYearId
        term_id = $termId
        description = "Learn modern web development with Laravel and Vue.js"
        is_active = $true
    } | ConvertTo-Json

    $createSubjectResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects" `
        -Method POST `
        -Headers $authHeaders `
        -Body $newSubjectBody

    $newSubjectId = $createSubjectResponse.data.id
    Write-Host "  ✓ Subject created (ID: $newSubjectId, Code: $($createSubjectResponse.data.code))" -ForegroundColor Green

    # Step 5: Get specific subject
    Write-Host "`n[5/10] Testing GET /api/admin/subjects/{id}..." -ForegroundColor Yellow
    $subjectResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects/$newSubjectId" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved subject: $($subjectResponse.data.name)" -ForegroundColor Green

    # Step 6: Get teachers
    Write-Host "`n[6/10] Getting teacher IDs..." -ForegroundColor Yellow
    $teachersResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users?role=teacher" `
        -Method GET `
        -Headers $authHeaders

    $teacherId1 = $teachersResponse.data[0].id
    $teacherId2 = $teachersResponse.data[1].id
    Write-Host "  ✓ Teacher IDs: $teacherId1, $teacherId2" -ForegroundColor Green

    # Step 7: Assign teachers to subject
    Write-Host "`n[7/10] Testing POST /api/admin/subjects/{id}/assign-teachers..." -ForegroundColor Yellow
    $assignTeachersBody = @{
        teacher_ids = @($teacherId1, $teacherId2)
    } | ConvertTo-Json

    $assignResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects/$newSubjectId/assign-teachers" `
        -Method POST `
        -Headers $authHeaders `
        -Body $assignTeachersBody

    Write-Host "  ✓ Teachers assigned successfully" -ForegroundColor Green

    # Step 8: Get students
    Write-Host "`n[8/10] Getting student IDs..." -ForegroundColor Yellow
    $studentsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/users?role=student&is_active=1&per_page=3" `
        -Method GET `
        -Headers $authHeaders

    $studentIds = $studentsResponse.data | Where-Object { $_.deleted_at -eq $null } | ForEach-Object { $_.id } | Select-Object -First 3
    Write-Host "  ✓ Student IDs: $($studentIds -join ', ')" -ForegroundColor Green

    # Step 9: Enroll students in subject
    Write-Host "`n[9/10] Testing POST /api/admin/subjects/{id}/enroll..." -ForegroundColor Yellow
    $enrollBody = @{
        student_ids = $studentIds
    } | ConvertTo-Json

    $enrollResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects/$newSubjectId/enroll" `
        -Method POST `
        -Headers $authHeaders `
        -Body $enrollBody

    Write-Host "  ✓ Students enrolled: $($enrollResponse.data.enrolled_count)" -ForegroundColor Green

    # Step 10: Get subject students
    Write-Host "`n[10/10] Testing GET /api/admin/subjects/{id}/students..." -ForegroundColor Yellow
    $subjectStudentsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects/$newSubjectId/students" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved students for subject" -ForegroundColor Green

    Write-Host "`n=== All Subject Management API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
