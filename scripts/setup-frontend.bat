@echo off
cd /d %~dp0\..\frontend

echo ========================================
echo Frontend Setup - School Assignment Manager
echo ========================================
echo.

echo [1/6] Creating Vue 3 project...
if exist "package.json" (
    echo Vue project already exists - skipping creation
) else (
    (echo y & echo n & echo n) | call npm create vite@5 frontend-temp -- --template vue-ts
    if %errorlevel% neq 0 (
        echo ERROR: Failed to create Vue project
        pause
        exit /b 1
    )
    move frontend-temp\* . > nul 2>&1
    move frontend-temp\.* . > nul 2>&1
    rmdir frontend-temp
)
echo.

echo [2/6] Installing base dependencies...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: Failed to install dependencies
    pause
    exit /b 1
)
echo.

echo [3/6] Installing Vue Router and Pinia...
call npm install vue-router@4 pinia
echo.

echo [4/6] Installing Axios...
call npm install axios
echo.

echo [5/6] Installing Vuetify...
call npm install vuetify@next @mdi/font
echo.

echo [6/6] Installing additional packages...
call npm install vee-validate yup dayjs
call npm install -D @vue/eslint-config-typescript eslint prettier eslint-plugin-vue @types/node
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Configure environment files
echo 2. Set up Vuetify plugin
echo 3. Configure Vue Router
echo 4. Run: npm run dev
echo.
pause
