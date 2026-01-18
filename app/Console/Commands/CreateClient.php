<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateClient extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'olika:create-client 
                            {name : Nome do estabelecimento}
                            {--slug= : Slug para subdomínio (gerado automaticamente se não informado)}
                            {--plan=basic : Plano (basic ou ia)}
                            {--admin-email= : Email do admin (opcional)}
                            {--admin-password= : Senha do admin (opcional, gera automaticamente)}
                            {--trial : Criar como trial de 14 dias}';

    /**
     * The console command description.
     */
    protected $description = 'Cria um novo cliente (tenant) no sistema SaaS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $slug = $this->option('slug') ?: Str::slug($name);
        $plan = $this->option('plan');
        $isTrial = $this->option('trial');

        // Verificar se slug já existe
        if (Client::where('slug', $slug)->exists()) {
            $this->error("❌ Já existe um cliente com o slug '{$slug}'");
            return 1;
        }

        $this->info("🚀 Criando cliente: {$name}");
        $this->info("   Slug: {$slug}");
        $this->info("   Plano: {$plan}");

        // Criar cliente
        $client = Client::create([
            'name' => $name,
            'slug' => $slug,
            'plan' => $plan,
            'active' => true,
            'is_trial' => $isTrial,
            'trial_started_at' => $isTrial ? now() : null,
            'trial_ends_at' => $isTrial ? now()->addDays(14) : null,
        ]);

        $this->info("✅ Cliente criado com ID: {$client->id}");

        // Criar configurações iniciais
        Setting::create([
            'client_id' => $client->id,
            'business_name' => $name,
            'is_open' => true,
            'primary_color' => '#FF6B35',
        ]);

        $this->info("✅ Configurações iniciais criadas");

        // Criar admin se email informado
        $adminEmail = $this->option('admin-email');
        if ($adminEmail) {
            $password = $this->option('admin-password') ?: Str::random(12);
            
            $user = User::create([
                'client_id' => $client->id,
                'name' => 'Admin ' . $name,
                'email' => $adminEmail,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]);

            $this->info("✅ Usuário admin criado:");
            $this->info("   Email: {$adminEmail}");
            $this->info("   Senha: {$password}");
            $this->warn("   ⚠️  Guarde essa senha! Ela não será exibida novamente.");
        }

        // Exibir resumo
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════");
        $this->info("✅ CLIENTE CRIADO COM SUCESSO!");
        $this->info("═══════════════════════════════════════════════════");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $client->id],
                ['Nome', $client->name],
                ['Slug', $client->slug],
                ['URL do Cardápio', "https://{$client->slug}.menuonline.com.br"],
                ['Plano', $client->plan],
                ['Trial', $isTrial ? 'Sim (14 dias)' : 'Não'],
                ['Status', 'Ativo'],
            ]
        );

        if ($isTrial) {
            $this->warn("📅 O trial expira em: " . $client->trial_ends_at->format('d/m/Y H:i'));
        }

        return 0;
    }
}
