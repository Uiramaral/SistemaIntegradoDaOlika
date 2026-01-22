# ---------------------------------------------
# Script: deploy-hostgator.ps1
# ---------------------------------------------

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$ErrorActionPreference = "Stop"

# === CONFIGURAÇÃO ===
$sessionName = "Olika Dev"
$remotePath  = "/public_html/desenvolvimento"
$winscpPath  = "C:\Program Files (x86)\WinSCP\WinSCP.com"
# ====================

Write-Host "----------------------------------------" -ForegroundColor Cyan
Write-Host "🚀 Deploy HostGator: $sessionName" -ForegroundColor Yellow
Write-Host "----------------------------------------" -ForegroundColor Cyan

# 1. Verificar WinSCP
if (!(Test-Path $winscpPath)) {
    Write-Host "❌ WinSCP não encontrado!" -ForegroundColor Red
    exit
}

# 2. Arquivos alterados (último commit)
Write-Host "🔍 Verificando alterações..." -ForegroundColor Gray
$files = git diff --name-only HEAD~1 HEAD

if (!$files) {
    Write-Host "ℹ️ Nada para subir." -ForegroundColor Green
}
else {
    Write-Host "📦 Arquivos para upload:" -ForegroundColor White
    $files | ForEach-Object { Write-Host " - $_" -ForegroundColor Gray }

    # 3. Comandos WinSCP
    $commands = @(
        "open ""$sessionName""",
        "option batch continue",
        "option confirm off"
    )

    foreach ($file in $files) {
        if (Test-Path $file) {
            $remoteFileDir = Split-Path "$remotePath/$file" -Parent
            $remoteFileDir = $remoteFileDir.Replace("\", "/")
            
            $commands += "mkdir -p ""$remoteFileDir"""
            $commands += "put ""$file"" ""$remotePath/$file"""
        }
    }

    $commands += "exit"

    # 4. Execução
    Write-Host "`n📤 Enviando..." -ForegroundColor Yellow
    $commands | & $winscpPath /command /stdin | Out-Null

    if ($LASTEXITCODE -eq 0) {
        Write-Host "`n✅ Sucesso!" -ForegroundColor Green
    } else {
        Write-Host "`n❌ Erro no upload." -ForegroundColor Red
    }
}
