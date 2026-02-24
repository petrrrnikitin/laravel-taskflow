<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS tasks_search_vector_update ON tasks');

        DB::statement("
            CREATE OR REPLACE FUNCTION tasks_search_vector_fn()
            RETURNS trigger AS \$\$
            BEGIN
                NEW.search_vector :=
                    to_tsvector('pg_catalog.english', coalesce(NEW.title, '') || ' ' || coalesce(NEW.description, ''))
                    ||
                    to_tsvector('pg_catalog.russian', coalesce(NEW.title, '') || ' ' || coalesce(NEW.description, ''));
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::statement('
            CREATE TRIGGER tasks_search_vector_update
            BEFORE INSERT OR UPDATE ON tasks
            FOR EACH ROW EXECUTE FUNCTION tasks_search_vector_fn()
        ');

        DB::statement("
            UPDATE tasks SET search_vector =
                to_tsvector('pg_catalog.english', coalesce(title, '') || ' ' || coalesce(description, ''))
                ||
                to_tsvector('pg_catalog.russian', coalesce(title, '') || ' ' || coalesce(description, ''))
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS tasks_search_vector_update ON tasks');
        DB::statement('DROP FUNCTION IF EXISTS tasks_search_vector_fn');

        DB::statement("
            CREATE TRIGGER tasks_search_vector_update
            BEFORE INSERT OR UPDATE ON tasks
            FOR EACH ROW EXECUTE FUNCTION
            tsvector_update_trigger(search_vector, 'pg_catalog.english', title, description)
        ");

        DB::statement("
            UPDATE tasks SET search_vector =
                to_tsvector('pg_catalog.english', coalesce(title, '') || ' ' || coalesce(description, ''))
        ");
    }
};
