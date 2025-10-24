<?php
/**
 * Script simples para executar migrations
 * Execute via navegador: https://seudominio.com/migrate_simple.php
 */

// Configurações básicas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Olika - Migrations</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}";
echo ".container{max-width:800px;margin:0 auto;background:white;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
echo ".success{color:green;background:#e6ffe6;padding:10px;border:1px solid green;border-radius:4px;margin:10px 0;}";
echo ".error{color:red;background:#ffe6e6;padding:10px;border:1px solid red;border-radius:4px;margin:10px 0;}";
echo ".info{color:blue;background:#e6f3ff;padding:10px;border:1px solid blue;border-radius:4px;margin:10px 0;}";
echo "pre{background:#f8f8f8;padding:10px;border-radius:4px;overflow-x:auto;}";
echo "</style></head><body>";

echo "<div class='container'>";
echo "<h1>🍞 Olika - Executando Migrations</h1>";

// Verificar se está no diretório correto
if (!file_exists('artisan')) {
    echo "<div class='error'>❌ Arquivo artisan não encontrado. Certifique-se de estar no diretório raiz do Laravel.</div>";
    echo "</div></body></html>";
    exit;
}

// Verificar se o .env existe
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "<div class='info'>⚠️ Arquivo .env criado a partir do .env.example</div>";
    } else {
        echo "<div class='error'>❌ Arquivo .env não encontrado e não há .env.example</div>";
        echo "</div></body></html>";
        exit;
    }
}

// Função para executar comandos
function executeCommand($command) {
    $output = [];
    $return_code = 0;
    
    exec($command . " 2>&1", $output, $return_code);
    
    return [
        'output' => $output,
        'return_code' => $return_code
    ];
}

echo "<h2>🚀 Executando comandos...</h2>";

// 1. Gerar chave da aplicação
echo "<h3>1. Gerando chave da aplicação</h3>";
$result = executeCommand('php artisan key:generate --force');
if ($result['return_code'] === 0) {
    echo "<div class='success'>✅ Chave gerada com sucesso</div>";
} else {
    echo "<div class='error'>❌ Erro ao gerar chave</div>";
    echo "<pre>" . implode("\n", $result['output']) . "</pre>";
}

// 2. Limpar cache
echo "<h3>2. Limpando cache</h3>";
executeCommand('php artisan config:clear');
executeCommand('php artisan cache:clear');
echo "<div class='success'>✅ Cache limpo</div>";

// 3. Executar migrations
echo "<h3>3. Executando migrations</h3>";
$result = executeCommand('php artisan migrate --force');
if ($result['return_code'] === 0) {
    echo "<div class='success'>✅ Migrations executadas com sucesso</div>";
    echo "<pre>" . implode("\n", $result['output']) . "</pre>";
} else {
    echo "<div class='error'>❌ Erro ao executar migrations</div>";
    echo "<pre>" . implode("\n", $result['output']) . "</pre>";
    echo "</div></body></html>";
    exit;
}

// 4. Executar seeders
echo "<h3>4. Executando seeders</h3>";
$result = executeCommand('php artisan db:seed --force');
if ($result['return_code'] === 0) {
    echo "<div class='success'>✅ Seeders executados com sucesso</div>";
    echo "<pre>" . implode("\n", $result['output']) . "</pre>";
} else {
    echo "<div class='error'>❌ Erro ao executar seeders</div>";
    echo "<pre>" . implode("\n", $result['output']) . "</pre>";
}

// 5. Otimizar cache
echo "<h3>5. Otimizando cache</h3>";
executeCommand('php artisan config:cache');
executeCommand('php artisan route:cache');
echo "<div class='success'>✅ Cache otimizado</div>";

// Resultado final
echo "<hr>";
echo "<h2>🎉 Concluído!</h2>";
echo "<div class='success'>";
echo "<h3>✅ Sistema Olika configurado com sucesso!</h3>";
echo "<p>O sistema está pronto para uso. Você pode:</p>";
echo "<ul>";
echo "<li><a href='/'>Acessar o cardápio</a></li>";
echo "<li><a href='/menu'>Ver o menu completo</a></li>";
echo "<li>Configurar as integrações no arquivo .env</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📋 Próximos passos:</h3>";
echo "<ol>";
echo "<li>Configure as chaves do MercadoPago no arquivo .env</li>";
echo "<li>Configure a API do WhatsApp no arquivo .env</li>";
echo "<li>Teste o sistema fazendo um pedido</li>";
echo "<li>Configure as notificações WhatsApp</li>";
echo "</ol>";

echo "<h3>🔧 Configurações necessárias no .env:</h3>";
echo "<pre>";
echo "# MercadoPago\n";
echo "MERCADOPAGO_ACCESS_TOKEN=seu_token_aqui\n";
echo "MERCADOPAGO_PUBLIC_KEY=sua_chave_publica_aqui\n";
echo "MERCADOPAGO_ENV=sandbox\n\n";
echo "# WhatsApp\n";
echo "WHATSAPP_API_URL=sua_api_whatsapp_aqui\n";
echo "WHATSAPP_API_KEY=sua_chave_whatsapp_aqui\n";
echo "</pre>";

echo "<p style='text-align:center;color:#666;font-size:12px;margin-top:30px;'>";
echo "Sistema Olika - Pães Artesanais | " . date('Y-m-d H:i:s');
echo "</p>";

echo "</div></body></html>";
?>
