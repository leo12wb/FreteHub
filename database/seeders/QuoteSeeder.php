<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\QuoteOption;
use App\Models\Store;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::first();

        if (! $store) {
            return;
        }

        // Cotação pendente com opções de todas as transportadoras
        $quote = Quote::factory()->pending()->create(['store_id' => $store->id]);

        $options = [
            ['carrier' => 'Correios',       'service' => 'PAC',        'price_cents' => 1850, 'delivery_days' => 7],
            ['carrier' => 'Correios',       'service' => 'SEDEX',      'price_cents' => 3250, 'delivery_days' => 2],
            ['carrier' => 'JadLog',         'service' => 'Econômico',  'price_cents' => 2100, 'delivery_days' => 5],
            ['carrier' => 'Total Express',  'service' => 'Standard',   'price_cents' => 2480, 'delivery_days' => 3],
        ];

        foreach ($options as $option) {
            QuoteOption::create(array_merge($option, ['quote_id' => $quote->id]));
        }

        $this->command->info("Cotação de exemplo criada: {$quote->id}");
    }
}
