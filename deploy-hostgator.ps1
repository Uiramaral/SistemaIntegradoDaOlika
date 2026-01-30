# ---------------------------------------------
# Script: deploy-hostgator.ps1
# ---------------------------------------------
param([switch]$ForceAll)

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$ErrorActionPreference = "Stop"

# === CONFIGURAÇÃO ===
$sessionName = "Olika Dev"
$remotePath  = "/desenvolvimento"
$winscpPath  = "C:\Program Files (x86)\WinSCP\WinSCP.com"
$stateFile   = ".deploy-hostgator.state.json"
$logFile     = ".deploy-hostgator.log"
$ignorePatterns = @("temp_saas_reference", "deploy-hostgator.ps1", "deploy-watch.ps1", ".deploy-hostgator.*", ".env*", ".git*", "README.md", "*.tmp")
# ====================

if (-not (Test-Path $winscpPath)) {
    Write-Host "ERRO: WinSCP não encontrado em $winscpPath" -ForegroundColor Red
    exit
}

# Carregar estado anterior
$previousState = @{}
if (Test-Path $stateFile) {
    try {
        $content = Get-Content $stateFile -Raw | ConvertFrom-Json
        $content.PSObject.Properties | ForEach-Object { $previousState[$_.Name] = $_.Value }
    } catch { }
}

# Analisar arquivos
Write-Host "Analisando arquivos locais..." -ForegroundColor Gray
$allFiles = git ls-files
$allFiles += git ls-files -m -o --exclude-standard

$filesToUpload = @()
$newState = $previousState.Clone()

foreach ($relPath in ($allFiles | Sort-Object -Unique)) {
    $isIgnored = $ignorePatterns | Where-Object { $relPath -like $_ -or $relPath -match "^$_" }
    if ($isIgnored -or -not (Test-Path $relPath -PathType Leaf)) { continue }

    # Pular arquivos vazios (comum dar erro em FTP)
    if ((Get-Item $relPath).Length -eq 0) { continue }

    $currentHash = (Get-FileHash -Algorithm SHA256 -Path $relPath).Hash
    
    if ($ForceAll -or -not $previousState.ContainsKey($relPath) -or $previousState[$relPath] -ne $currentHash) {
        $filesToUpload += $relPath
        $newState[$relPath] = $currentHash
    }
}

if ($filesToUpload.Count -eq 0) {
    Write-Host "Tudo atualizado." -ForegroundColor Green
    exit
}

Write-Host "Arquivos detectados: $($filesToUpload.Count)" -ForegroundColor Cyan

# Gerar Comandos WinSCP
$commands = @(
    "open ""$sessionName""",
    "option batch continue",
    "option confirm off",
    "option transfer binary"
)

# Estrutura de pastas
$remoteDirs = @()
foreach ($file in $filesToUpload) {
    $dir = Split-Path $file -Parent
    if ($dir) {
        $parts = $dir.Split('\')
        $current = ""
        foreach ($part in $parts) {
            $current = if ($current) { "$current/$part" } else { $part }
            if ($remoteDirs -notcontains $current) { $remoteDirs += $current }
        }
    }
}

foreach ($dir in ($remoteDirs | Sort-Object Length)) {
    $fullRemoteDir = "$remotePath/$dir".Replace('\', '/')
    $commands += "mkdir ""$fullRemoteDir""" 
}

foreach ($file in $filesToUpload) {
    $localFile = Join-Path $PSScriptRoot $file
    $remoteTarget = "$remotePath/$file".Replace('\', '/')
    $commands += "put ""$localFile"" ""$remoteTarget"""
}

$commands += "exit"

# Execução
Write-Host "Iniciando Upload via WinSCP..." -ForegroundColor Yellow
$commands | & $winscpPath /log="$logFile" /command /stdin | Out-Null

if ($LASTEXITCODE -eq 0) {
    $newState | ConvertTo-Json | Set-Content $stateFile
    Write-Host "Deploy com sucesso!" -ForegroundColor Green
    [Console]::Beep(440, 200) # Aviso sonoro de sucesso
} else {
    Write-Host "Erro no upload. Veja o log: $logFile" -ForegroundColor Red
}