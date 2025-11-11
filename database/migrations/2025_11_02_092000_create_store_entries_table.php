<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_entries', function (Blueprint $t) {
            $t->id();

            // link the purchase that was posted to store
            $t->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();

            // supplier snapshot (optional FK + denormalized name)
            $t->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // header snapshot (keep types compatible with your 'purchases' table)
            $t->string('purchase_sn', 50);
            $t->string('purchase_date'); // purchases.purchase_date is string in your schema
            $t->string('supplier_name')->nullable();

            $t->string('remarks', 2000)->nullable();

            $t->timestamps();

            $t->index(['purchase_sn','purchase_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_entries');
    }
};
