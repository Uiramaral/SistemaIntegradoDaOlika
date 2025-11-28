# Git Sync Script - Sistema Integrado da Olika
# PowerShell Version

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Git Sync - Sistema Integrado da Olika" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Mudar para o diretório do script
Set-Location $PSScriptRoot

# [1/4] Pull
Write-Host "[1/4] Puxando atualizações do repositório remoto..." -ForegroundColor Yellow
git pull origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: Falha ao fazer pull. Abortando." -ForegroundColor Red
    Read-Host "Pressione Enter para sair"
    exit 1
}
Write-Host ""

# [2/4] Add
Write-Host "[2/4] Adicionando arquivos modificados..." -ForegroundColor Yellow
git add .
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: Falha ao adicionar arquivos. Abortando." -ForegroundColor Red
    Read-Host "Pressione Enter para sair"
    exit 1
}
Write-Host ""

# [3/4] Commit
Write-Host "[3/4] Fazendo commit..." -ForegroundColor Yellow
git commit -m "Atualizações de UI: hero compacto, modal otimizado, cupons em grade"
if ($LASTEXITCODE -ne 0) {
    Write-Host "AVISO: Nenhuma mudança para commitar." -ForegroundColor Yellow
    Write-Host ""
}
Write-Host ""

# [4/4] Push
Write-Host "[4/4] Enviando para o repositório remoto..." -ForegroundColor Yellow
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERRO: Falha ao fazer push." -ForegroundColor Red
    Read-Host "Pressione Enter para sair"
    exit 1
}
Write-Host ""

Write-Host "============================================" -ForegroundColor Green
Write-Host "  Processo concluído com sucesso!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Green
Read-Host "Pressione Enter para fechar"

