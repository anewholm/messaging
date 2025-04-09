<?php namespace AcornAssociated\Messaging\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornassociatedMessagingMessageMessage extends Migration
{
    public function up()
    {
        Schema::create('acornassociated_messaging_message_message', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('message1_id')->unsigned();
            $table->uuid('message2_id')->unsigned();
            $table->integer('relationship')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['message1_id','message2_id','relationship']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('acornassociated_messaging_message_message');
    }
}
