@echo off
echo Starting pegLIT Linker API Server on port 5001...
echo Leave this window open while using PROpeg.
echo.
cd /d "%~dp0..\ext_tools\peglit"
".\venv\Scripts\python.exe" linker_server.py
pause
