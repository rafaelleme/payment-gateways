<?php

declare(strict_types=1);

namespace Rafaelleme\PaymentGateways\Laravel\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'payment-gateways:install
                            {--force : Sobrescrever arquivos existentes}
                            {--without-migrations : Não publicar as migrations}';

    protected $description = 'Instala o pacote payment-gateways: publica config e migrations';

    public function handle(): void
    {
        $this->info('');
        $this->info('  ██████╗  █████╗ ██╗   ██╗███╗   ███╗███████╗███╗   ██╗████████╗');
        $this->info('  ██╔══██╗██╔══██╗╚██╗ ██╔╝████╗ ████║██╔════╝████╗  ██║╚══██╔══╝');
        $this->info('  ██████╔╝███████║ ╚████╔╝ ██╔████╔██║█████╗  ██╔██╗ ██║   ██║   ');
        $this->info('  ██╔═══╝ ██╔══██║  ╚██╔╝  ██║╚██╔╝██║██╔══╝  ██║╚██╗██║   ██║   ');
        $this->info('  ██║     ██║  ██║   ██║   ██║ ╚═╝ ██║███████╗██║ ╚████║   ██║   ');
        $this->info('  ╚═╝     ╚═╝  ╚═╝   ╚═╝   ╚═╝     ╚═╝╚══════╝╚═╝  ╚═══╝   ╚═╝   ');
        $this->info('');
        $this->info('  <fg=green>Payment Gateways</> — Instalação do pacote');
        $this->info('');

        $force = (bool) $this->option('force');

        $this->publishConfig($force);

        if (! $this->option('without-migrations')) {
            $this->publishMigrations($force);
        }

        $this->displayEnvInstructions();
        $this->displayNextSteps();

        $this->info('');
        $this->info('  <fg=green>✔ Instalação concluída!</>');
        $this->info('');

        return;
    }

    private function publishConfig(bool $force): void
    {
        $this->info('  → Publicando arquivo de configuração...');

        $params = ['--tag' => 'payment-gateways-config'];

        if ($force) {
            $params['--force'] = true;
        }

        $this->callSilently('vendor:publish', $params);

        $destination = config_path('payment-gateways.php');

        if (file_exists($destination)) {
            $this->line('  <fg=green>  ✔ config/payment-gateways.php publicado.</>');
        } else {
            $this->line('  <fg=yellow>  ⚠ Não foi possível publicar a configuração. Verifique manualmente.</>');
        }
    }

    private function publishMigrations(bool $force): void
    {
        $this->info('  → Publicando migrations...');

        $params = ['--tag' => 'payment-gateways-migrations'];

        if ($force) {
            $params['--force'] = true;
        }

        $this->callSilently('vendor:publish', $params);

        $migrations = [
            'create_gateway_customers_table',
            'create_gateway_subscriptions_table',
            'create_gateway_payments_table',
            'add_failed_at_to_gateway_subscriptions_table',
        ];

        foreach ($migrations as $migration) {
            $this->line("  <fg=green>  ✔ Migration [{$migration}] publicada.</>");
        }

        $this->line('');
        $this->line('  <fg=yellow>  Lembre-se de rodar: php artisan migrate</>');
    }

    private function displayEnvInstructions(): void
    {
        $this->info('');
        $this->info('  ──────────────────────────────────────────────────');
        $this->info('  Adicione as seguintes variáveis ao seu .env:');
        $this->info('  ──────────────────────────────────────────────────');
        $this->line('');
        $this->line('  <fg=cyan># Payment Gateways</>');
        $this->line('  <fg=cyan>PAYMENT_GATEWAY_DEFAULT=asaas</>');
        $this->line('  <fg=cyan>PAYMENT_GATEWAY_GRACE_PERIOD_DAYS=3</>');
        $this->line('');
        $this->line('  <fg=cyan># Asaas</>');
        $this->line('  <fg=cyan>ASAAS_API_KEY=your_api_key_here</>');
        $this->line('  <fg=cyan>ASAAS_BASE_URL=https://api.asaas.com</>');
        $this->line('  <fg=cyan># Sandbox: ASAAS_BASE_URL=https://api-sandbox.asaas.com</>');
        $this->line('');
        $this->info('  ──────────────────────────────────────────────────');
    }

    private function displayNextSteps(): void
    {
        $this->info('');
        $this->info('  Próximos passos:');
        $this->line('');
        $this->line('  1. Configure as variáveis de ambiente no .env');
        $this->line('  2. Execute: <fg=yellow>php artisan migrate</>');
        $this->line('  3. (Opcional) Configure o webhook no painel do Asaas:');
        $this->line('     URL: <fg=cyan>https://seu-dominio.com/webhooks/asaas</>');
        $this->line('');
        $this->line('  Documentação: https://github.com/rafaelleme/payment-gateways');
    }
}
