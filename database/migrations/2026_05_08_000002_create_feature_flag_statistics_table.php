<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $prefix = config('database.connections.mysql.prefix', '');

        Schema::create($prefix . 'feature_flag_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('flag_name', 100)->comment('Имя флага');
            $table->boolean('result')->comment('Результат оценки: true/false');
            $table->string('variant', 50)->nullable()->comment('Вариант: A, B, C или null');
            $table->decimal('weight', 4, 3)->nullable()->comment('Вес правила (0.000-1.000)');
            $table->string('matched_rule', 255)->nullable()->comment('Условие сработавшего правила (для отладки)');
            $table->json('context')->nullable()->comment('Полный контекст оценки (для отладки)');
            $table->char('context_hash', 32)->comment('MD5-хеш контекста');
            $table->string('ip', 45)->comment('IP пользователя (IPv6)');
            $table->timestamp('evaluated_at')->useCurrent()->comment('Время оценки');
            $table->index(['flag_name', 'evaluated_at'], 'idx_flag_time');
            $table->index(['flag_name', 'variant'], 'idx_flag_variant');
            $table->index(['context_hash', 'evaluated_at'], 'idx_context_time');
        });
    }

    public function down(): void
    {
        $prefix = config('database.connections.mysql.prefix', '');
        Schema::dropIfExists($prefix . 'feature_flag_statistics');
    }
};
