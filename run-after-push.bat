@echo off
REM ===== Configuration =====
set "PLINK=C:\Program Files\PuTTY\plink.exe"
set SERVER_IP=192.168.1.100
set USERNAME=webserver
set PASSWORD=dapho04051983
set "REMOTE_DIR_FRONTEND=systems/Oracle-Netsuite-Printing"
set "REMOTE_DIR_BACKEND=systems/Oracle-Netsuite-Printing-Server"

REM ===== Run commands via PuTTY =====
echo.
echo.
call :ColorText 0a "==================================================================="
echo.
echo                       BUILDING AND DEPLOYING FRONTEND...
call :ColorText 0a "==================================================================="
echo.
"%PLINK%" -batch -ssh %USERNAME%@%SERVER_IP% -pw %PASSWORD% ^
  "cd %REMOTE_DIR_FRONTEND% && git pull && docker-compose down && docker-compose up --build -d"
echo.
echo.
echo.
REM ===== Execute =====
call :ColorText 0a "==================================================================="
echo.
call :ColorText 0a "============ !!! Building & Deployment completed !!! ============"
echo.
call :ColorText 0a "==================================================================="
echo.
echo.
echo.
call :ColorText 0a "0%%="
echo.
call :ColorText 0a "10%%====="
echo.
call :ColorText 0a "20%%=========="
echo.
call :ColorText 0a "30%%================"
echo.
call :ColorText 0a "40%%======================"
echo.
call :ColorText 0a "50%%============================"
echo.
call :ColorText 0a "60%%=================================="
echo.
call :ColorText 0a "70%%========================================"
echo.
call :ColorText 0a "80%%=============================================="
echo.
call :ColorText 0a "90%%==================================================="
echo.
call :ColorText 0a "100%%========================================================"
echo.
echo.


REM ===== Simplified SSH Command =====
set "SSH_CMD=cd %REMOTE_DIR_BACKEND% && CURRENT=$(grep -oP '^APP_VERSION=\K[0-9]+\.[0-9]+' .env) && MAJOR=$(echo $CURRENT | cut -d. -f1) && MINOR=$(echo $CURRENT | cut -d. -f2 | sed 's/^0*//') && NEW_MINOR=$((MINOR + 1)) && if [ $NEW_MINOR -gt 99 ]; then NEW_MINOR=0 && MAJOR=$((MAJOR + 1)); fi && NEW_VERSION=$MAJOR.$(printf %%02d $NEW_MINOR) && sed -i \"s/^APP_VERSION=$CURRENT/APP_VERSION=$NEW_VERSION/\" .env && echo \"++++++++++++++ APP_VERSION updated from $CURRENT to $NEW_VERSION ++++++++++++++\""

echo.
echo.
REM ===== Execute =====
call :ColorText 0a "==================================================================="
echo.
echo                       Incrementing APP_VERSION...
"%PLINK%" -batch -ssh %USERNAME%@%SERVER_IP% -pw %PASSWORD% "%SSH_CMD%"
call :ColorText 0a "==================================================================="
echo.

REM ===== Verify =====
echo.
echo.
echo.
call :ColorText 0a "==================================================================="
echo.
echo                       Current APP_VERSION:
"%PLINK%" -batch -ssh %USERNAME%@%SERVER_IP% -pw %PASSWORD% "cd %REMOTE_DIR_BACKEND% && echo '++++++++++++++++++++++++ '$(grep '^APP_VERSION=' .env)' ++++++++++++++++++++++++'"
call :ColorText 0a "==================================================================="
echo.

echo.
echo.
call :ColorText 0a "======================================================="
echo.
echo +++++++++ VERSION UPDATED SUCCESSFULLY +++++++++
call :ColorText 0a "======================================================="
echo.
echo.
echo.
echo.
call :ColorText 0a "0%%="
echo.
call :ColorText 0a "10%%====="
echo.
call :ColorText 0a "20%%=========="
echo.
call :ColorText 0a "30%%================"
echo.
call :ColorText 0a "40%%======================"
echo.
call :ColorText 0a "50%%============================"
echo.
call :ColorText 0a "60%%=================================="
echo.
call :ColorText 0a "70%%========================================"
echo.
call :ColorText 0a "80%%=============================================="
echo.
call :ColorText 0a "90%%==================================================="
echo.
call :ColorText 0a "100%%========================================================"
echo.
echo.

echo.
call :ColorText 0a "==================================================================="
echo.
echo                       BUILDING AND DEPLOYING BACKEND...
call :ColorText 0a "==================================================================="
echo.
echo.

"%PLINK%" -batch -ssh %USERNAME%@%SERVER_IP% -pw %PASSWORD% ^
  "cd %REMOTE_DIR_BACKEND% && git pull && docker-compose down && docker-compose up --build -d"
echo.
echo.
REM ===== Execute =====
echo.
call :ColorText 0a "==================================================================="
echo.
call :ColorText 0a "============ !!! Building & Deployment completed !!! ============"
echo.
call :ColorText 0a "==================================================================="
echo.

pause
exit /b

:ColorText
<nul set /p ".=." > "%~2"
findstr /v /a:%1 /R "^$" "%~2" nul
del "%~2" > nul 2>&1
goto :eof