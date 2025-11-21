# Script para baixar Tailwind CSS e Google Fonts localmente
$ErrorActionPreference = "Stop"

$publicPath = "public"
$fontsPath = "$publicPath\fonts\inter"
$cssPath = "$publicPath\css"
$jsPath = "$publicPath\js"

# Criar diretórios
Write-Host "Criando diretórios..." -ForegroundColor Green
New-Item -ItemType Directory -Force -Path $fontsPath | Out-Null
New-Item -ItemType Directory -Force -Path $cssPath | Out-Null
New-Item -ItemType Directory -Force -Path $jsPath | Out-Null

# URLs das fontes Inter (latin - mais comum)
# Nota: Vamos baixar apenas a versão latin que é suficiente para português
$fontFiles = @{
    "Inter-Light.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-Regular.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-Medium.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-SemiBold.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-Bold.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
    "Inter-ExtraBold.woff2" = "https://fonts.gstatic.com/s/inter/v20/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2"
}

Write-Host "Baixando fontes Inter..." -ForegroundColor Green
foreach ($file in $fontFiles.Keys) {
    $url = $fontFiles[$file]
    $output = "$fontsPath\$file"
    if (-not (Test-Path $output)) {
        Write-Host "  Baixando $file..." -ForegroundColor Yellow
        try {
            Invoke-WebRequest -Uri $url -OutFile $output -UseBasicParsing
            Write-Host "  OK $file baixado" -ForegroundColor Green
        } catch {
            Write-Host "  ERRO ao baixar $file : $_" -ForegroundColor Red
        }
    } else {
        Write-Host "  $file ja existe" -ForegroundColor Cyan
    }
}

# Criar arquivo CSS local para as fontes
Write-Host "Criando arquivo CSS das fontes..." -ForegroundColor Green
$fontCssContent = @'
/* Inter Font - Local */
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 300;
  font-display: swap;
  src: url('../fonts/inter/Inter-Light.woff2') format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url('../fonts/inter/Inter-Regular.woff2') format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url('../fonts/inter/Inter-Medium.woff2') format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url('../fonts/inter/Inter-SemiBold.woff2') format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url('../fonts/inter/Inter-Bold.woff2') format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 800;
  font-display: swap;
  src: url('../fonts/inter/Inter-ExtraBold.woff2') format('woff2');
}
'@

$fontCssContent | Out-File -FilePath "$cssPath\inter-fonts.css" -Encoding UTF8
Write-Host "OK Arquivo CSS das fontes criado" -ForegroundColor Green

# Baixar Tailwind CSS (usando CDN compilado como fallback)
Write-Host ""
Write-Host "NOTA: O Tailwind CSS via CDN (cdn.tailwindcss.com) e um servico especial que compila CSS em tempo real." -ForegroundColor Yellow
Write-Host "Para uma solucao local completa, recomenda-se instalar via npm e compilar." -ForegroundColor Yellow
Write-Host "O sistema ja esta configurado com fallback multiplo de CDNs." -ForegroundColor Yellow

Write-Host ""
Write-Host "OK Processo concluido!" -ForegroundColor Green
Write-Host "Arquivos salvos em:" -ForegroundColor Cyan
Write-Host "  - Fontes: $fontsPath" -ForegroundColor Cyan
Write-Host "  - CSS Fontes: $cssPath\inter-fonts.css" -ForegroundColor Cyan
