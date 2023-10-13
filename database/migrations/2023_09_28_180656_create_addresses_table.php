<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id(); 
            $table->string('city'); 
            $table->string('street'); 
            $table->string('house_number'); 
            $table->string('apartment_number')->nullable(); 
            $table->string('entrance')->nullable(); 
            $table->string('floor')->nullable(); 
            $table->string('intercom')->nullable(); 
            $table->string('gate')->nullable();
            $table->text('comment')->nullable(); 
            $table->unsignedBigInteger('user_id');
            $table->timestamps(); 

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
