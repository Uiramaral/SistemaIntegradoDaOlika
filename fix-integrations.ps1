# =====================================================
# SCRIPT PARA CORRIGIR INTEGRAÇÕES NO SERVIDOR
# =====================================================

Write-Host "🔧 Corrigindo integrações..." -ForegroundColor Cyan

# 1. Limpar cache de views compiladas localmente
Write-Host "`n📁 Limpando cache de views..." -ForegroundColor Yellow
Remove-Item "storage/framework/views/*" -Force -ErrorAction SilentlyContinue
Write-Host "✅ Cache de views limpo!" -ForegroundColor Green

# 2. SQL para executar no servidor
$sql = @"
-- Limpar integrações com dados incorretos
DELETE FROM api_integrations WHERE client_id = 1;
"@

Write-Host "`n📋 SQL gerado:" -ForegroundColor Yellow
Write-Host $sql -ForegroundColor White

Write-Host "`n⚠️  INSTRUÇÕES:" -ForegroundColor Red
Write-Host "1. Acesse phpMyAdmin no servidor" -ForegroundColor White
Write-Host "2. Selecione o banco: hg6ddb59_larav25" -ForegroundColor White
Write-Host "3. Execute o SQL acima" -ForegroundColor White
Write-Host "4. Recarregue a página /dashboard/integrations" -ForegroundColor White

Write-Host "`n✨ Após executar o SQL, as integrações serão recriadas automaticamente!" -ForegroundColor Green
