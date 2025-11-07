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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Número único de factura
            $table->foreignId('subscription_id')->constrained('tenancy_subscriptions')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('tenancy_plans')->onDelete('restrict');
            
            // Información de facturación
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Estado y fechas
            $table->string('status')->default('pending'); // pending, paid, canceled, refunded
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            
            // Información de pago
            $table->string('payment_method')->nullable(); // paypal, stripe, bank_transfer, etc.
            $table->string('payment_reference')->nullable(); // Referencia externa del pago
            $table->string('payment_link')->nullable(); // Link de pago si está pendiente
            
            // Notas y metadatos
            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['subscription_id']);
            $table->index(['tenant_id']);
            $table->index(['plan_id']);
            $table->index(['status']);
            $table->index(['invoice_number']);
            $table->index(['due_date']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};




