<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('purchases', function (Blueprint $table) {
      $table->id();

      $table->string('purchase_sn', 50)->unique();
      $table->string('purchase_date');

      // Supplier (single definition)
      $table->foreignId('supplier_id')
            ->constrained('suppliers')
            ->cascadeOnDelete();

      // Tie back to the internal order (purchase slip)
      $table->foreignId('purchase_slip_id')
            ->constrained('purchase_slips')
            ->cascadeOnDelete();

      // Copied from slip for convenience
      $table->foreignId('department_id')
            ->nullable()
            ->constrained('departments')
            ->nullOnDelete();
      

      // Totals
      $table->decimal('sub_total', 15, 2)->default(0);           // sum of line_subtotals
      $table->enum('tax_mode', ['VAT','PAN'])->default('PAN');    // VAT => add vat_percent; PAN => 0
      $table->decimal('vat_percent', 5, 2)->default(13.00);       // 13.00%
      $table->decimal('vat_amount', 15, 2)->default(0);
      $table->decimal('grand_total', 15, 2)->default(0);

      // Keep legacy/compat total if you need it (mirror grand_total in code)
      $table->decimal('total_amount', 14, 2)->default(0);

      // Bill image path (required at DB level)

      $table->string('bill_no')->nullable();
      $table->string('bill_pic')->nullable();

      $table->text('remarks')->nullable();

      $table->timestamps();

      $table->index(['purchase_sn','purchase_date']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('purchases');
  }
};
