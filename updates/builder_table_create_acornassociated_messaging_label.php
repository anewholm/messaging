<?php namespace Acorn\Messaging\Updates;

use DB;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornMessagingLabel extends Migration
{
    public function up()
    {
        Schema::create('acorn_messaging_label', function($table)
        {
            // TODO: A replacement for the labels field
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->string('name')->unsigned();
            $table->string('description')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('acorn_messaging_label');
    }
}
