<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSequenceDateToCodeSequencesTable extends Migration
{
    private function prefixed(string $table): string
    {
        return DB::getTablePrefix() . $table;
    }

    public function up()
    {
        $table = $this->prefixed('code_sequences');

        if (!Schema::hasColumn('code_sequences', 'sequence_date')) {
            Schema::table('code_sequences', function (Blueprint $t) {
                $t->date('sequence_date')->nullable()->after('next_sequence');
            });
        }

        // Unique pair (label, sequence_date) — used by OpdVisitRepository for per-day OPD numbers.
        // Existing rows have sequence_date NULL; treat NULLs as distinct in Postgres so legacy global counters still work.
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS {$table}_label_sequence_date_unique ON {$table} (label, sequence_date)");
    }

    public function down()
    {
        $table = $this->prefixed('code_sequences');

        DB::statement("DROP INDEX IF EXISTS {$table}_label_sequence_date_unique");

        if (Schema::hasColumn('code_sequences', 'sequence_date')) {
            Schema::table('code_sequences', function (Blueprint $t) {
                $t->dropColumn('sequence_date');
            });
        }
    }
}
