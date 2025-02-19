<?php namespace AcornAssociated\Messaging\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornassociatedMessagingMessageUserGroup extends Migration
{
    public function up()
    {
        Schema::create('acornassociated_messaging_message_user_group', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('message_id');
            $table->uuid('user_group_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['message_id','user_group_id']);

            $table->foreign('message_id')
                ->references('id')->on('acornassociated_messaging_message')
                ->onDelete('cascade');
            $table->foreign('user_group_id')
                ->references('id')->on('acornassociated_user_user_groups')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('acornassociated_messaging_message_user_group');
    }
}
