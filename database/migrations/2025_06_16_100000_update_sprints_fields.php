<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // 1. derruba constraints, dropa e recria
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sprints');
        Schema::enableForeignKeyConstraints();

        // 2. recria a tabela sprints com novos campos
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->longText('description')->nullable();

            // housekeeping
            $table->softDeletes();
            $table->timestamps();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            // relações
            $table->foreignId('client_id')->nullable()->constrained('clients');

            // novos campos de créditos / billing
            $table->integer('tickets_credits')->nullable();
            $table->integer('extra_credits')->default(0);
            $table->integer('total_credits')->nullable();
            $table->boolean('billed')->default(false);
        });
    }

    public function down()
    {
        // rollback recreando a versão *antiga* (com project_id)
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sprints');
        Schema::enableForeignKeyConstraints();

        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->longText('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // campos antigos
            $table->foreignId('project_id')->constrained('projects');
        });
    }
};
