<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('purchase_items', function (Blueprint $table) {
      $table->foreignId('purchase_slip_item_id')
        ->nullable()
        ->after('purchase_id')
        ->constrained('purchase_slip_items')
        ->nullOnDelete();

      // One slip-item can be purchased only once
      $table->unique('purchase_slip_item_id');
    });
  }

  public function down(): void
  {
    Schema::table('purchase_items', function (Blueprint $table) {
      $table->dropUnique(['purchase_slip_item_id']);
      $table->dropConstrainedForeignId('purchase_slip_item_id');
    });
  }
};
