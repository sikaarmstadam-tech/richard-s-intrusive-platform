@echo off
:: ============================================================
:: Piplex Operations — Development Server
:: Double-click to start: http://127.0.0.1:8080
:: ============================================================
title Piplex Dev Server
echo.
echo  ================================================
echo   PIPLEX OPERATIONS — Dev Server Starting...
echo   Open: http://127.0.0.1:8080
echo   Press Ctrl+C to stop
echo  ================================================
echo.

"C:\Users\sikaa\OneDrive\Desktop\richard's intrusive platform\php\php.exe" -c "C:\Users\sikaa\OneDrive\Desktop\richard's intrusive platform\backend\php.ini" -S 127.0.0.1:8080 -t "C:\Users\sikaa\OneDrive\Desktop\richard's intrusive platform"

echo.
echo Server stopped.
pause
