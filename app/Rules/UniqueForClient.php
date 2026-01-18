<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

/**
 * Regra de validação para unicidade com escopo de client_id
 * 
 * Uso:
 * 'code' => ['required', new UniqueForClient('coupons', 'code')],
 * 'slug' => ['required', new UniqueForClient('categories', 'slug', $this->category?->id)],
 */
class UniqueForClient implements Rule
{
    protected string $table;
    protected string $column;
    protected ?int $ignoreId;
    protected ?int $clientId;

    /**
     * @param string $table Nome da tabela
     * @param string $column Nome da coluna a verificar
     * @param int|null $ignoreId ID a ignorar (para update)
     * @param int|null $clientId Client ID (usa o atual se não informado)
     */
    public function __construct(
        string $table, 
        string $column, 
        ?int $ignoreId = null,
        ?int $clientId = null
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->clientId = $clientId ?? currentClientId();
    }

    /**
     * Verifica se a regra passa
     */
    public function passes($attribute, $value): bool
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('client_id', $this->clientId);
        
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }
        
        return !$query->exists();
    }

    /**
     * Mensagem de erro
     */
    public function message(): string
    {
        return 'Este :attribute já está em uso.';
    }
}
