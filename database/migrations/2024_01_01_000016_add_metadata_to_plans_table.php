<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenancy_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('tenancy_plans', 'metadata')) {
                $table->json('metadata')->nullable()->after('limits');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenancy_plans', function (Blueprint $table) {
            if (Schema::hasColumn('tenancy_plans', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};

