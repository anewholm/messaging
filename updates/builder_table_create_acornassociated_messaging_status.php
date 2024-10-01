<?php namespace Acorn\Messaging\Updates;

use DB;
use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateAcornMessagingStatus extends Migration
{
    public function up()
    {
        Schema::create('acorn_messaging_status', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->string('name');  // e.g. arrived, read, hidden
            $table->string('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('acorn_messaging_user_message_status', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('user_id');
            $table->uuid('message_id');
            $table->uuid('status_id');
            $table->string('value')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->primary(['message_id', 'status_id']);
            $table->foreign('user_id')
                ->references('id')->on('acorn_user_users')
                ->onDelete('cascade');
            $table->foreign('message_id')
                ->references('id')->on('acorn_messaging_message')
                ->onDelete('cascade');
            $table->foreign('status_id')
                ->references('id')->on('acorn_messaging_status')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('acorn_messaging_user_message_status');
        Schema::dropIfExists('acorn_messaging_status');
    }
}
