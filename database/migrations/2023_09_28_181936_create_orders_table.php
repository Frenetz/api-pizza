<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('delivery_method_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('address_id'); 
            $table->string('status');
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
            
            
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('delivery_method_id')->references('id')->on('delivery_methods')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->cascadeOnDelete();
            $table->foreign('address_id')->references('id')->on('addresses')->cascadeOnDelete();
        });
    }
    
    
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

// $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
// $table->foreignId('delivery_method_id')->constrained('delivery_methods')->onDelete('cascade'); 
// $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade'); 
// $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');