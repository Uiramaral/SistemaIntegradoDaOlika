@echo off
cd /d "c:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika"
git add -A
git commit -m "fix: ClientScope client_id ambiguity + remove HTMX"
git push origin main
echo Done!
pause
