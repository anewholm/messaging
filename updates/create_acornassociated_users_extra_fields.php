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
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_username')) $table->string('acorn_IMAP_username')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_password')) $table->string('acorn_IMAP_password')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_server'))   $table->string('acorn_IMAP_server')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_port'))     $table->integer('acorn_IMAP_port')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_protocol')) $table->string('acorn_IMAP_protocol')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_encryption'))      $table->string('acorn_IMAP_encryption')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_authentication'))  $table->string('acorn_IMAP_authentication')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_validate_cert'))   $table->boolean('acorn_IMAP_validate_cert')->nullable();

            // SMTP
            if (!Schema::hasColumn($table->getTable(), 'acorn_SMTP_server'))     $table->string('acorn_SMTP_server')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_SMTP_port'))       $table->string('acorn_SMTP_port')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_SMTP_encryption')) $table->string('acorn_SMTP_encryption')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_SMTP_authentication')) $table->string('acorn_SMTP_authentication')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_SMTP_username'))   $table->string('acorn_SMTP_username')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_SMTP_password'))   $table->string('acorn_SMTP_password')->nullable();

            // General
            if (!Schema::hasColumn($table->getTable(), 'acorn_messaging_sounds')) $table->boolean('acorn_messaging_sounds')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_messaging_email_notifications')) $table->char('acorn_messaging_email_notifications', 1)->nullable();

            if (!Schema::hasColumn($table->getTable(), 'acorn_messaging_autocreated')) $table->boolean('acorn_messaging_autocreated')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acorn_IMAP_last_fetch'))       $table->timestamp('acorn_IMAP_last_fetch')->nullable();
        });
    }

    public function down()
    {
        Schema::table('acorn_user_users', function(\Winter\Storm\Database\Schema\Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_username')) $table->dropColumn('acorn_IMAP_username');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_password')) $table->dropColumn('acorn_IMAP_password');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_server'))   $table->dropColumn('acorn_IMAP_server');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_port'))     $table->dropColumn('acorn_IMAP_port');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_protocol')) $table->dropColumn('acorn_IMAP_protocol');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_encryption'))      $table->dropColumn('acorn_IMAP_encryption');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_authentication'))  $table->dropColumn('acorn_IMAP_authentication');
            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_validate_cert'))   $table->dropColumn('acorn_IMAP_validate_cert');
            if (Schema::hasColumn($table->getTable(), 'acorn_messaging_sounds'))   $table->dropColumn('acorn_messaging_sounds');
            if (Schema::hasColumn($table->getTable(), 'acorn_messaging_email_notifications'))   $table->dropColumn('acorn_messaging_email_notifications');

            if (Schema::hasColumn($table->getTable(), 'acorn_IMAP_last_fetch'))   $table->dropColumn('acorn_IMAP_last_fetch');
        });
    }
}
