<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('purchase_slip_items', function (Blueprint $table) {
      $table->boolean('is_purchased')->default(false)->after('item_category_id');
      $table->timestamp('purchased_at')->nullable()->after('is_purchased');
    });
  }

  public function down(): void
  {
    Schema::table('purchase_slip_items', function (Blueprint $table) {
      $table->dropColumn(['is_purchased','purchased_at']);
    });
  }
};
