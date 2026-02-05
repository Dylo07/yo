<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mini-CRM Lead Management - Main leads table
     * Stores all incoming inquiries from various sources (WhatsApp, Facebook, Instagram, etc.)
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            
            // Customer Information
            $table->string('customer_name')->nullable();
            $table->string('phone_number', 20)->index(); // Not unique - same guest can inquire multiple times
            $table->string('country_code', 5)->nullable(); // e.g., +94
            
            // Inquiry Details
            $table->datetime('inquiry_date'); // When first message arrived
            $table->string('source'); // enum handled in model: whatsapp, facebook, instagram, walk_in, referral, other
            
            // Stay Requirements
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->tinyInteger('adults')->unsigned()->default(0);
            $table->tinyInteger('children')->unsigned()->default(0);
            $table->text('requirements')->nullable(); // Free text: what they asked / special requests
            
            // Lead Status & Tracking
            $table->string('status')->default('new'); // enum: new, call_pending, contacted, follow_up_needed, booking_sent, won, lost
            $table->string('lost_reason')->nullable(); // price, competitor, dates_unavailable, no_answer, not_interested, other
            $table->tinyInteger('interest_level')->unsigned()->nullable(); // 1-5 scale
            
            // Follow-up Management
            $table->datetime('next_follow_up_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Conversion Tracking
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            
            // Post-Stay Feedback
            $table->tinyInteger('feedback_rating')->unsigned()->nullable(); // 1-5 scale
            
            // Communication Tracking
            $table->datetime('last_communication_at')->nullable();
            
            // Audit Fields
            $table->timestamps();
            $table->softDeletes(); // Never truly delete - audit trail requirement
            
            // Indexes for common queries
            $table->index('status');
            $table->index('source');
            $table->index('inquiry_date');
            $table->index('next_follow_up_at');
            $table->index('assigned_to');
            $table->index(['status', 'next_follow_up_at']); // Composite for follow-up queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
