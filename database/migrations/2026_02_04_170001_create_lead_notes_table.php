<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mini-CRM Lead Management - Lead notes table (immutable history log)
     * No update/delete allowed - provides complete audit trail
     */
    public function up(): void
    {
        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Note Content
            $table->text('note');
            $table->string('type')->default('general'); // enum: general, call_outcome, whatsapp_reply, feedback, system
            
            // Only created_at - notes are immutable
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index('lead_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_notes');
    }
};
