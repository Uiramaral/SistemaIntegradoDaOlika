@echo off
echo Sincronizando storage...
xcopy "storage\app\public\*" "public\storage\" /E /I /H /Y
echo Storage sincronizado com sucesso!
pause
