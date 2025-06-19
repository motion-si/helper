<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'epic_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['epic_id']);
                $table->dropColumn('epic_id');
            });
        }

        if (Schema::hasTable('sprints') && Schema::hasColumn('sprints', 'epic_id')) {
            Schema::table('sprints', function (Blueprint $table) {
                $table->dropForeign(['epic_id']);
                $table->dropColumn('epic_id');
            });
        }

        Schema::dropIfExists('epics');
    }

    public function down()
    {
        Schema::create('epics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects');
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('parent_id')->nullable()->constrained('epics');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('epic_id')->nullable()->constrained('epics');
        });

        Schema::table('sprints', function (Blueprint $table) {
            $table->foreignId('epic_id')->nullable()->constrained('epics');
        });
    }
};
