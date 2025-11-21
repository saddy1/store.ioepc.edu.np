<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();

            $table->string('full_name');
            $table->string('contact', 30)->nullable();
            $table->string('atten_no', 50)->nullable(); // Attendance/Invigilator number (free text)
            $table->string('email')->nullable()->unique(); // nullable but unique when present
            $table->string('password'); // admin sets first time
            $table->boolean('must_change_password')->default(true); // 1st login they must change
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
