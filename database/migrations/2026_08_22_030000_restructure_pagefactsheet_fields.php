<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagefactsheet', function (Blueprint $table) {
            $table->string('titleID')->default('')->after('name');
            $table->string('titleEN')->default('')->after('titleID');
            $table->text('descriptionID')->nullable()->after('titleEN');
            $table->text('descriptionEN')->nullable()->after('descriptionID');
            $table->string('link')->default('')->after('descriptionEN');
        });

        Schema::table('pagefactsheet', function (Blueprint $table) {
            $table->dropColumn(['contentID', 'contentEN']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagefactsheet', function (Blueprint $table) {
            $table->text('contentID');
            $table->text('contentEN');
        });

        Schema::table('pagefactsheet', function (Blueprint $table) {
            $table->dropColumn(['titleID', 'titleEN', 'descriptionID', 'descriptionEN', 'link']);
        });
    }
};
