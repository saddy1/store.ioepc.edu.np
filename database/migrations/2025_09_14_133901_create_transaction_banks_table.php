<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
	{
		Schema::create('transaction_banks', function(Blueprint $table) {
			$table->increments('id');
			$table->string('txn_id', 255);
			$table->string('name', 255);
			$table->float('amount');
			$table->date('date');
			$table->boolean('status')->default(1);
			$table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down()
	{
		Schema::drop('transaction_banks');
	}
};

