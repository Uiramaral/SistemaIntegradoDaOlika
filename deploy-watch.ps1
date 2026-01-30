# ---------------------------------------------
# Script: deploy-watch.ps1
# ---------------------------------------------
param(
    [int]$DebounceSeconds = 5,
    [switch]$ForceAllOnStart
)

$ErrorActionPreference = "Stop"
$projectRoot = $PSScriptRoot
$deployScript = Join-Path $projectRoot "deploy-hostgator.ps1"

# 1. Limpeza de eventos anteriores
$eventIds = @("FileChanged", "FileCreated", "FileDeleted", "FileRenamed")
foreach ($id in $eventIds) {
    Get-EventSubscriber -SourceIdentifier $id -ErrorAction SilentlyContinue | Unregister-Event -Force
    Get-Event -SourceIdentifier $id -ErrorAction SilentlyContinue | Remove-Event
}

# 2. Estado
$state = [hashtable]::Synchronized(@{
    inProgress = $false
    needsDeploy = $false
    lastEventTime = [DateTime]::MinValue
})

$ignoreFragments = @("\.git\", "\node_modules\", "\.deploy-hostgator.", "deploy-watch.ps1", "deploy-hostgator.ps1")

function ShouldIgnorePath([string]$path) {
    foreach ($frag in $ignoreFragments) { if ($path -like "*$frag*") { return $true } }
    return $false
}

# 3. Watcher
$watcher = New-Object System.IO.FileSystemWatcher -ArgumentList $projectRoot
$watcher.IncludeSubdirectories = $true
$watcher.NotifyFilter = [System.IO.NotifyFilters]'FileName, LastWrite, Attributes'

# 4. Registro (Antes do Loop)
Register-ObjectEvent $watcher Changed -SourceIdentifier "FileChanged" | Out-Null
Register-ObjectEvent $watcher Created -SourceIdentifier "FileCreated" | Out-Null
Register-ObjectEvent $watcher Deleted -SourceIdentifier "FileDeleted" | Out-Null
Register-ObjectEvent $watcher Renamed -SourceIdentifier "FileRenamed" | Out-Null
$watcher.EnableRaisingEvents = $true

Write-Host "----------------------------------------" -ForegroundColor Cyan
Write-Host "WATCHER ATIVO (Debounce: $DebounceSeconds s)" -ForegroundColor Yellow
Write-Host "Aguardando estabilizacao dos arquivos..." -ForegroundColor Gray
Write-Host "----------------------------------------" -ForegroundColor Cyan

if ($ForceAllOnStart) { & $deployScript -ForceAll }

# 5. Loop de Monitoramento
try {
    while ($true) {
        $hasChanges = $false
        foreach ($id in $eventIds) {
            $evt = Get-Event -SourceIdentifier $id -ErrorAction SilentlyContinue
            if ($evt) {
                $path = $evt.SourceEventArgs.FullPath
                if (-not (ShouldIgnorePath $path)) {
                    $hasChanges = $true
                    $state.lastEventTime = Get-Date # Reinicia o cronômetro
                    Write-Host "Mudanca detectada em: $(Split-Path $path -Leaf). Aguardando silencio..." -ForegroundColor Gray
                }
                Remove-Event -SourceIdentifier $id
            }
        }

        if ($hasChanges) { $state.needsDeploy = $true }

        # Lógica de Silêncio/Estabilidade
        $secondsSinceLastChange = ((Get-Date) - $state.lastEventTime).TotalSeconds
        
        if ($state.needsDeploy -and $secondsSinceLastChange -ge $DebounceSeconds -and -not $state.inProgress) {
            $state.inProgress = $true
            Write-Host "`n>>> [$(Get-Date -Format HH:mm:ss)] Arquivos estaveis. Iniciando deploy..." -ForegroundColor Cyan
            try {
                & $deployScript
            } catch {
                Write-Host "Erro: $($_.Exception.Message)" -ForegroundColor Red
            }
            $state.needsDeploy = $false
            $state.inProgress = $false
            Write-Host "Pronto. Monitorando...`n" -ForegroundColor Gray
        }
        Start-Sleep -Milliseconds 500
    }
} finally {
    $watcher.EnableRaisingEvents = $false
    $watcher.Dispose()
    foreach ($id in $eventIds) { Unregister-Event -SourceIdentifier $id -ErrorAction SilentlyContinue }
    Write-Host "Watcher parado." -ForegroundColor Yellow
}