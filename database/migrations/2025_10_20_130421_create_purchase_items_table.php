<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('purchase_items', function (Blueprint $table) {
      $table->id();

      // Parent purchase
      $table->foreignId('purchase_id')
            ->constrained('purchases')
            ->cascadeOnDelete();

      // Category (required)
      $table->foreignId('item_category_id')
      ->nullable()
            ->constrained('item_categories')
            ->restrictOnDelete();

      // Optional link to products (if/when you use it)
      $table->foreignId('product_id')
            ->nullable()
            ->constrained('products')
            ->nullOnDelete();

      // Free-text item when product_id is null
      $table->string('temp_name')->nullable();
      $table->string('temp_sn', 100)->nullable();

      // Units / pricing
      $table->string('unit', 20)->nullable();
      $table->decimal('qty', 12, 3);
      $table->decimal('rate', 12, 2);

      // Optional per-line discount (either way you fill them)
      $table->decimal('discount_percent', 5, 2)->default(0);  // e.g. 5.00 = 5%
      $table->decimal('discount_amount', 12, 2)->default(0);  // absolute
$table->string('store_entry_sn')->nullable();
      $table->date('store_entry_date')->nullable();
      // Computed: (qty * rate) - discount_amount
      $table->decimal('line_subtotal', 15, 2)->default(0);

      $table->text('notes')->nullable();

      $table->timestamps();

      $table->index(['purchase_id']);
      $table->index(['item_category_id']);
      $table->index(['product_id']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('purchase_items');
  }
};
