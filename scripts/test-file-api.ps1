# File Management API Test Script
# Usage: .\scripts\test-file-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== File Management API Test ===" -ForegroundColor Cyan

$headers = @{
    "Accept" = "application/json"
}

try {
    # Step 1: Login as admin
    Write-Host "`n[1/8] Testing Admin Login..." -ForegroundColor Yellow
    $loginBody = @{
        email = "admin@school.local"
        password = "password"
    } | ConvertTo-Json

    $loginHeaders = @{
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }

    $loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $loginHeaders `
        -Body $loginBody

    $token = $loginResponse.data.token
    Write-Host "  ✓ Login successful" -ForegroundColor Green

    $authHeaders = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
    }

    # Step 2: Get active subject
    Write-Host "`n[2/8] Getting active subject..." -ForegroundColor Yellow
    $subjectsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects?is_active=1&per_page=1" `
        -Method GET `
        -Headers $authHeaders

    $subjectId = $subjectsResponse.data[0].id
    Write-Host "  ✓ Subject ID: $subjectId" -ForegroundColor Green

    # Step 3: Create assignment
    Write-Host "`n[3/8] Creating assignment..." -ForegroundColor Yellow
    $deadline = (Get-Date).AddDays(7).ToString("yyyy-MM-ddTHH:mm:ss")
    $assignmentBody = @{
        subject_id = $subjectId
        title = "File Upload Test Assignment"
        description = "Test file upload functionality"
        deadline = $deadline
        is_graded = $true
        grading_type = "points"
        max_score = 100
        submission_type = "file"
        is_active = $true
    } | ConvertTo-Json

    $assignmentHeaders = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    $createAssignmentResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments" `
        -Method POST `
        -Headers $assignmentHeaders `
        -Body $assignmentBody

    $assignmentId = $createAssignmentResponse.data.id
    Write-Host "  ✓ Assignment created (ID: $assignmentId)" -ForegroundColor Green

    # Step 4: Publish assignment
    Write-Host "`n[4/8] Publishing assignment..." -ForegroundColor Yellow
    $publishResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId/publish" `
        -Method POST `
        -Headers $authHeaders

    Write-Host "  ✓ Assignment published" -ForegroundColor Green

    # Step 5: Login as student
    Write-Host "`n[5/8] Testing Student Login..." -ForegroundColor Yellow
    $studentLoginBody = @{
        email = "student2@school.local"
        password = "password"
    } | ConvertTo-Json

    $studentLoginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
        -Method POST `
        -Headers $loginHeaders `
        -Body $studentLoginBody

    $studentToken = $studentLoginResponse.data.token
    $studentHeaders = @{
        "Authorization" = "Bearer $studentToken"
        "Accept" = "application/json"
    }
    Write-Host "  ✓ Student logged in" -ForegroundColor Green

    # Step 6: Submit assignment
    Write-Host "`n[6/8] Submitting assignment..." -ForegroundColor Yellow
    $submitBody = @{
        student_comments = "Submitting with file"
    } | ConvertTo-Json

    $submitHeaders = @{
        "Authorization" = "Bearer $studentToken"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }

    $submitResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/assignments/$assignmentId/submit" `
        -Method POST `
        -Headers $submitHeaders `
        -Body $submitBody

    $submissionId = $submitResponse.data.id
    Write-Host "  ✓ Assignment submitted (Submission ID: $submissionId)" -ForegroundColor Green

    # Step 7: Create test file
    Write-Host "`n[7/8] Creating and uploading test file..." -ForegroundColor Yellow
    $testFilePath = Join-Path $env:TEMP "test-upload-$(Get-Random).txt"
    "This is a test file for file upload API" | Out-File -FilePath $testFilePath -Encoding UTF8
    
    $fileContent = [System.IO.File]::ReadAllBytes($testFilePath)
    $boundary = [System.Guid]::NewGuid().ToString()
    $LF = "`r`n"
    
    $bodyLines = (
        "--$boundary",
        "Content-Disposition: form-data; name=`"submission_id`"$LF",
        $submissionId,
        "--$boundary",
        "Content-Disposition: form-data; name=`"file`"; filename=`"test-report.txt`"",
        "Content-Type: text/plain$LF",
        [System.Text.Encoding]::UTF8.GetString($fileContent),
        "--$boundary--$LF"
    ) -join $LF

    $uploadHeaders = @{
        "Authorization" = "Bearer $studentToken"
        "Accept" = "application/json"
        "Content-Type" = "multipart/form-data; boundary=$boundary"
    }

    $uploadResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/files/upload" `
        -Method POST `
        -Headers $uploadHeaders `
        -Body $bodyLines

    $fileId = $uploadResponse.data.id
    Write-Host "  ✓ File uploaded (File ID: $fileId)" -ForegroundColor Green
    
    # Cleanup test file
    Remove-Item -Path $testFilePath -Force

    # Step 8: Get file list
    Write-Host "`n[8/8] Getting file list..." -ForegroundColor Yellow
    $filesResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/files?submission_id=$submissionId" `
        -Method GET `
        -Headers $studentHeaders

    Write-Host "  ✓ Retrieved $($filesResponse.data.Count) files" -ForegroundColor Green

    Write-Host "`n=== All File Management API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
