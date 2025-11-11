<?php
// database/migrations/xxxx_xx_xx_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->string('name', 150);
      $table->string('sku', 50)->nullable();               // your internal code
      $table->foreignId('item_category_id')->nullable()->constrained('item_categories')->nullOnDelete();
      $table->foreignId('product_category_id')->nullable()->constrained('categories')->nullOnDelete();
      $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
      $table->string('unit', 20)->default('pcs');        // pcs, box, kg, etc.
      $table->unsignedInteger('reorder_level')->default(0);
      $table->string('image_path')->nullable();          // storage path
      $table->boolean('is_active')->default(true);
      $table->timestamps();
      $table->index(['name','sku']);
    });
  }
  public function down(): void { Schema::dropIfExists(table: 'products'); }
};

