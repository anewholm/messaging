<?php namespace AcornAssociated\Messaging\Updates;

use DB;
use Schema;
use \AcornAssociated\Migration;

class BuilderTableCreateAcornassociatedMessagingMessage extends Migration
{
    public function up()
    {
        Schema::create('acornassociated_messaging_message', function($table)
        {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary()->default(DB::raw('(gen_random_uuid())'));
            $table->uuid('user_from_id');
            $table->string('subject', 2048);
            $table->text('body');
            $table->string('labels', 2048)->nullable();
            // e.g. for emails
            $table->string('externalID', 2048)->nullable()->unique();
            $table->string('source', 2048)->nullable();
            $table->string('mime_type', 64)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $this->setTableTypeContent('acornassociated_messaging_message');
    }

    public function down()
    {
        Schema::dropIfExists('acornassociated_messaging_message');
    }
}
