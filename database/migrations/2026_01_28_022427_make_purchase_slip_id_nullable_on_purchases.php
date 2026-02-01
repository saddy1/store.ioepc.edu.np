<?php

// database/migrations/xxxx_xx_xx_xxxxxx_make_purchase_slip_id_nullable_on_purchases.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop FK first (name may differ)
            try { $table->dropForeign(['purchase_slip_id']); } catch (\Throwable $e) {}
            $table->unsignedBigInteger('purchase_slip_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_slip_id')->nullable(false)->change();
            $table->foreign('purchase_slip_id')->references('id')->on('purchase_slips');
        });
    }
};
