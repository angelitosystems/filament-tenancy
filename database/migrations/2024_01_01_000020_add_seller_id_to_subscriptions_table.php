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
        Schema::table('tenancy_subscriptions', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('plan_id')->constrained('sellers')->onDelete('set null');
            $table->string('payment_link')->nullable()->after('external_id'); // Link de pago PayPal generado
            $table->timestamp('payment_link_expires_at')->nullable()->after('payment_link');
            
            $table->index(['seller_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenancy_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn(['seller_id', 'payment_link', 'payment_link_expires_at']);
        });
    }
};

