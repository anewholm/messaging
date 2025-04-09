<?php namespace AcornAssociated\Messaging\Updates;

use Schema;
use \AcornAssociated\Migration;

class BuilderTableCreateAcornassociatedMessagingMessageUser extends Migration
{
    public function up()
    {
        Schema::create('acornassociated_messaging_message_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['message_id','user_id']);

            $table->foreign('user_id')
                ->references('id')->on('acornassociated_user_users')
                ->onDelete('cascade');
            $table->foreign('message_id')
                ->references('id')->on('acornassociated_messaging_message')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('acornassociated_messaging_message_user');
    }
}
