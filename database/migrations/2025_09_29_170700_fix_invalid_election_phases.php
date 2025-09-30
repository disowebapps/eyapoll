<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Fix invalid phase values
        DB::table('elections')
            ->where('phase', 'registration')
            ->update(['phase' => 'candidate_registration']);
    }

    public function down()
    {
        // Revert if needed
        DB::table('elections')
            ->where('phase', 'candidate_registration')
            ->update(['phase' => 'registration']);
    }
};