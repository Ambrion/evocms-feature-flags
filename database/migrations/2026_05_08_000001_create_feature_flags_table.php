<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $prefix = config('database.connections.default.prefix', '');

        Schema::create($prefix . 'feature_flags', function (Blueprint $table) {
            $table->string('name', 100)->primary()->comment('Имя флага в snake_case');
            $table->json('default_value')->nullable()->comment('Значение по умолчанию: true, false, "A", "B", 123, null');
            $table->json('rules')->nullable()->comment('Правила: [{"condition":"...","value":true}]');
            $table->boolean('is_active')->default(true)->comment('Активен ли флаг');
            $table->boolean('log_statistics')->default(false)->comment('Записывать ли статистику использования этого флага');
            $table->text('description')->nullable()->comment('Описание для админки');
            $table->timestamps();

            $table->index(['is_active', 'name'], 'idx_active_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $prefix = config('database.connections.default.prefix', '');
        Schema::dropIfExists($prefix . 'feature_flags');
    }
};
