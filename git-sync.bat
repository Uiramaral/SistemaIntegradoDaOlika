@echo off
echo ============================================
echo   Git Sync - Sistema Integrado da Olika
echo ============================================
echo.

cd /d "%~dp0"

echo [1/4] Puxando atualizações do repositório remoto...
git pull origin main
if %errorlevel% neq 0 (
    echo ERRO: Falha ao fazer pull. Abortando.
    pause
    exit /b 1
)
echo.

echo [2/4] Adicionando arquivos modificados...
git add .
if %errorlevel% neq 0 (
    echo ERRO: Falha ao adicionar arquivos. Abortando.
    pause
    exit /b 1
)
echo.

echo [3/4] Fazendo commit...
git commit -m "Atualizações de UI: hero compacto, modal otimizado, cupons em grade"
if %errorlevel% neq 0 (
    echo AVISO: Nenhuma mudança para commitar.
    echo.
)
echo.

echo [4/4] Enviando para o repositório remoto...
git push origin main
if %errorlevel% neq 0 (
    echo ERRO: Falha ao fazer push.
    pause
    exit /b 1
)
echo.

echo ============================================
echo   Processo concluído com sucesso!
echo ============================================
pause

