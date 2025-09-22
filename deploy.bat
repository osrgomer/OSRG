@echo off
echo OSRG Connect - Deploy to Hostinger
echo ===================================
echo.
echo This script will help you deploy changes to your Hostinger server.
echo.
echo Steps to deploy:
echo 1. Make your changes in VS Code
echo 2. Run: git add . && git commit -m "Your commit message"
echo 3. Run: git push origin master
echo 4. Manually upload changed files to Hostinger File Manager
echo.
echo Your local development folder: %cd%\private_social_platform
echo Your Hostinger path: public_html/osrg/private_social_platform/
echo.
echo Files changed in last commit:
git diff --name-only HEAD~1 HEAD
echo.
pause