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
        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedBigInteger('status')->default(1);
            $table->foreign('status', 'sections_status_foreign')
                ->references('id')
                ->on('section_status')
                ->restrictOnDelete();
            $table->index('status', 'sections_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign('sections_status_foreign');
            $table->dropIndex('sections_status_index');
            $table->dropColumn('status');
        });
    }
};
