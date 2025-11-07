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
            $table->string('payment_method')->nullable()->after('status');
            $table->string('external_id')->nullable()->after('payment_method');
            $table->decimal('price', 10, 2)->nullable()->after('external_id');
            $table->string('billing_cycle')->nullable()->after('price');
            $table->boolean('auto_renew')->default(true)->after('billing_cycle');
            $table->timestamp('next_billing_at')->nullable()->after('auto_renew');
            $table->text('notes')->nullable()->after('canceled_reason');
            
            $table->index(['payment_method']);
            $table->index(['external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenancy_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['external_id']);
            $table->dropIndex(['payment_method']);
            
            $table->dropColumn([
                'payment_method',
                'external_id',
                'price',
                'billing_cycle',
                'auto_renew',
                'next_billing_at',
                'notes',
            ]);
        });
    }
};

