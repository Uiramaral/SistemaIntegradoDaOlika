# ---------------------------------------------
# Script: git-sync-sistema.ps1
# Autor: Thomas (GPT-5)
# Descrição: Atualiza o repositório principal (Sistema Integrado da Olika)
# ---------------------------------------------

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$ErrorActionPreference = "Stop"

$Pasta = "C:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika"
$Repo  = "https://github.com/Uiramaral/SistemaIntegradoDaOlika.git"

Write-Host "----------------------------------------" -ForegroundColor Cyan
Write-Host "Atualizando repositório: Sistema Integrado da Olika" -ForegroundColor Yellow
Write-Host "----------------------------------------" -ForegroundColor Cyan

Set-Location $Pasta

# Garante o remote correto
git remote set-url origin $Repo

# Ignora arquivos sensíveis
$ignoreList = @(".env", "olika-whatsapp-integration/")
if (-not (Test-Path ".gitignore")) { New-Item ".gitignore" -ItemType File | Out-Null }
foreach ($item in $ignoreList) {
    if (-not (Select-String -Path ".gitignore" -Pattern $item -SimpleMatch -ErrorAction SilentlyContinue)) {
        Add-Content ".gitignore" "`n$item"
    }
}

# Adiciona, commita e envia
git add .
git commit -m "Atualização automática em $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -a 2>$null
git branch -M main
git fetch origin main
git merge origin/main --no-edit
git push origin main

Write-Host "✅ Sistema Integrado atualizado com sucesso!" -ForegroundColor Green
