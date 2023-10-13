<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->text('composition'); 
            $table->integer('calories')->nullable(); 
            $table->integer('price');
            $table->unsignedBigInteger('category_id');
            $table->timestamps(); 

            
            $table->foreign('category_id')->references('id')->on('product_categories')->cascadeOnDelete();
        });
        
    }
    
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

// $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade'); 