<?php namespace AcornAssociated\Messaging\Updates;

use DB;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateBackendUsersExtraFields extends Migration
{
    public function up()
    {
        // Add extra namespaced fields in to the users table
        Schema::table('acornassociated_user_users', function(\Winter\Storm\Database\Schema\Blueprint $table) {
            // IMAP
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_username')) $table->string('acornassociated_imap_username')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_password')) $table->string('acornassociated_imap_password')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_server'))   $table->string('acornassociated_imap_server')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_port'))     $table->integer('acornassociated_imap_port')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_protocol')) $table->string('acornassociated_imap_protocol')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_encryption'))      $table->string('acornassociated_imap_encryption')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_authentication'))  $table->string('acornassociated_imap_authentication')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_validate_cert'))   $table->boolean('acornassociated_imap_validate_cert')->nullable();

            // SMTP
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_smtp_server'))     $table->string('acornassociated_smtp_server')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_smtp_port'))       $table->string('acornassociated_smtp_port')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_smtp_encryption')) $table->string('acornassociated_smtp_encryption')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_smtp_authentication')) $table->string('acornassociated_smtp_authentication')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_smtp_username'))   $table->string('acornassociated_smtp_username')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_smtp_password'))   $table->string('acornassociated_smtp_password')->nullable();

            // General
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_messaging_sounds')) $table->boolean('acornassociated_messaging_sounds')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_messaging_email_notifications')) $table->char('acornassociated_messaging_email_notifications', 1)->nullable();

            if (!Schema::hasColumn($table->getTable(), 'acornassociated_messaging_autocreated')) $table->boolean('acornassociated_messaging_autocreated')->nullable();
            if (!Schema::hasColumn($table->getTable(), 'acornassociated_imap_last_fetch'))       $table->timestamp('acornassociated_imap_last_fetch')->nullable();
        });
    }

    public function down()
    {
        Schema::table('acornassociated_user_users', function(\Winter\Storm\Database\Schema\Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_username')) $table->dropColumn('acornassociated_imap_username');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_password')) $table->dropColumn('acornassociated_imap_password');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_server'))   $table->dropColumn('acornassociated_imap_server');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_port'))     $table->dropColumn('acornassociated_imap_port');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_protocol')) $table->dropColumn('acornassociated_imap_protocol');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_encryption'))      $table->dropColumn('acornassociated_imap_encryption');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_authentication'))  $table->dropColumn('acornassociated_imap_authentication');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_validate_cert'))   $table->dropColumn('acornassociated_imap_validate_cert');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_messaging_sounds'))   $table->dropColumn('acornassociated_messaging_sounds');
            if (Schema::hasColumn($table->getTable(), 'acornassociated_messaging_email_notifications'))   $table->dropColumn('acornassociated_messaging_email_notifications');

            if (Schema::hasColumn($table->getTable(), 'acornassociated_imap_last_fetch'))   $table->dropColumn('acornassociated_imap_last_fetch');
        });
    }
}
