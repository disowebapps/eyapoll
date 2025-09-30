<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->boolean('is_revoked')->default(false)->after('vote_receipt_hash');
        });
    }

    public function down()
    {
        Schema::table('vote_tokens', function (Blueprint $table) {
            $table->dropColumn('is_revoked');
        });
    }
};