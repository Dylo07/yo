<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Customer Feedback Tracking - Tracks feedback collection from completed bookings
     */
    public function up(): void
    {
        Schema::create('customer_feedbacks', function (Blueprint $table) {
            $table->id();
            
            // Link to booking (nullable for manual entries)
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            
            // Customer Information (copied from booking or manual entry)
            $table->string('customer_name');
            $table->string('contact_number', 20);
            $table->string('function_type')->nullable();
            $table->date('function_date'); // The date the function/stay ended
            
            // Feedback Status
            $table->enum('status', ['pending', 'completed'])->default('pending');
            
            // Feedback Details (filled when feedback is taken)
            $table->tinyInteger('rating')->unsigned()->nullable(); // 1-5 stars
            $table->text('feedback_notes')->nullable();
            $table->datetime('feedback_taken_at')->nullable();
            $table->foreignId('feedback_taken_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('function_date');
            $table->index('contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_feedbacks');
    }
};
