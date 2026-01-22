# ---------------------------------------------
# Script: deploy-hostgator.ps1
# ---------------------------------------------

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$ErrorActionPreference = "Stop"

# === CONFIGURACAO ===
$sessionName = "Olika Dev"
$remotePath  = "/desenvolvimento"
$winscpPath  = "C:\Program Files (x86)\WinSCP\WinSCP.com"
$stateFile   = ".deploy-hostgator.last"
$logFile     = ".deploy-hostgator.log"
# Diretorio local do projeto (para o WinSCP encontrar os arquivos)
$localRoot   = $PSScriptRoot
# Se true, inclui arquivos modificados ainda nao commitados
$includeUncommitted = $true
# Ajuste esta lista para ignorar arquivos/pastas no deploy
$ignorePatterns = @(
    "temp_saas_reference",
    "deploy-hostgator.ps1",
    "git-sync.ps1",
    ".deploy-hostgator.*",
    ".env",
    ".env.*",
    ".gitignore",
    "README.md",
    "DESIGN_SPECIFICATION.md"
)
# ====================

Write-Host "----------------------------------------" -ForegroundColor Cyan
Write-Host "Deploy HostGator: $sessionName" -ForegroundColor Yellow
Write-Host "----------------------------------------" -ForegroundColor Cyan

# 1. Verificar WinSCP
if (-not (Test-Path $winscpPath)) {
    Write-Host "WinSCP nao encontrado!" -ForegroundColor Red
    exit
}

# 2. Arquivos alterados desde o ultimo deploy
Write-Host "Verificando alteracoes..." -ForegroundColor Gray
$head = (git rev-parse HEAD).Trim()
$base = $null
if (Test-Path $stateFile) {
    $base = (Get-Content $stateFile -TotalCount 1).Trim()
}
if ([string]::IsNullOrWhiteSpace($base)) {
    $base = "HEAD~1"
}

function ShouldIgnore($path, $patterns) {
    foreach ($pattern in $patterns) {
        if ($path -like $pattern -or $path -like "$pattern*") {
            return $true
        }
    }
    return $false
}

$files = @()
$files += git diff --name-only --diff-filter=AM $base HEAD
$files += git diff --name-only --diff-filter=AM --cached
if ($includeUncommitted) {
    $files += git diff --name-only --diff-filter=AM
    $files += git ls-files -m -o --exclude-standard
}
$files = $files | Sort-Object -Unique | Where-Object {
    $_ -and (Test-Path $_ -PathType Leaf) -and (-not (ShouldIgnore $_ $ignorePatterns))
}

if (-not $files) {
    Write-Host "Nada para subir." -ForegroundColor Green
    exit
}

Write-Host "Arquivos para upload:" -ForegroundColor White
$files | ForEach-Object { Write-Host " - $_" -ForegroundColor Gray }

# 3. Comandos WinSCP
$commands = @()
$commands += "open ""$sessionName"""
$commands += "option batch continue"
$commands += "option confirm off"

# Criar pastas remotas (recursivo) antes dos uploads
$remoteDirs = New-Object System.Collections.Generic.HashSet[string]
foreach ($file in $files) {
    $remoteFileDir = Split-Path "$remotePath/$file" -Parent
    $remoteFileDir = $remoteFileDir.Replace('\', '/')
    if ($remoteFileDir -like "$remotePath*") {
        $relative = $remoteFileDir.Substring($remotePath.Length).TrimStart('/')
    } else {
        $relative = $remoteFileDir.TrimStart('/')
    }
    if (-not [string]::IsNullOrWhiteSpace($relative)) {
        $parts = $relative.Split('/')
        $current = $remotePath.TrimEnd('/')
        foreach ($part in $parts) {
            if ($part) {
                $current = "$current/$part"
                $remoteDirs.Add($current) | Out-Null
            }
        }
    }
}

foreach ($dir in ($remoteDirs | Sort-Object Length)) {
    $commands += "mkdir ""$dir"""
}

foreach ($file in $files) {
    if (Test-Path $file) {
        $localFile = Join-Path $localRoot $file
        $remoteTarget = "$remotePath/$file"
        $remoteTarget = $remoteTarget.Replace('\', '/')
        $commands += "put ""$localFile"" ""$remoteTarget"""
    }
}

$commands += "exit"

# 4. Execucao
Write-Host "`nEnviando..." -ForegroundColor Yellow
$commands | & $winscpPath /log="$logFile" /command /stdin | Out-Null

if ($LASTEXITCODE -eq 0) {
    Set-Content -Path $stateFile -Value $head
    Write-Host "`nSucesso!" -ForegroundColor Green
} else {
    Write-Host "`nErro no upload." -ForegroundColor Red
    if (Test-Path $logFile) {
        Write-Host "Log: $logFile" -ForegroundColor Yellow
        Get-Content $logFile -Tail 40 | ForEach-Object { Write-Host $_ }
    }
}
