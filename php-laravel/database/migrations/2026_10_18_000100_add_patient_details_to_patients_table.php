<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('name');
            $table->string('cpf', 14)->unique();
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropColumn(['name', 'cpf', 'phone', 'birth_date', 'email']);
        });
    }
};
