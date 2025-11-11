<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_slip_items', function (Blueprint $table) {
      $table->id();

      // Link to purchase_slips
      $table->foreignId('purchase_slip_id')
        ->constrained('purchase_slips')
        ->cascadeOnDelete();

      // Temporary item name / SKU (used when product_id is null)
      $table->string('temp_name')->nullable();
      $table->string('temp_sn', 100)->nullable();

      // Quantity, rate, unit, totals
      $table->decimal('ordered_qty', 12, 3);
      $table->decimal('max_rate', 12, 2);
      $table->decimal('line_total', 15, 2)->default(0);
      $table->string('unit', 20)->nullable();
      $table->foreignId('item_category_id')->nullable()->constrained('item_categories')->nullOnDelete();

      // Item category (optional)


      $table->timestamps();

      $table->index(['purchase_slip_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('purchase_slip_items');
    Schema::table('purchase_slip_items', function (Blueprint $table) {
      $table->dropConstrainedForeignId('item_category_id');
    });
  }
};
