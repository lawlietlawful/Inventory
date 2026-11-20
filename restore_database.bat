@echo off
title Canteen POS - Database Restore Tool
color 0a

echo ==============================================================
echo       CANTEEN MANAGEMENT SYSTEM - DATABASE RESTORE TOOL
echo ==============================================================
echo.
echo This script will import the centralized database into PostgreSQL.
echo Database: final_database (Schemas: canteen, inventory, eis)
echo.

set /p DB_HOST="Enter PostgreSQL Host [default: 127.0.0.1]: " || set DB_HOST=127.0.0.1
if "%DB_HOST%"=="" set DB_HOST=127.0.0.1

set /p DB_PORT="Enter PostgreSQL Port [default: 5433]: " || set DB_PORT=5433
if "%DB_PORT%"=="" set DB_PORT=5433

set /p DB_USER="Enter PostgreSQL Username [default: postgres]: " || set DB_USER=postgres
if "%DB_USER%"=="" set DB_USER=postgres

set /p PGPASSWORD="Enter PostgreSQL Password: "

echo.
echo [1/2] Creating database 'final_database' (if not exists)...
psql -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d postgres -c "CREATE DATABASE final_database;" 2>nul

echo [2/2] Importing database_backup.sql into 'final_database'...
psql -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d final_database -f "%~dp0database_backup.sql"

echo.
echo ==============================================================
echo   Restore complete! You can now start the application.
echo ==============================================================
set PGPASSWORD=
pause
