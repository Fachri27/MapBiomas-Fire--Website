<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factsheet', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('titleID');
            $table->string('titleEN');
            $table->text('descriptionID')->nullable();
            $table->text('descriptionEN')->nullable();
            $table->string('link')->default('');
            $table->timestamps();
        });

        // Pindahkan dua edisi yang ada ke tabel baru.
        $rows = DB::table('pagefactsheet')->get([
            'category', 'titleID', 'titleEN', 'descriptionID', 'descriptionEN', 'link',
        ]);
        foreach ($rows as $row) {
            DB::table('factsheet')->insert((array) $row);
        }

        Schema::drop('pagefactsheet');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('pagefactsheet', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('monthly');
            $table->string('titleID')->default('');
            $table->string('titleEN')->default('');
            $table->text('descriptionID')->nullable();
            $table->text('descriptionEN')->nullable();
            $table->string('link')->default('');
            $table->timestamps();
        });

        $rows = DB::table('factsheet')->get([
            'category', 'titleID', 'titleEN', 'descriptionID', 'descriptionEN', 'link',
        ]);
        foreach ($rows as $row) {
            DB::table('pagefactsheet')->insert(array_merge((array) $row, ['name' => 'factsheet-'.$row->category]));
        }

        Schema::drop('factsheet');
    }
};
