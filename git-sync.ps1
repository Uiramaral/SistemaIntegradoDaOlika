# ---------------------------------------------
# Script: git-sync.ps1
# Autor: Thomas (GPT-5)
# Descrição: Atualiza os repositórios Git da Olika automaticamente
# ---------------------------------------------

# --- CONFIGURAÇÃO DE CONSOLE ---
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$PSDefaultParameterValues['Out-File:Encoding'] = 'utf8'
$ErrorActionPreference = "Stop"

# --- CAMINHOS LOCAIS ---
$PastaSistema  = "C:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika"
$PastaWhatsApp = "C:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika\olika-whatsapp-integration"

# --- REPOSITÓRIOS REMOTOS ---
$RepoSistema  = "https://github.com/Uiramaral/SistemaIntegradoDaOlika.git"
$RepoWhatsApp = "https://github.com/Uiramaral/olika-whatsapp-integration.git"

# --- FUNÇÃO PRINCIPAL ---
function Atualizar-Repo {
    param (
        [string]$Path,
        [string]$Remote,
        [string]$Nome,
        [string[]]$Ignorar = @()
    )

    Write-Host "----------------------------------------" -ForegroundColor Cyan
    Write-Host "Atualizando repositório: $Nome" -ForegroundColor Yellow
    Write-Host "----------------------------------------" -ForegroundColor Cyan

    try {
        Set-Location $Path
    }
    catch {
        Write-Host "ERRO: O caminho '$Path' não foi encontrado. Verifique as variáveis de caminho." -ForegroundColor Red
        return
    }

    # Inicializa o repositório se não existir
    if (-not (Test-Path ".git")) {
        Write-Host "Inicializando repositório Git..." -ForegroundColor Green
        git init | Out-Null
        git remote add origin $Remote
    }

    # Configura o Git para evitar avisos de CRLF
    git config core.autocrlf true

    # Atualiza o .gitignore com exclusões específicas
    if ($Ignorar.Count -gt 0) {
        Write-Host "Atualizando .gitignore..." -ForegroundColor Gray

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

    # Adiciona arquivos e faz commit
    git add . | Out-Null

    try {
        git commit -m "Atualização automática em $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" | Out-Null
    }
    catch {
        Write-Host "Nenhuma modificação nova para commitar." -ForegroundColor DarkYellow
    }

    git branch -M main

    # Faz pull antes do push (para evitar non-fast-forward)
    try {
        git pull origin main --rebase
    }
    catch {
        Write-Host "Sem atualizações remotas ou falha no pull. Continuando com o push..." -ForegroundColor Gray
    }

    # Faz push das mudanças
    try {
        git push origin main
        Write-Host "Atualização concluída para $Nome!" -ForegroundColor Green
    }
    catch {
        Write-Host "ERRO: Falha ao executar 'git push origin main'. Verifique credenciais ou conflitos." -ForegroundColor Red
    }

    Write-Host ""
} # <-- Chave final da função

# --- EXECUÇÃO DOS DOIS REPOSITÓRIOS ---
Atualizar-Repo -Path $PastaSistema `
               -Remote $RepoSistema `
               -Nome "Sistema Unificado da Olika" `
               -Ignorar @(".env", "olika-whatsapp-integration/")

Atualizar-Repo -Path $PastaWhatsApp `
               -Remote $RepoWhatsApp `
               -Nome "Olika WhatsApp Integration"

Write-Host "----------------------------------------" -ForegroundColor Cyan
Write-Host "Todos os repositórios foram atualizados com sucesso!" -ForegroundColor Green
Write-Host "----------------------------------------" -ForegroundColor Cyan
