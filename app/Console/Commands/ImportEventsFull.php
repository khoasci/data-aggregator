<?php

namespace App\Console\Commands;

use Carbon\Carbon;

class ImportEventsFull extends AbstractImportCommand
{

    protected $signature = 'import:events-full';

    protected $description = "Import all events data";


    public function handle()
    {

        // Return false if the user bails out
        if (!$this->confirm("Running this will delete all existing events from your database! Are you sure?"))
        {
            return false;
        }

        // Remove all events from the search index
        $this->call("scout:flush", ['model' => \App\Models\Membership\Event::class]);

        // Truncate all tables
        // $this->call("migrate:refresh");

        // Pseduo-refresh this specific migration...
        $migration = new \CreateMembershipTables();
        $migration->down();
        $migration->up();

        $this->info("Refreshed CreateMembershipTables migration.");

        // Reinstall search: flush might not work, since some models might be present in the index, which aren't here
        $this->warn("Please manually ensure that your search index mappings are up-to-date.");
        // $this->call("search:uninstall");
        // $this->call("search:install");

        $this->import('events', 1);

        $this->info("Imported all events from data service!");

    }


    private function import($endpoint, $current = 1)
    {

        $model = \App\Models\Membership\Event::class;

        // Abort if the table is already filled
        if( $model::count() > 0 )
        {
            return false;
        }

        // Query for the first page + get page count
        $json = $this->queryService($endpoint, $current);
        $pages = $json->pagination->total_pages;

        while ($current <= $pages)
        {

            foreach ($json->data as $source)
            {

                $source->is_ticketed = true;
                $this->saveDatum( $source, $model );

            }

            $current++;
            $json = $this->queryService($endpoint, $current);

        }

    }

    private function queryService($endpoint, $page = 1, $limit = 100)
    {
        return $this->query( env('EVENTS_DATA_SERVICE_URL', 'http://localhost') . '/' . $endpoint . '?page=' . $page . '&limit=' . $limit );
    }

}