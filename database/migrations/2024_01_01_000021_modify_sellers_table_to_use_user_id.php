<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sellers')) {
            // Si la tabla no existe, crear la nueva estructura
            Schema::create('sellers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('code')->unique();
                $table->decimal('commission_rate', 5, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id']);
                $table->index(['code']);
                $table->index(['is_active']);
            });
            return;
        }

        // Si la tabla existe, modificarla
        Schema::table('sellers', function (Blueprint $table) {
            // Verificar si tiene las columnas antiguas
            if (Schema::hasColumn('sellers', 'name') || Schema::hasColumn('sellers', 'email')) {
                // Eliminar índices antiguos si existen
                try {
                    $table->dropIndex(['email']);
                } catch (\Exception $e) {
                    // El índice puede no existir
                }

                // Eliminar columnas antiguas
                if (Schema::hasColumn('sellers', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('sellers', 'email')) {
                    $table->dropColumn('email');
                }
            }

            // Agregar user_id si no existe
            if (!Schema::hasColumn('sellers', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
                $table->index(['user_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Eliminar user_id y restaurar columnas antiguas
            if (Schema::hasColumn('sellers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }

            // Restaurar columnas antiguas si no existen
            if (!Schema::hasColumn('sellers', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('sellers', 'email')) {
                $table->string('email')->unique()->after('name');
                $table->index(['email']);
            }
        });
    }
};




