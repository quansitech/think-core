<?php
namespace Testing;

use Illuminate\Support\Facades\DB;

trait DBTrait{
    public function install()
    {
        $this->artisan('migrate:refresh');
    }

    protected function uninstall($database_name = '')
    {
        if(!$database_name){
            $database_name = env('DB_DATABASE');
        }
        $tables = DB::select("SELECT CONCAT('',table_name) as tb, table_type as table_type FROM information_schema.`TABLES` WHERE table_schema='" . $database_name . "'");

        foreach($tables as $table){
            switch($table->table_type){
                case 'BASE TABLE':
                    DB::statement("drop table $database_name." . $table->tb);
                    break;
                case 'VIEW':
                    DB::statement("drop view $database_name." . $table->tb);
                    break;
            }
        }

        $procedures = DB::select("show procedure status where Db='" . $database_name . "'");
        foreach($procedures as $procedure){
            DB::unprepared("drop procedure $database_name." . $procedure->Name);
        }

        $events = DB::select("SELECT * FROM information_schema.EVENTS where event_schema='" . $database_name . "'");
        foreach($events as $event){
            DB::unprepared("drop event $database_name." . $event->EVENT_NAME);
        }
    }
}