# Instruções Gerais do Agente

Este arquivo contém instruções permanentes para o comportamento do Agente Antigravity neste projeto.

## Idioma e Comunicação
- **Sempre responda em Português (Brasil).** Todas as conversas e explicações devem ser feitas prioritariamente neste idioma.

## Restrições do Ambiente de Produção
- **Não instalar dependências no servidor:** Nunca tente rodar `composer install`, `npm install` ou comandos similares diretamente no ambiente de produção.
- **Alterações de Banco de Dados:** Sempre gere scripts SQL puros para qualquer alteração no banco de dados. O uso de `php artisan migrate` no servidor de produção é problemático e deve ser evitado; prefira a execução manual de SQL.
- **Deploy:** O sistema utiliza scripts do tipo `deploy-hostgator.ps1`. Sempre considere esse fluxo de trabalho para atualizações.
