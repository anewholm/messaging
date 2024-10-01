<?php namespace Acorn\Messaging\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornMessagingAction extends Migration
{
    public function up()
    {
        Schema::create('acorn_messaging_action', function($table)
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
        Schema::dropIfExists('acorn_messaging_action');
    }
}
