# Assignment Management API Test Script
# Usage: .\scripts\test-assignment-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Assignment Management API Test ===" -ForegroundColor Cyan

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/15] Testing Admin Login..." -ForegroundColor Yellow
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

    # Step 2: Create assignment template
    Write-Host "`n[2/15] Testing POST /api/admin/assignment-templates (Create Template)..." -ForegroundColor Yellow
    $templateBody = @{
        name = "Test Template $(Get-Random -Minimum 100 -Maximum 999)"
        title = "Weekly Report Template"
        description = "Standard weekly progress report"
        grading_type = "points"
        max_score = 100
        submission_type = "both"
        allowed_file_types = @("pdf", "docx")
        max_file_size = 10240
        max_files = 3
    } | ConvertTo-Json

    $createTemplateResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignment-templates" `
        -Method POST `
        -Headers $authHeaders `
        -Body $templateBody

    $templateId = $createTemplateResponse.data.id
    Write-Host "  ✓ Template created (ID: $templateId)" -ForegroundColor Green

    # Step 3: Get all templates
    Write-Host "`n[3/15] Testing GET /api/admin/assignment-templates..." -ForegroundColor Yellow
    $templatesResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignment-templates" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($templatesResponse.data.Count) templates" -ForegroundColor Green

    # Step 4: Get active subject
    Write-Host "`n[4/15] Getting active subject..." -ForegroundColor Yellow
    $subjectsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects?is_active=1&per_page=1" `
        -Method GET `
        -Headers $authHeaders

    if ($subjectsResponse.data.Count -eq 0) {
        Write-Host "  ⚠ No active subjects found, getting any subject..." -ForegroundColor Yellow
        $subjectsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects?per_page=1" `
            -Method GET `
            -Headers $authHeaders
    }

    $subjectId = $subjectsResponse.data[0].id
    Write-Host "  ✓ Subject ID: $subjectId" -ForegroundColor Green

    # Step 5: Create assignment
    Write-Host "`n[5/15] Testing POST /api/admin/assignments (Create Assignment)..." -ForegroundColor Yellow
    $deadline = (Get-Date).AddDays(7).ToString("yyyy-MM-ddTHH:mm:ss")
    $assignmentBody = @{
        subject_id = $subjectId
        template_id = $templateId
        title = "Week 1 Progress Report"
        description = "Submit your first week progress report"
        deadline = $deadline
        is_graded = $true
        grading_type = "points"
        max_score = 100
        submission_type = "both"
        allowed_file_types = @("pdf", "docx")
        max_file_size = 10240
        max_files = 3
        is_active = $true
    } | ConvertTo-Json

    $createAssignmentResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments" `
        -Method POST `
        -Headers $authHeaders `
        -Body $assignmentBody

    $assignmentId = $createAssignmentResponse.data.id
    Write-Host "  ✓ Assignment created (ID: $assignmentId)" -ForegroundColor Green

    # Step 6: Get all assignments
    Write-Host "`n[6/15] Testing GET /api/admin/assignments..." -ForegroundColor Yellow
    $assignmentsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($assignmentsResponse.data.Count) assignments" -ForegroundColor Green

    # Step 7: Get specific assignment
    Write-Host "`n[7/15] Testing GET /api/admin/assignments/{id}..." -ForegroundColor Yellow
    $assignmentResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved assignment: $($assignmentResponse.data.title)" -ForegroundColor Green

    # Step 8: Update assignment
    Write-Host "`n[8/15] Testing PUT /api/admin/assignments/{id} (Update Assignment)..." -ForegroundColor Yellow
    $updateBody = @{
        title = "Week 1 Progress Report (Updated)"
        max_score = 120
    } | ConvertTo-Json

    $updateResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId" `
        -Method PUT `
        -Headers $authHeaders `
        -Body $updateBody

    Write-Host "  ✓ Assignment updated: $($updateResponse.data.title)" -ForegroundColor Green

    # Step 9: Publish assignment
    Write-Host "`n[9/15] Testing POST /api/admin/assignments/{id}/publish..." -ForegroundColor Yellow
    $publishResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId/publish" `
        -Method POST `
        -Headers $authHeaders

    Write-Host "  ✓ Assignment published" -ForegroundColor Green

    # Step 10: Bulk create submissions
    Write-Host "`n[10/15] Testing POST /api/admin/assignments/{id}/submissions/bulk-create..." -ForegroundColor Yellow
    $bulkCreateResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId/submissions/bulk-create" `
        -Method POST `
        -Headers $authHeaders

    Write-Host "  ✓ Created $($bulkCreateResponse.data.created_count) submission records" -ForegroundColor Green

    # Step 11: Get submissions
    Write-Host "`n[11/15] Testing GET /api/admin/submissions?assignment_id=$assignmentId..." -ForegroundColor Yellow
    $submissionsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/submissions?assignment_id=$assignmentId&with_student=1" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Retrieved $($submissionsResponse.data.Count) submissions" -ForegroundColor Green

    # Step 12: Login as student and submit
    Write-Host "`n[12/15] Testing Student Login and Submit..." -ForegroundColor Yellow
    $studentLoginBody = @{
        email = "student2@school.local"
        password = "password"
    } | ConvertTo-Json

    $studentLoginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $headers `
        -Body $studentLoginBody

    $studentToken = $studentLoginResponse.data.token
    $studentHeaders = @{
        "Authorization" = "Bearer $studentToken"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }
    Write-Host "  ✓ Student logged in" -ForegroundColor Green

    # Step 13: Submit assignment
    Write-Host "`n[13/15] Testing POST /api/assignments/{id}/submit..." -ForegroundColor Yellow
    $submitBody = @{
        student_comments = "Here is my progress report for week 1"
    } | ConvertTo-Json

    $submitResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/assignments/$assignmentId/submit" `
        -Method POST `
        -Headers $studentHeaders `
        -Body $submitBody

    $submissionId = $submitResponse.data.id
    Write-Host "  ✓ Assignment submitted (Submission ID: $submissionId)" -ForegroundColor Green

    # Step 14: Grade submission (as admin)
    Write-Host "`n[14/15] Testing POST /api/admin/submissions/{id}/grade..." -ForegroundColor Yellow
    $gradeBody = @{
        score = 85
        grade = "B+"
        teacher_comments = "Good work! Keep it up."
        request_resubmission = $false
    } | ConvertTo-Json

    $gradeResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/submissions/$submissionId/grade" `
        -Method POST `
        -Headers $authHeaders `
        -Body $gradeBody

    Write-Host "  ✓ Submission graded: $($gradeResponse.data.score) points" -ForegroundColor Green

    # Step 15: Get assignment statistics
    Write-Host "`n[15/15] Testing GET /api/admin/assignments/{id}/statistics..." -ForegroundColor Yellow
    $statisticsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId/statistics" `
        -Method GET `
        -Headers $authHeaders

    Write-Host "  ✓ Statistics: $($statisticsResponse.data.statistics.submitted) submitted, $($statisticsResponse.data.statistics.graded) graded" -ForegroundColor Green

    Write-Host "`n=== All Assignment Management API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
