<?php
/**
 * Script para executar migrations do Laravel
 * Execute este arquivo diretamente no servidor via navegador ou linha de comando
 * 
 * URL: https://seudominio.com/run_migrations.php
 * OU: php run_migrations.php
 */

// Configurações
$output = [];
$success = true;
$errors = [];

echo "<h1>🍞 Olika - Executando Migrations</h1>";
echo "<hr>";

// Verificar se está no diretório correto
if (!file_exists('artisan')) {
    $errors[] = "Arquivo artisan não encontrado. Certifique-se de estar no diretório raiz do Laravel.";
    $success = false;
}

if (!$success) {
    echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<h3>❌ Erros encontrados:</h3>";
    foreach ($errors as $error) {
        echo "<p>• $error</p>";
    }
    echo "</div>";
    exit;
}

// Função para executar comandos
function runCommand($command, $description) {
    global $output;
    
    echo "<h3>🔄 $description</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-left: 4px solid #007cba;'>";
    
    $result = [];
    $return_code = 0;
    
    exec($command . " 2>&1", $result, $return_code);
    
    $output[] = [
        'command' => $command,
        'description' => $description,
        'result' => $result,
        'return_code' => $return_code
    ];
    
    foreach ($result as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    
    echo "</pre>";
    
    if ($return_code !== 0) {
        echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
        echo "❌ Erro ao executar: $description";
        echo "</div>";
        return false;
    } else {
        echo "<div style='color: green; background: #e6ffe6; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✅ Sucesso: $description";
        echo "</div>";
        return true;
    }
}

// Verificar se o PHP tem as extensões necessárias
echo "<h3>🔍 Verificando extensões PHP</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "<div style='color: red; background: #ffe6e6; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<h4>❌ Extensões PHP faltando:</h4>";
    foreach ($missing_extensions as $ext) {
        echo "<p>• $ext</p>";
    }
    echo "</div>";
} else {
    echo "<div style='color: green; background: #e6ffe6; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "✅ Todas as extensões necessárias estão instaladas";
    echo "</div>";
}

// Verificar arquivo .env
echo "<h3>🔍 Verificando configuração</h3>";
if (!file_exists('.env')) {
    echo "<div style='color: orange; background: #fff3cd; padding: 10px; border: 1px solid orange; margin: 10px 0;'>";
    echo "⚠️ Arquivo .env não encontrado. Copiando de .env.example...";
    echo "</div>";
    
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "<div style='color: green; background: #e6ffe6; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
        echo "✅ Arquivo .env criado com sucesso";
        echo "</div>";
    }
}

// Executar comandos
echo "<h2>🚀 Executando comandos do Laravel</h2>";

// 1. Gerar chave da aplicação
if (!runCommand('php artisan key:generate --force', 'Gerando chave da aplicação')) {
    $success = false;
}

// 2. Limpar cache
runCommand('php artisan config:clear', 'Limpando cache de configuração');
runCommand('php artisan cache:clear', 'Limpando cache da aplicação');

// 3. Executar migrations
if (!runCommand('php artisan migrate --force', 'Executando migrations')) {
    $success = false;
}

// 4. Executar seeders
if ($success) {
    if (!runCommand('php artisan db:seed --force', 'Executando seeders')) {
        echo "<div style='color: orange; background: #fff3cd; padding: 10px; border: 1px solid orange; margin: 10px 0;'>";
        echo "⚠️ Erro ao executar seeders, mas as migrations foram executadas com sucesso";
        echo "</div>";
    }
}

// 5. Limpar cache final
runCommand('php artisan config:cache', 'Cacheando configurações');
runCommand('php artisan route:cache', 'Cacheando rotas');

// Resultado final
echo "<hr>";
echo "<h2>📊 Resumo da Execução</h2>";

if ($success) {
    echo "<div style='color: green; background: #e6ffe6; padding: 20px; border: 2px solid green; margin: 20px 0; text-align: center;'>";
    echo "<h2>🎉 SUCESSO!</h2>";
    echo "<p>As migrations foram executadas com sucesso!</p>";
    echo "<p>O sistema Olika está pronto para uso.</p>";
    echo "</div>";
    
    echo "<h3>✅ O que foi executado:</h3>";
    echo "<ul>";
    echo "<li>✅ Chave da aplicação gerada</li>";
    echo "<li>✅ Cache limpo</li>";
    echo "<li>✅ Migrations executadas</li>";
    echo "<li>✅ Seeders executados</li>";
    echo "<li>✅ Cache otimizado</li>";
    echo "</ul>";
    
    echo "<h3>🌐 Próximos passos:</h3>";
    echo "<ol>";
    echo "<li>Acesse o cardápio: <a href='/'>Ver Cardápio</a></li>";
    echo "<li>Configure as integrações no arquivo .env</li>";
    echo "<li>Teste o sistema de pedidos</li>";
    echo "</ol>";
    
} else {
    echo "<div style='color: red; background: #ffe6e6; padding: 20px; border: 2px solid red; margin: 20px 0; text-align: center;'>";
    echo "<h2>❌ ERRO!</h2>";
    echo "<p>Houve erros durante a execução das migrations.</p>";
    echo "</div>";
    
    echo "<h3>🔧 Possíveis soluções:</h3>";
    echo "<ul>";
    echo "<li>Verifique as configurações do banco de dados no arquivo .env</li>";
    echo "<li>Certifique-se de que o banco de dados existe</li>";
    echo "<li>Verifique as permissões do usuário do banco</li>";
    echo "<li>Execute os comandos manualmente via SSH se possível</li>";
    echo "</ul>";
}

// Informações de debug
echo "<details style='margin-top: 20px;'>";
echo "<summary style='cursor: pointer; font-weight: bold;'>🔍 Detalhes técnicos</summary>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin-top: 10px;'>";
echo "PHP Version: " . phpversion() . "\n";
echo "Laravel Version: " . (file_exists('vendor/laravel/framework/src/Illuminate/Foundation/Application.php') ? 'Detectado' : 'Não detectado') . "\n";
echo "Diretório atual: " . getcwd() . "\n";
echo "Usuário: " . get_current_user() . "\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";
echo "</details>";

// Log dos comandos executados
echo "<details style='margin-top: 20px;'>";
echo "<summary style='cursor: pointer; font-weight: bold;'>📝 Log dos comandos</summary>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin-top: 10px;'>";
foreach ($output as $item) {
    echo "Comando: {$item['command']}\n";
    echo "Descrição: {$item['description']}\n";
    echo "Código de retorno: {$item['return_code']}\n";
    echo "Resultado:\n";
    foreach ($item['result'] as $line) {
        echo "  " . htmlspecialchars($line) . "\n";
    }
    echo "\n" . str_repeat('-', 50) . "\n\n";
}
echo "</pre>";
echo "</details>";

echo "<hr>";
echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Script executado em " . date('Y-m-d H:i:s') . " | Sistema Olika - Pães Artesanais";
echo "</p>";
?>
