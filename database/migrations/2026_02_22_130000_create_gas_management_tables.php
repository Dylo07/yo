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
        // Gas cylinder types/inventory settings
        Schema::create('gas_cylinders', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "12.5kg Cylinder", "37.5kg Cylinder"
            $table->decimal('weight_kg', 8, 2); // Weight in kg
            $table->decimal('price', 10, 2); // Current price per cylinder
            $table->integer('filled_stock')->default(0); // Filled cylinders ready to use
            $table->integer('empty_stock')->default(0); // Empty cylinders to be exchanged
            $table->integer('minimum_stock')->default(5); // Alert threshold for filled cylinders
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Gas purchases/exchanges from dealer (incoming filled, outgoing empty)
        Schema::create('gas_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gas_cylinder_id')->constrained('gas_cylinders')->onDelete('cascade');
            $table->integer('filled_received'); // Filled cylinders received from dealer
            $table->integer('empty_returned')->default(0); // Empty cylinders returned to dealer
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('dealer_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('purchase_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Gas issues to kitchen (outgoing from store)
        Schema::create('gas_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gas_cylinder_id')->constrained('gas_cylinders')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('issued_to')->default('Kitchen'); // Kitchen, Restaurant, etc.
            $table->date('issue_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_issues');
        Schema::dropIfExists('gas_purchases');
        Schema::dropIfExists('gas_cylinders');
    }
};
