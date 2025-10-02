<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono')->nullable()->after('email');
            $table->string('zonaHoraria')->nullable()->after('telefono');
            $table->string('idioma')->nullable()->after('zonaHoraria');
            $table->text('foto')->nullable()->after('idioma'); // 👈 cambiar string por text
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'zonaHoraria', 'idioma', 'foto']);
        });
    }
};
