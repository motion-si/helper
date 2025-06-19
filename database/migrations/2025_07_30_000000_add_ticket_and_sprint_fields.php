<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('credits');
            $table->string('development_environment')->nullable()->after('branch');
            $table->date('starts_at')->nullable()->after('development_environment');
            $table->date('ends_at')->nullable()->after('starts_at');
            $table->date('released_at')->nullable()->after('ends_at');
            $table->boolean('false_bug_report')->default(false)->after('released_at');
        });

        Schema::table('sprints', function (Blueprint $table) {
            $table->date('billing_reference')->nullable()->after('billed');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['branch','development_environment','starts_at','ends_at','released_at','false_bug_report']);
        });

        Schema::table('sprints', function (Blueprint $table) {
            $table->dropColumn('billing_reference');
        });
    }
};
