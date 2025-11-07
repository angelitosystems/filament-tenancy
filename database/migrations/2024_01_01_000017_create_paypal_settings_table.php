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
        Schema::create('paypal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mode')->default('sandbox'); // sandbox or live
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('webhook_secret')->nullable();
            $table->string('return_url')->default('/paypal/success');
            $table->string('cancel_url')->default('/paypal/cancel');
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_settings');
    }
};




