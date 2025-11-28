# ---------------------------------------------
# Script: git-sync.ps1
# Autor: Thomas (GPT-5)
# Descrição: Atualiza os repositórios Git da Olika
# ---------------------------------------------

# Força codificação UTF-8 (corrige caracteres especiais)
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Interrompe execução em caso de erro
$ErrorActionPreference = "Stop"

# Caminhos locais
$PastaSistema = "C:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika"
$PastaWhatsApp = "C:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika\olika-whatsapp-integration"

# Repositórios remotos
$RepoSistema = "https://github.com/Uiramaral/SistemaIntegradoDaOlika.git"
$RepoWhatsApp = "https://github.com/Uiramaral/olika-whatsapp-integration.git"

function Atualizar-Repo {
    param (
        [string]$Path,
        [string]$Remote,
        [string]$Nome,
        [string[]]$Ignorar = @()
    )

    Write-Host "----------------------------------------" -ForegroundColor Cyan
    Write-Host "🔄 Atualizando repositório: $Nome" -ForegroundColor Yellow
    Write-Host "----------------------------------------" -ForegroundColor Cyan

    Set-Location $Path

    if (-not (Test-Path ".git")) {
        Write-Host "🚀 Inicializando repositório Git..." -ForegroundColor Green
        git init | Out-Null
        git remote add origin $Remote
    }

    # Atualiza o .gitignore (sem causar erro)
    if ($Ignorar.Count -gt 0) {
        Write-Host "🧩 Atualizando .gitignore..." -ForegroundColor Gray

        if (-not (Test-Path ".gitignore")) {
            New-Item -ItemType File -Path ".gitignore" | Out-Null
        }

        foreach ($item in $Ignorar) {
            $pattern = [regex]::Escape($item)
            $exists = Select-String -Path ".gitignore" -Pattern $pattern -SimpleMatch -ErrorAction SilentlyContinue
            if (-not $exists) {
                Add-Content ".gitignore" "`n$item"
            }
        }
    }

    git add .

    try {
        git commit -m "Atualização automática em $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" | Out-Null
    }
    catch {
        Write-Host "⚠️ Nenhuma modificação nova para commitar." -ForegroundColor DarkYellow
    }

    git branch -M main
    git push -u origin main

    Write-Host "✅ Atualização concluída para $Nome!" -ForegroundColor Green
    Write-Host ""
}

# Atualiza o repositório principal (Sistema Unificado)
Atualizar-Repo -Path $PastaSistema `
               -Remote $RepoSistema `
               -Nome "Sistema Unificado da Olika" `
               -Ignorar @(".env", "olika-whatsapp-integration/")

# Atualiza o repositório do WhatsApp Integration
Atualizar-Repo -Path $PastaWhatsApp `
               -Remote $RepoWhatsApp `
               -Nome "Olika WhatsApp Integration"

Write-Host "🎉 Todos os repositórios foram atualizados com sucesso!" -ForegroundColor Green
