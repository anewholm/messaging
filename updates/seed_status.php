<?php namespace AcornAssociated\Messaging\Updates;

use Winter\Storm\Database\Updates\Seeder;
use AcornAssociated\Messaging\Models\Status;
use DB;

class SeedStatus extends Seeder
{
    public function run()
    {
        if (!Status::count()) {
            // System message statuses
            // Do not delete!
            DB::table('acornassociated_messaging_status')->insert(['name' => 'Arrived',   'description' => 'For external messages only, like email.']);
            DB::table('acornassociated_messaging_status')->insert(['name' => 'Seen',      'description' => 'In a list']);
            DB::table('acornassociated_messaging_status')->insert(['name' => 'Read',      'description' => 'In full view, or if not truncated in a list']);
            DB::table('acornassociated_messaging_status')->insert(['name' => 'Important', 'description' => 'User Action']);
            DB::table('acornassociated_messaging_status')->insert(['name' => 'Hidden',    'description' => 'User Action']);
        }
    }
}
