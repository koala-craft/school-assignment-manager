# Notification Management API Test Script
# Usage: .\scripts\test-notification-api.ps1

Set-Location (Join-Path $PSScriptRoot "..\")

Write-Host "=== Notification Management API Test ===" -ForegroundColor Cyan

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

    # Step 2: Get subject with enrolled students
    Write-Host "`n[2/10] Getting subject with students..." -ForegroundColor Yellow
    $subjectsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/subjects?with_students=1&per_page=1" `
        -Method GET `
        -Headers $authHeaders

    $subjectId = $subjectsResponse.data[0].id
    Write-Host "  ✓ Subject ID: $subjectId" -ForegroundColor Green

    # Step 3: Create and publish assignment (triggers notifications)
    Write-Host "`n[3/10] Creating and publishing assignment..." -ForegroundColor Yellow
    $deadline = (Get-Date).AddDays(7).ToString("yyyy-MM-ddTHH:mm:ss")
    $assignmentBody = @{
        subject_id = $subjectId
        title = "Notification Test Assignment"
        description = "Test notification on publish"
        deadline = $deadline
        is_graded = $true
        grading_type = "points"
        max_score = 100
        submission_type = "text"
        is_active = $true
    } | ConvertTo-Json

    $createResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments" `
        -Method POST `
        -Headers $authHeaders `
        -Body $assignmentBody

    $assignmentId = $createResponse.data.id

    $publishResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/admin/assignments/$assignmentId/publish" `
        -Method POST `
        -Headers $authHeaders

    Write-Host "  ✓ Assignment published (triggers notifications)" -ForegroundColor Green

    # Step 4: Login as student
    Write-Host "`n[4/10] Testing Student Login..." -ForegroundColor Yellow
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

    # Step 5: Get notifications
    Write-Host "`n[5/10] Testing GET /api/notifications..." -ForegroundColor Yellow
    $notificationsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/notifications" `
        -Method GET `
        -Headers $studentHeaders

    $itemCount = $notificationsResponse.data.items.Count
    if ($null -eq $itemCount) { $itemCount = @($notificationsResponse.data.items).Count }
    Write-Host "  ✓ Retrieved $itemCount notifications (unread: $($notificationsResponse.data.unread_count))" -ForegroundColor Green

    # Step 6: Get unread only
    Write-Host "`n[6/10] Testing GET /api/notifications?unread_only=true..." -ForegroundColor Yellow
    $unreadResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/notifications?unread_only=true" `
        -Method GET `
        -Headers $studentHeaders

    Write-Host "  ✓ Unread notifications: $($unreadResponse.data.unread_count)" -ForegroundColor Green

    # Step 7: Mark single notification as read
    if ($notificationsResponse.data.unread_count -gt 0) {
        Write-Host "`n[7/10] Testing PUT /api/notifications/{id}/read..." -ForegroundColor Yellow
        $firstNotificationId = $notificationsResponse.data.items[0].id
        $markReadResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/notifications/$firstNotificationId/read" `
            -Method PUT `
            -Headers $studentHeaders

        Write-Host "  ✓ Notification marked as read" -ForegroundColor Green
    } else {
        Write-Host "`n[7/10] Skipping (no notifications to mark)" -ForegroundColor Yellow
    }

    # Step 8: Get notification settings
    Write-Host "`n[8/10] Testing GET /api/notification-settings..." -ForegroundColor Yellow
    $settingsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/notification-settings" `
        -Method GET `
        -Headers $studentHeaders

    Write-Host "  ✓ Settings: email=$($settingsResponse.data.email_enabled), assignment_created=$($settingsResponse.data.assignment_created)" -ForegroundColor Green

    # Step 9: Update notification settings
    Write-Host "`n[9/10] Testing PUT /api/notification-settings..." -ForegroundColor Yellow
    $updateSettingsBody = @{
        deadline_reminder = $false
        graded = $true
    } | ConvertTo-Json

    $updateSettingsResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/notification-settings" `
        -Method PUT `
        -Headers $studentHeaders `
        -Body $updateSettingsBody

    Write-Host "  ✓ Settings updated" -ForegroundColor Green

    # Step 10: Mark all as read
    Write-Host "`n[10/10] Testing PUT /api/notifications/read-all..." -ForegroundColor Yellow
    $markAllResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/notifications/read-all" `
        -Method PUT `
        -Headers $studentHeaders

    Write-Host "  ✓ All notifications marked as read" -ForegroundColor Green

    Write-Host "`n=== All Notification Management API Tests Passed! ===" -ForegroundColor Green

} catch {
    Write-Host "`nError: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
    exit 1
}
