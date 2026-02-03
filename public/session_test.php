<?php
// Arquivo de diagnóstico de Sessão e Banco de Dados
// Coloque na pasta public e acesse via navegador

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Diagnóstico de Sessão</h1>";
echo "Host Atual: " . request()->getHost() . "<br>";
echo "Data: " . date('Y-m-d H:i:s') . "<br>";

// 1. Verificar conexão com DB
try {
    echo "<h3>1. Conexão com Banco de Dados</h3>";
    \DB::connection()->getPdo();
    echo "<span style='color:green'>Conexão OK!</span><br>";
    echo "Database: " . \DB::connection()->getDatabaseName() . "<br>";
} catch (\Exception $e) {
    echo "<span style='color:red'>Erro de conexão: " . $e->getMessage() . "</span>";
    die();
}

// 2. Verificar Tabela de Sessões
try {
    echo "<h3>2. Tabela de Sessões</h3>";
    if (\Schema::hasTable('sessions')) {
        echo "<span style='color:green'>Tabela 'sessions' EXISTE.</span><br>";

        $count = \DB::table('sessions')->count();
        echo "Sessões ativas no banco: " . $count . "<br>";

        // Tentar escrever
        $sessionId = \Session::getId();
        echo "ID da Sessão Atual (Framework): " . $sessionId . "<br>";

        // Verificar se existe registro para esta sessão
        $exists = \DB::table('sessions')->where('id', $sessionId)->exists();
        if ($exists) {
            echo "<span style='color:green'>Registro de sessão encontrado no banco para este ID.</span><br>";
        } else {
            echo "<span style='color:orange'>Registro de sessão NÃO encontrado no banco para este ID (pode ser criado no final da requisição).</span><br>";
        }

    } else {
        echo "<span style='color:red'>Tabela 'sessions' NÃO EXISTE!</span><br>";
        echo "Recomendação: Execute a migration para criar a tabela de sessões.<br>";
        echo "<pre>php artisan session:table\nphp artisan migrate</pre>";
    }
} catch (\Exception $e) {
    echo "<span style='color:red'>Erro ao verificar tabela: " . $e->getMessage() . "</span>";
}

// 3. Configuração de Sessão
echo "<h3>3. Configuração de Sessão</h3>";
$config = config('session');
echo "Driver: " . $config['driver'] . "<br>";
echo "Domain: " . var_export($config['domain'], true) . "<br>";
echo "Secure: " . var_export($config['secure'], true) . "<br>";
echo "Same Site: " . $config['same_site'] . "<br>";
echo "Cookie Name: " . $config['cookie'] . "<br>";

// 4. Teste de escrita manual
try {
    echo "<h3>4. Teste de Escrita Manual</h3>";
    \Session::put('diagnostic_test', time());
    \Session::save();
    echo "Sessão salva. Recarregue a página para verificar persistência.<br>";

    if (\Session::has('diagnostic_test')) {
        echo "Valor recuperado da sessão: " . \Session::get('diagnostic_test') . "<br>";
        echo "<span style='color:green'>Persistência de sessão parece estar funcionando na memória.</span>";
    }
} catch (\Exception $e) {
    echo "<span style='color:red'>Erro ao manipular sessão: " . $e->getMessage() . "</span>";
}
