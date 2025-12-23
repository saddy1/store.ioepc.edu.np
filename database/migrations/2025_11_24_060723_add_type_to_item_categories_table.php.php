<?php
// database/migrations/2025_11_24_000001_add_type_to_item_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->string('type', 30)
                ->default('Consumable')   // default so old data works
                ->after('name_en');
        });
    }

    public function down(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
