<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'tier')) {
                $table->string('tier')->default('basic')->after('service_id');
            }

            if (! Schema::hasColumn('packages', 'turnaround_hours')) {
                $table->unsignedInteger('turnaround_hours')->nullable()->after('price');
            }

            if (! Schema::hasColumn('packages', 'features')) {
                $table->json('features')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'features')) {
                $table->dropColumn('features');
            }

            if (Schema::hasColumn('packages', 'turnaround_hours')) {
                $table->dropColumn('turnaround_hours');
            }

            if (Schema::hasColumn('packages', 'tier')) {
                $table->dropColumn('tier');
            }
        });
    }
};