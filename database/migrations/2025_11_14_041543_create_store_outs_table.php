<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_outs', function (Blueprint $t) {
            $t->id();

            // Who received the item
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // Optional: link back to a store entry header (if you want)
            $t->foreignId('store_entry_id')->nullable()->constrained('store_entries')->nullOnDelete();

            // Issue info
            $t->string('store_out_sn', 50);       // e.g. OUT-2079-001
            $t->string('store_out_date_bs', 10);  // BS date as string "YYYY-MM-DD"

            $t->string('remarks', 2000)->nullable();

            $t->timestamps();

            $t->index(['store_out_sn', 'store_out_date_bs']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_outs');
    }
};
