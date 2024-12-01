<?php namespace Acorn\Messaging\Updates;

use DB;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateBackendUsersExtraFields extends Migration
{
    public function up()
    {
        // Add extra namespaced fields in to the users table
        Schema::table('acorn_user_users', function(\Winter\Storm\Database\Schema\Blueprint $table) {
            // IMAP
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_username')) $table->string('acorn_imap_username')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_password')) $table->string('acorn_imap_password')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_server'))   $table->string('acorn_imap_server')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_port'))     $table->integer('acorn_imap_port')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_protocol')) $table->string('acorn_imap_protocol')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_encryption'))      $table->string('acorn_imap_encryption')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_authentication'))  $table->string('acorn_imap_authentication')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_validate_cert'))   $table->boolean('acorn_imap_validate_cert')->nullable();

            // SMTP
            if (!Schema::hasColumn($table->getTable(), 'acorn_smtp_server'))     $table->string('acorn_smtp_server')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_smtp_port'))       $table->string('acorn_smtp_port')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_smtp_encryption')) $table->string('acorn_smtp_encryption')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_smtp_authentication')) $table->string('acorn_smtp_authentication')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_smtp_username'))   $table->string('acorn_smtp_username')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_smtp_password'))   $table->string('acorn_smtp_password')->nullable();

            // General
            if (!Schema::hasColumn($table->getTable(), 'acorn_messaging_sounds')) $table->boolean('acorn_messaging_sounds')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_messaging_email_notifications')) $table->char('acorn_messaging_email_notifications', 1)->nullable();

            if (!Schema::hasColumn($table->getTable(), 'acorn_messaging_autocreated')) $table->boolean('acorn_messaging_autocreated')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_imap_last_fetch'))       $table->timestamp('acorn_imap_last_fetch')->nullable();
        });
    }

    public function down()
    {
        Schema::table('acorn_user_users', function(\Winter\Storm\Database\Schema\Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_username')) $table->dropColumn('acorn_imap_username');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_password')) $table->dropColumn('acorn_imap_password');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_server'))   $table->dropColumn('acorn_imap_server');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_port'))     $table->dropColumn('acorn_imap_port');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_protocol')) $table->dropColumn('acorn_imap_protocol');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_encryption'))      $table->dropColumn('acorn_imap_encryption');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_authentication'))  $table->dropColumn('acorn_imap_authentication');
            if (Schema::hasColumn($table->getTable(), 'acorn_imap_validate_cert'))   $table->dropColumn('acorn_imap_validate_cert');
            if (Schema::hasColumn($table->getTable(), 'acorn_messaging_sounds'))   $table->dropColumn('acorn_messaging_sounds');
            if (Schema::hasColumn($table->getTable(), 'acorn_messaging_email_notifications'))   $table->dropColumn('acorn_messaging_email_notifications');

            if (Schema::hasColumn($table->getTable(), 'acorn_imap_last_fetch'))   $table->dropColumn('acorn_imap_last_fetch');
        });
    }
}
