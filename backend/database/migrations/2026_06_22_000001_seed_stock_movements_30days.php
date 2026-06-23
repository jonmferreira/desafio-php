<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Lógica movida para DemoMovementsSeeder — migrations rodam antes dos seeders
        // e não encontrariam usuários/produtos ainda populados.
    }

    public function down(): void
    {
        // migration de dados — não há rollback destrutivo seguro
    }
};
