<?php

// database/migrations/2025_11_24_000003_add_returned_at_to_store_out_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_out_items', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('store_out_items', function (Blueprint $table) {
            $table->dropColumn('returned_at');
        });
    }
};
