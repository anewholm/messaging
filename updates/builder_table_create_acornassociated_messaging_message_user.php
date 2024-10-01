<?php namespace Acorn\Messaging\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornMessagingMessageUser extends Migration
{
    public function up()
    {
        Schema::create('acorn_messaging_message_user', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['message_id','user_id']);

            $table->foreign('user_id')
                ->references('id')->on('acorn_user_users')
                ->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('acorn_messaging_message_user');
    }
}
