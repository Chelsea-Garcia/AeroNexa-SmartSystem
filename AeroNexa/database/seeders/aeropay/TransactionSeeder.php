<?php

namespace Database\Seeders\aeropay;

use Illuminate\Database\Seeder;
use App\Models\aeropay\Transaction;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        Transaction::truncate();
        Transaction::factory()->count(10)->create();
        $this->command->info('Transactions seeded.');
    }
}
