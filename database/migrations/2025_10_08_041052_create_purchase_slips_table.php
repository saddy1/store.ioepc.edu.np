<?php

// database/migrations/xxxx_xx_xx_create_purchase_slips_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('purchase_slips', function (Blueprint $table) {
      $table->id();

      $table->string('po_sn', 50)->unique();   // Purchase Slip S.N.
      $table->date('po_date');                 // store as DATE (was string)

      $table->foreignId('department_id')
            ->constrained('departments')
            ->cascadeOnDelete();

      $table->text('remarks')->nullable();

      $table->timestamps();

      // Additional indexes to speed up filters
      $table->index('po_date');
      $table->index(['department_id', 'po_date']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('purchase_slips');
  }
};
