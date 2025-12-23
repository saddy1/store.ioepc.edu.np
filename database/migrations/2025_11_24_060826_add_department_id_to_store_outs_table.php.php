<?php

// database/migrations/2025_11_24_000002_add_department_id_to_store_outs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_outs', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')
                ->nullable()
                ->after('employee_id');

            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('store_outs', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
