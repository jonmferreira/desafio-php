<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('peso', 10, 3)->default(0)->after('unit');
            $table->unsignedInteger('min_fardos')->default(0)->after('peso');
            $table->dropColumn('min_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('min_quantity')->default(0)->after('unit');
            $table->dropColumn(['peso', 'min_fardos']);
        });
    }
};
