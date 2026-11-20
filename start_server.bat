@echo off
title Inventory Dawn Management System (Group 2)
color 0b
echo ==============================================================
echo       INVENTORY MANAGEMENT SYSTEM (GROUP 2) - LAUNCHER
echo ==============================================================
echo.
echo Starting Laravel Web Server on http://127.0.0.1:8001 ...
echo Connected Database: final_database (PostgreSQL Port 5433)
echo Press Ctrl+C at any time to stop the server.
echo.
php artisan serve --host=127.0.0.1 --port=8001
pause
