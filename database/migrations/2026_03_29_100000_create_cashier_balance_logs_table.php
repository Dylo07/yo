<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_balance_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('time');
            $table->decimal('balance', 12, 2);
            $table->string('note')->nullable();
            $table->string('entered_by');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_balance_logs');
    }
};
