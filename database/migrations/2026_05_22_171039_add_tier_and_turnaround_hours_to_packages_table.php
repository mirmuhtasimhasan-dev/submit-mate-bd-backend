<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('tier')->default('basic')->after('service_id');
            $table->unsignedInteger('turnaround_hours')->nullable()->after('price');
            $table->json('features')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['tier', 'turnaround_hours', 'features']);
        });
    }
};