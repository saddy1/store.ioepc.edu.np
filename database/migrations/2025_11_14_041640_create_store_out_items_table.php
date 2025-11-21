<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_out_items', function (Blueprint $t) {
            $t->id();

            $t->foreignId('store_out_id')->constrained('store_outs')->cascadeOnDelete();

            // From where this stock is coming (for traceability)
            $t->foreignId('store_entry_item_id')->nullable()->constrained('store_entry_items')->nullOnDelete();

            // Optional taxonomy for reporting
            $t->foreignId('item_category_id')->nullable()->constrained('item_categories')->nullOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();

            // Snapshots
            $t->string('item_name');
            $t->string('item_sn')->nullable();
            $t->string('unit', 20)->nullable();

            // Issued quantity
            $t->decimal('qty', 12, 3)->default(0);

            $t->timestamps();

            $t->index(['store_out_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_out_items');
    }
};
