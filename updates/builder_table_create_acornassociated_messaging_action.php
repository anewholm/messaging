<?php namespace AcornAssociated\Messaging\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornassociatedMessagingAction extends Migration
{
    public function up()
    {
        Schema::create('acornassociated_messaging_action', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('message_id');
            $table->string('action', 1024);
            $table->text('settings');
            $table->uuid('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('acornassociated_messaging_action');
    }
}
