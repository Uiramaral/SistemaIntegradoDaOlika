<?php
/**
 * Script CLI para executar migrations
 * Execute via linha de comando: php migrate_cli.php
 * OU via navegador: https://seudominio.com/migrate_cli.php
 */

// Verificar se está sendo executado via CLI ou web
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Olika - Migrations</title>";
    echo "<style>body{font-family:monospace;margin:20px;background:#000;color:#0f0;}";
    echo ".container{max-width:800px;margin:0 auto;background:#111;padding:20px;border:1px solid #333;}";
    echo ".success{color:#0f0;} .error{color:#f00;} .info{color:#0ff;}";
    echo "pre{background:#222;padding:10px;border:1px solid #333;overflow-x:auto;}";
    echo "</style></head><body><div class='container'>";
}

echo "🍞 Olika - Executando Migrations\n";
echo str_repeat("=", 50) . "\n\n";

// Verificar se está no diretório correto
if (!file_exists('artisan')) {
    echo "❌ ERRO: Arquivo artisan não encontrado!\n";
    echo "Certifique-se de estar no diretório raiz do Laravel.\n";
    exit(1);
}

// Verificar .env
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "⚠️  Arquivo .env criado a partir do .env.example\n";
    } else {
        echo "❌ ERRO: Arquivo .env não encontrado!\n";
        exit(1);
    }
}

// Função para executar comandos
function runCommand($command) {
    global $isCli;
    
    if ($isCli) {
        echo "Executando: $command\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    $output = [];
    $return_code = 0;
    
    exec($command . " 2>&1", $output, $return_code);
    
    foreach ($output as $line) {
        if ($isCli) {
            echo $line . "\n";
        } else {
            echo "<pre>" . htmlspecialchars($line) . "</pre>";
        }
    }
    
    if ($return_code === 0) {
        if ($isCli) {
            echo "✅ Sucesso!\n";
        } else {
            echo "<div class='success'>✅ Sucesso!</div>";
        }
    } else {
        if ($isCli) {
            echo "❌ Erro! Código: $return_code\n";
        } else {
            echo "<div class='error'>❌ Erro! Código: $return_code</div>";
        }
    }
    
    if ($isCli) {
        echo str_repeat("-", 50) . "\n\n";
    }
    
    return $return_code === 0;
}

// Executar comandos
$success = true;

echo "1. Gerando chave da aplicação...\n";
if (!runCommand('php artisan key:generate --force')) {
    $success = false;
}

echo "2. Limpando cache...\n";
runCommand('php artisan config:clear');
runCommand('php artisan cache:clear');

echo "3. Executando migrations...\n";
if (!runCommand('php artisan migrate --force')) {
    $success = false;
}

echo "4. Executando seeders...\n";
if (!runCommand('php artisan db:seed --force')) {
    echo "⚠️  Aviso: Erro ao executar seeders, mas as migrations foram executadas\n";
}

echo "5. Otimizando cache...\n";
runCommand('php artisan config:cache');
runCommand('php artisan route:cache');

// Resultado final
echo str_repeat("=", 50) . "\n";

if ($success) {
    echo "🎉 SUCESSO! Sistema Olika configurado!\n";
    echo "\nPróximos passos:\n";
    echo "1. Configure as integrações no arquivo .env\n";
    echo "2. Acesse o cardápio: https://seudominio.com/\n";
    echo "3. Teste o sistema fazendo um pedido\n";
} else {
    echo "❌ ERRO! Houve problemas durante a execução.\n";
    echo "\nPossíveis soluções:\n";
    echo "1. Verifique as configurações do banco no .env\n";
    echo "2. Certifique-se de que o banco de dados existe\n";
    echo "3. Verifique as permissões do usuário do banco\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Executado em: " . date('Y-m-d H:i:s') . "\n";
echo "Sistema Olika - Pães Artesanais\n";

if (!$isCli) {
    echo "</div></body></html>";
}
?>
