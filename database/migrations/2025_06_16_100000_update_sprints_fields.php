<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('sprints', function (Blueprint $table) {
            if (Schema::hasColumn('sprints', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
            $table->foreignId('client_id')->nullable()->constrained('clients');
            $table->integer('tickets_credits')->nullable();
            $table->integer('extra_credits')->default(0);
            $table->integer('total_credits')->nullable();
            $table->boolean('billed')->default(false);
        });
    }

    public function down()
    {
        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn(['client_id','tickets_credits','extra_credits','total_credits','billed']);
            $table->foreignId('project_id')->constrained('projects');
        });
    }
};
