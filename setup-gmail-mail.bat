@echo off
cd /d "%~dp0"
echo.
echo === Gmail SMTP setup (DevBhoomi) ===
echo.
echo 1. Open: https://myaccount.google.com/apppasswords
echo 2. Turn ON 2-Step Verification, then create an App Password for Mail
echo 3. Paste the 16-character password when prompted below
echo.
C:\xampp\php\php.exe artisan mail:set-password
if errorlevel 1 goto :fail
C:\xampp\php\php.exe artisan config:clear
echo.
echo Done. Test with:
echo   C:\xampp\php\php.exe artisan mail:test-verification your@gmail.com
echo.
pause
exit /b 0

:fail
echo.
echo Setup failed. Check .env exists and PHP path is C:\xampp\php\php.exe
pause
exit /b 1
