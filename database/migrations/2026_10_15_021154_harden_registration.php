<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Firebase gestiona la contraseña: el backend no la necesita
        DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY birth_date DATE NULL');

        // Quita el ON UPDATE que cambiaba la fecha de vigencia al editar la fila
        DB::statement('ALTER TABLE legal_document_versions
                       MODIFY effective_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        Schema::table('users', function (Blueprint $t) {
            $t->unique('firebase_localId', 'users_firebase_localid_unique');
            $t->timestamp('registered_at')->nullable();
            $t->string('registration_ip', 45)->nullable();
            $t->dropColumn('firebase_token');   // un idToken dura 1 h y no cabe en varchar(255)
        });

        Schema::table('legal_document_versions', function (Blueprint $t) {
            $t->unique(['doc_type', 'version'], 'ldv_doc_type_version_unique');
        });

        Schema::table('terms_acceptance_log', function (Blueprint $t) {
            $t->unique(['user_id', 'doc_version_id'], 'tal_user_version_unique');
        });

        // Las versiones legales son evidencia: inmutables también a nivel de BD
        DB::unprepared("CREATE TRIGGER ldv_no_update BEFORE UPDATE ON legal_document_versions
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'legal versions are immutable'");
        DB::unprepared("CREATE TRIGGER ldv_no_delete BEFORE DELETE ON legal_document_versions
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'legal versions are immutable'");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS ldv_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS ldv_no_delete');

        Schema::table('terms_acceptance_log', fn (Blueprint $t) => $t->dropUnique('tal_user_version_unique'));
        Schema::table('legal_document_versions', fn (Blueprint $t) => $t->dropUnique('ldv_doc_type_version_unique'));
        Schema::table('users', function (Blueprint $t) {
            $t->dropUnique('users_firebase_localid_unique');
            $t->dropColumn(['registered_at', 'registration_ip']);
            $t->text('firebase_token')->nullable();
        });
    }
};