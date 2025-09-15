<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void
{
Schema::create('students', function (Blueprint $table) {
$table->id();
$table->string('token_num')->unique();
$table->string('roll_num'); // Pattern: PUR###*** (3 digits, 3 letters, 3 digits)
$table->string('name');
$table->string('faculty');
$table->string('batch');
$table->string('subject');
$table->string('year');
$table->string('part');
$table->string('amount');
$table->string('payment_id')->nullable();
$table->boolean('fine')->default(false);
$table->string('status')->default('unsubmitted'); // e.g., active|inactive|pending
$table->timestamps();
});
}


public function down(): void
{
Schema::dropIfExists('students');
}
};