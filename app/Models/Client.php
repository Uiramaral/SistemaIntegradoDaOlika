<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Model Client - Representa um cliente/tenant do sistema SaaS
 * 
 * Cada client é uma loja/negócio separado que usa o sistema.
 * Todas as entidades principais (customers, orders, products, etc) 
 * são isoladas por client_id.
 * 
 * IMPORTANTE: O slug é IMUTÁVEL após criação!
 */
class Client extends Model
{
    use HasFactory;

    /**
     * Slugs reservados que não podem ser usados
     */
    protected const RESERVED_SLUGS = [
        'admin', 'api', 'www', 'mail', 'smtp', 'ftp', 'ssh', 'app',
        'olika', 'sistema', 'painel', 'dashboard', 'login', 'register',
        'cadastro', 'suporte', 'help', 'blog', 'static', 'assets',
        'cdn', 'img', 'images', 'css', 'js', 'fonts', 'media',
    ];

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'plan',
        'instance_url',
        'deploy_status',
        'whatsapp_phone',
        'notificacao_whatsapp',
        'active',
        'subscription_id',
        'is_trial',
        'trial_started_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_trial' => 'boolean',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    // =========================================================================
    // BOOT - Eventos do Model
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        // Antes de criar: gerar slug se não fornecido
        static::creating(function (Client $client) {
            if (empty($client->slug)) {
                $client->slug = static::generateUniqueSlug($client->name);
            } else {
                // Normalizar slug fornecido
                $client->slug = Str::slug($client->slug);
            }

            // Validar slug
            if (!static::isSlugAvailable($client->slug)) {
                throw new \InvalidArgumentException(
                    "O slug '{$client->slug}' já está em uso ou é reservado."
                );
            }
        });

