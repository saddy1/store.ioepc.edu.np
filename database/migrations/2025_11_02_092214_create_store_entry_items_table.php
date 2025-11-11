<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_entry_items', function (Blueprint $t) {
            $t->id();

            $t->foreignId('store_entry_id')->constrained('store_entries')->cascadeOnDelete();

            // link back to the exact purchase line that created it
            $t->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();

            // optional product link if you use products
            $t->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // your existing taxonomies
            $t->foreignId('item_category_id')->nullable()->constrained('item_categories')->nullOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete(); // "product category"
            $t->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();

            // snapshots
            $t->string('item_name');        // from temp_name or product name
            $t->string('item_sn')->nullable();
            $t->string('unit', 20)->nullable();

            // qty/price
            $t->decimal('qty', 12, 3)->default(0);
            $t->decimal('rate', 12, 2)->default(0);
            $t->decimal('total_price', 14, 2)->default(0);

            $t->timestamps();

            $t->index(['store_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_entry_items');
    }
};
