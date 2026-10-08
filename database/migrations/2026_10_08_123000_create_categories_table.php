<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        $names = [
            'Makanan',
            'Minuman',
            'Fashion',
            'Sembako',
            'Elektronik',
            'Aksesoris',
            'Kesehatan & Kecantikan',
            'Peralatan Rumah',
        ];

        if (Schema::hasTable('products')) {
            $names = array_merge(
                $names,
                DB::table('products')
                    ->whereNotNull('category')
                    ->where('category', '<>', '')
                    ->distinct()
                    ->pluck('category')
                    ->all()
            );
        }

        $now = now();
        $categories = collect($names)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        DB::table('categories')->insertOrIgnore($categories);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