        // Ao atualizar: impedir mudança do slug
        static::updating(function (Client $client) {
            if ($client->isDirty('slug') && $client->getOriginal('slug') !== null) {
                throw new \InvalidArgumentException(
                    "O slug não pode ser alterado após a criação. Slug atual: '{$client->getOriginal('slug')}'"
                );
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS - Entidades que pertencem a este cliente
    // =========================================================================

    /**
     * Clientes (compradores) desta loja
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Pedidos desta loja
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Produtos desta loja
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Categorias desta loja
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Configurações desta loja
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    /**
     * Cupons desta loja
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Tokens de API deste cliente
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Instâncias WhatsApp deste cliente
     */
    public function whatsappInstances(): HasMany
    {
        return $this->hasMany(WhatsappInstance::class);
    }

    /**
     * URLs de instâncias WhatsApp atribuídas a este cliente (Railway)
     */
    public function whatsappInstanceUrls(): HasMany
    {
        return $this->hasMany(WhatsappInstanceUrl::class);
    }

    /**
     * Campanhas WhatsApp deste cliente
     */
    public function whatsappCampaigns(): HasMany
    {
        return $this->hasMany(WhatsappCampaign::class);
    }

    /**
     * Programas de fidelidade deste cliente
     */
    public function loyaltyPrograms(): HasMany
    {
        return $this->hasMany(LoyaltyProgram::class);
    }

    /**
     * Taxas de entrega deste cliente
     */
    public function deliveryFees(): HasMany
    {
        return $this->hasMany(DeliveryFee::class);
    }

    /**
     * Logs de deploy deste cliente
     */
    public function deploymentLogs(): HasMany
    {
        return $this->hasMany(DeploymentLog::class);
    }

    /**
     * Assinatura ativa do cliente
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Todas as assinaturas do cliente
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Usuários deste cliente
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Apenas clientes ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Apenas clientes em trial
     */
    public function scopeTrial($query)
    {
        return $query->where('is_trial', true);
    }

    /**
     * Clientes com trial expirado
     */
    public function scopeTrialExpired($query)
    {
        return $query->where('is_trial', true)
                     ->where('trial_ends_at', '<', now());
    }

    /**
     * Clientes por plano
     */
    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Verifica se o trial expirou
     */
    public function getIsTrialExpiredAttribute(): bool
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return false;
        }
        return $this->trial_ends_at->isPast();
    }

    /**
     * Dias restantes do trial
     */
    public function getTrialDaysRemainingAttribute(): ?int
    {
        if (!$this->is_trial || !$this->trial_ends_at) {
            return null;
        }
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * URL completa do cardápio (subdomínio)
     */
    public function getMenuUrlAttribute(): string
    {
        return "https://{$this->slug}.menuonline.com.br";
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Buscar cliente pelo slug (subdomínio)
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Obter settings do cliente (singleton por client)
     */
    public function getSetting(): ?Setting
    {
        return $this->settings()->first();
    }

    /**
     * Verificar se cliente pode usar recursos (ativo e não expirado)
     */
    public function canOperate(): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    // =========================================================================
    // SLUG METHODS - Gerenciamento de slug (subdomain)
    // =========================================================================

    /**
     * Verifica se um slug está disponível para uso
     * 
     * @param string $slug Slug a verificar
     * @param int|null $excludeId ID do client a excluir da verificação (para updates)
     * @return bool
     */
    public static function isSlugAvailable(string $slug, ?int $excludeId = null): bool
    {
        $slug = Str::slug($slug);

        // Verificar se é reservado
        if (in_array($slug, self::RESERVED_SLUGS)) {
            return false;
        }

        // Verificar tamanho mínimo
        if (strlen($slug) < 3) {
            return false;
        }

        // Verificar se já existe no banco
        $query = static::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Gera um slug único baseado no nome
     * 
     * @param string $name Nome do estabelecimento
     * @return string Slug único
     */
    public static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        
        // Garantir tamanho mínimo
        if (strlen($baseSlug) < 3) {
            $baseSlug = $baseSlug . '-loja';
        }

        $slug = $baseSlug;
        $counter = 1;

        // Adicionar número até encontrar um disponível
        while (!static::isSlugAvailable($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            // Segurança: evitar loop infinito
            if ($counter > 100) {
                $slug = $baseSlug . '-' . Str::random(6);
                break;
            }
        }

        return $slug;
    }

    /**
     * Valida formato do slug
     * 
     * @param string $slug
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public static function validateSlugFormat(string $slug): array
    {
        $slug = Str::slug($slug);

        if (strlen($slug) < 3) {
            return [
                'valid' => false,
                'message' => 'O slug deve ter no mínimo 3 caracteres.',
            ];
        }

        if (strlen($slug) > 30) {
            return [
                'valid' => false,
                'message' => 'O slug deve ter no máximo 30 caracteres.',
            ];
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            return [
                'valid' => false,
                'message' => 'O slug pode conter apenas letras minúsculas, números e hífens.',
            ];
        }

        if (in_array($slug, self::RESERVED_SLUGS)) {
            return [
                'valid' => false,
                'message' => 'Este nome está reservado e não pode ser usado.',
            ];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Verifica disponibilidade e retorna resposta formatada
     * 
     * @param string $slug
     * @return array
     */
    public static function checkSlugAvailability(string $slug): array
    {
        $normalized = Str::slug($slug);
        
        // Validar formato primeiro
        $formatCheck = static::validateSlugFormat($normalized);
        if (!$formatCheck['valid']) {
            return [
                'available' => false,
                'slug' => $normalized,
                'message' => $formatCheck['message'],
            ];
        }

        // Verificar disponibilidade
        $isAvailable = static::isSlugAvailable($normalized);

        if ($isAvailable) {
            return [
                'available' => true,
                'slug' => $normalized,
                'url' => "https://{$normalized}.menuonline.com.br",
                'message' => 'Este endereço está disponível!',
            ];
        }

        // Sugerir alternativas
        $suggestions = [];
        for ($i = 1; $i <= 3; $i++) {
            $suggestion = $normalized . '-' . $i;
            if (static::isSlugAvailable($suggestion)) {
                $suggestions[] = $suggestion;
            }
        }

        return [
            'available' => false,
            'slug' => $normalized,
            'message' => 'Este endereço já está em uso.',
            'suggestions' => $suggestions,
        ];
    }
}
