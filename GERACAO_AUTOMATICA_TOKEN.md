# ✅ Geração Automática de Token - Implementado

## 🎯 Status

✅ **IMPLEMENTADO** - Token é gerado automaticamente quando um novo cliente é criado.

---

## 📋 Como Funciona

### 1. Geração Automática no Model Client

Quando um novo cliente é criado via Laravel (`Client::create()` ou `Client::save()`), o método `booted()` é acionado e gera automaticamente um token único.

**Arquivo:** `app/Models/Client.php`

```php
protected static function booted()
{
    static::created(function ($client) {
        // Gera token único de 64 caracteres
        $token = self::generateUniqueToken();
        
        // Cria token de API para o cliente
        ApiToken::create([
            'client_id' => $client->id,
            'token' => $token,
            'expires_at' => null, // Sem expiração
        ]);
    });
}
```

### 2. Método de Geração de Token

O token é gerado usando `Str::random(64)` do Laravel, garantindo unicidade:

```php
private static function generateUniqueToken(): string
{
    do {
        $token = Str::random(64);
    } while (ApiToken::where('token', $token)->exists());
    
    return $token;
}
```

### 3. Regenerar Token (Opcional)

Se precisar gerar um novo token para um cliente existente:

```php
$client = Client::find(1);
$newToken = $client->regenerateApiToken();
```

---

## 🔧 Exemplo de Uso

### Criar Novo Cliente (Token gerado automaticamente):

```php
$client = Client::create([
    'name' => 'Novo Cliente',
    'slug' => 'novo-cliente',
    'plan' => 'ia',
    'active' => true,
]);

// Token já foi gerado automaticamente!
$token = $client->activeApiToken->token;
echo "Token: {$token}";
```

### Obter Token do Cliente:

```php
$client = Client::find(1);
$token = $client->activeApiToken->token;
```

---

## 📝 Tabela api_tokens

A tabela já existe no SQL principal (`olika_multi_instance_full_update.sql`):

```sql
CREATE TABLE IF NOT EXISTS api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(80) UNIQUE NOT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tokens_clients FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

---

## ✅ Vantagens

- ✅ **Automático**: Token gerado sem intervenção manual
- ✅ **Único**: Garantia de não duplicação
- ✅ **Seguro**: Token de 64 caracteres aleatórios
- ✅ **Rastreável**: Log registrado quando token é gerado
- ✅ **Flexível**: Pode regenerar token quando necessário

---

## 🚀 Pronto!

Sempre que criar um novo cliente, o token será gerado automaticamente! 🎉

