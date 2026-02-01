<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {

            // 1) If column doesn't exist, add it (nullable)
            if (!Schema::hasColumn('purchase_items', 'purchase_slip_item_id')) {
                $table->unsignedBigInteger('purchase_slip_item_id')->nullable()->after('purchase_id');
            } else {
                // 2) If exists, make sure it's nullable
                $table->unsignedBigInteger('purchase_slip_item_id')->nullable()->change();
            }

            // OPTIONAL (if you also want to store slip_id directly per item)
            if (!Schema::hasColumn('purchase_items', 'purchase_slip_id')) {
                $table->unsignedBigInteger('purchase_slip_id')->nullable()->after('purchase_slip_item_id');
            } else {
                $table->unsignedBigInteger('purchase_slip_id')->nullable()->change();
            }
        });

        /**
         * Foreign keys:
         * - Drop old FK name if it already exists (ignore errors)
         * - Recreate with a custom name (prevents "duplicate name" issue)
         */
        try {
            DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_purchase_slip_item_id_foreign`');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_purchase_slip_id_foreign`');
        } catch (\Throwable $e) {}

        Schema::table('purchase_items', function (Blueprint $table) {
            // Create FK with custom names (so it won’t collide)
            $table->foreign('purchase_slip_item_id', 'purchase_items_slip_item_fk')
                ->references('id')->on('purchase_slip_items')
                ->nullOnDelete();

            $table->foreign('purchase_slip_id', 'purchase_items_slip_fk')
                ->references('id')->on('purchase_slips')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop custom FKs first
        try {
            DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_slip_item_fk`');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE `purchase_items` DROP FOREIGN KEY `purchase_items_slip_fk`');
        } catch (\Throwable $e) {}

        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'purchase_slip_item_id')) {
                $table->dropColumn('purchase_slip_item_id');
            }
            if (Schema::hasColumn('purchase_items', 'purchase_slip_id')) {
                $table->dropColumn('purchase_slip_id');
            }
        });
    }
};
