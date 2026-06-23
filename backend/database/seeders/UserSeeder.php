<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! User::query()->where('cpf', '11144477735')->exists()) {
            User::factory()->admin()->create([
                'name' => 'Administrador Systock',
                'cpf' => '11144477735',
                'email' => 'admin@systock.com.br',
            ]);
        }

        if (! User::query()->where('cpf', '33366699957')->exists()) {
            User::factory()->create([
                'name' => 'Operador Systock',
                'cpf' => '33366699957',
                'email' => 'operador@systock.com.br',
            ]);
        }
    }
}
