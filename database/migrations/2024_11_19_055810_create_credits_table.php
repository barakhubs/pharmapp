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
        Schema::create('credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->decimal('amount_owed', 8, 2);
            $table->decimal('amount_paid', 8, 2);
            $table->date('balance');
            $table->enum('status', ['paid', 'unpaid', 'partially_paid'])->default('unpaid');
            $table->foreignId('branch_id')->constrained()->CascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
