<?php
// database/migrations/2025_09_15_000000_create_student_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table) {
            // Primary key is token_num (string)
          
            $table->string('token_num')->primary();

            // Paths to files (stored on 'public' disk)
            $table->string('payment_image'); // from input "payment_voucher"
            $table->string('voucher_image'); // from input "token_slip"
$table->string('status')->default('Pending'); // Status column with default value 'Pending'
            $table->timestamps();

            // FK: references students.token_num (must be unique/indexed in students)
            $table->foreign('token_num')
                ->references('token_num')
                ->on('students')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
