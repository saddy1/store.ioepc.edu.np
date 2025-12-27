<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_out_items', function (Blueprint $t) {
            if (!Schema::hasColumn('store_out_items', 'returned_at')) {
                $t->timestamp('returned_at')->nullable()->after('qty');
                $t->index(['store_entry_item_id', 'returned_at']);
            }

            if (!Schema::hasColumn('store_out_items', 'rate')) {
                $t->decimal('rate', 12, 2)->default(0)->after('qty');
            }

            if (!Schema::hasColumn('store_out_items', 'total_price')) {
                $t->decimal('total_price', 14, 2)->default(0)->after('rate');
            }

            if (!Schema::hasColumn('store_out_items', 'remarks')) {
                $t->text('remarks')->nullable()->after('total_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_out_items', function (Blueprint $t) {
            if (Schema::hasColumn('store_out_items', 'remarks')) $t->dropColumn('remarks');
            if (Schema::hasColumn('store_out_items', 'total_price')) $t->dropColumn('total_price');
            if (Schema::hasColumn('store_out_items', 'rate')) $t->dropColumn('rate');

            if (Schema::hasColumn('store_out_items', 'returned_at')) {
                $t->dropIndex(['store_entry_item_id', 'returned_at']);
                $t->dropColumn('returned_at');
            }
        });
    }
};
