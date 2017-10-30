<?php

namespace App\Console\Commands;

use Carbon\Carbon;

class ImportCollectionsFull extends AbstractImportCommand
{

    protected $signature = 'import:collections-full
                            {endpoint? : That last portion of the URL path naming the resource to import, for example "artists"}
                            {page? : The page to begin importing from}';

    protected $description = "Import all collections data. If no options are passes all Collections data will be imported.";


    public function handle()
    {

        ini_set("memory_limit", "-1");

        if ($this->argument('endpoint'))
        {

            $page = $this->argument('page') ?: 1;
            $this->import($this->argument('endpoint'), $page);

        }
        else
        {

            $this->import('agent-types');
            $this->import('agents');
            $this->import('departments');
            $this->import('object-types');
            $this->import('categories');
            $this->import('galleries');
            $this->import('artworks');
            $this->import('links');
            $this->import('videos');
            $this->import('texts');
            $this->import('sounds');
            $this->import('images');
            $this->import('exhibitions');

        }

    }


    private function import($endpoint, $current = 1)
    {

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $model = \App\Models\CollectionsModel::classFor($endpoint);

        // Abort if the table is already filled in production.
        // In test we want to update existing records. Once we verify this
        // functionality we may want to take this condition completely out.
        if( $model::count() > 0 && config('app.env') == 'production')
        {
            return false;
        }

        // Query for the first page + get page count
        $json = $this->queryService($endpoint, $current);
        $pages = $json->pagination->pages->total;

        while ($current <= $pages)
        {

            foreach ($json->data as $source)
            {

                $this->saveDatum( $source, $model );

            }

            $current++;
            $json = $this->queryService($endpoint, $current);

        }

        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

    }

    private function queryService($endpoint, $page = 1, $limit = 100)
    {
        return $this->query( env('COLLECTIONS_DATA_SERVICE_URL', 'http://localhost') . '/' . $endpoint . '?page=' . $page . '&per_page=' . $limit );
    }

}