<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('worksheet_classes', 'slug')) {
            Schema::table('worksheet_classes', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        DB::table('worksheet_classes')->orderBy('id')->get()->each(function (object $class): void {
            [$name, $slug] = match ($class->name) {
                'CSE', 'Civil Service Examination' => ['Civil Service Examination', 'cse'],
                'UPCAT', 'University of the Philippines College Admission Test' => [
                    'University of the Philippines College Admission Test',
                    'upcat',
                ],
                default => [
                    $class->name,
                    filled($class->slug) ? $class->slug : Str::slug($class->name),
                ],
            };

            DB::table('worksheet_classes')->where('id', $class->id)->update([
                'name' => $name,
                'slug' => $slug !== '' ? $slug : 'class-'.$class->id,
                'updated_at' => now(),
            ]);
        });

        $slugAlreadyUnique = collect(Schema::getIndexes('worksheet_classes'))
            ->contains(fn (array $index): bool => $index['unique'] === true
                && $index['columns'] === ['slug']);

        if (! $slugAlreadyUnique) {
            Schema::table('worksheet_classes', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worksheet_classes', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
