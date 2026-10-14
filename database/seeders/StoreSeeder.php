<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        // Loja principal para testes manuais
        $loja1 = Store::create([
            'name'     => 'Loja Exemplo',
            'email'    => 'loja@exemplo.com',
            'password' => Hash::make('password'),
        ]);

        $token1 = $loja1->createToken('dev-token')->plainTextToken;

        $this->command->info("Loja criada: {$loja1->email}");
        $this->command->info("Token: {$token1}");

        // Segunda loja com webhook configurado
        $loja2 = Store::create([
            'name'           => 'E-commerce Teste',
            'email'          => 'ecommerce@teste.com',
            'password'       => Hash::make('password'),
            'webhook_url'    => 'https://webhook.site/test',
            'webhook_secret' => 'super-secret-key-32-characters!!',
        ]);

        $token2 = $loja2->createToken('dev-token')->plainTextToken;

        $this->command->info("Loja criada: {$loja2->email}");
        $this->command->info("Token: {$token2}");
    }
}
