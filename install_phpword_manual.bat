@echo off
echo ============================================
echo PHPWord Manual Installation Script
echo ============================================
echo.

REM Create vendor directories
echo Creating vendor directories...
if not exist "vendor\phpoffice\phpword" mkdir vendor\phpoffice\phpword

echo.
echo Please download PHPWord manually:
echo 1. Go to: https://github.com/PHPOffice/PHPWord/archive/refs/heads/master.zip
echo 2. Extract the ZIP file
echo 3. Copy all contents to: c:\xampp\htdocs\mdr1\vendor\phpoffice\phpword\
echo.
echo After copying files, run this again to verify.
echo.

if exist "vendor\phpoffice\phpword\src" (
    echo [SUCCESS] PHPWord found!
    echo.
    echo Creating autoloader...

    REM The autoloader will be created by the next script
    echo Done! You can now use the Terminal Report Generator.
) else (
    echo [WAITING] PHPWord not found yet.
    echo Please download and extract to vendor\phpoffice\phpword\
)

echo.
pause
