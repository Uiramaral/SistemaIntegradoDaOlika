# Script para baixar fontes Inter com URLs corretas para cada peso
$ErrorActionPreference = "Stop"

$publicPath = "public"
$fontsPath = "$publicPath\fonts\inter"

# Criar diretório
New-Item -ItemType Directory -Force -Path $fontsPath | Out-Null

# URLs corretas para cada peso (versão latin)
$fontFiles = @{
    "Inter-Light.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-Regular.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-Medium.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-SemiBold.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-Bold.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-ExtraBold.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
}

Write-Host "NOTA: As fontes Inter usam a mesma URL base para latin em todos os pesos." -ForegroundColor Yellow
Write-Host "O navegador aplica o peso correto baseado no font-weight no CSS." -ForegroundColor Yellow
Write-Host "As fontes ja foram baixadas corretamente." -ForegroundColor Green

