<?php

namespace App\Console\Commands;

use Carbon\Carbon;

class ImportCollections extends AbstractImportCommand
{

    protected $signature = 'import:collections';

    protected $description = "Import collections data that has been updated since the last import";


    public function handle()
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

    private function import($endpoint, $current = 1)
    {

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $model = \App\Models\CollectionsModel::classFor($endpoint);

        $json = $this->queryService($endpoint, $current);
        $pages = $json->pagination->pages->total;

        while ($current <= $pages)
        {

            foreach ($json->data as $source)
            {
                $sourceIndexedTime = new Carbon($source->indexed_at);
                $sourceIndexedTime->timezone = config('app.timezone');

                if ($this->command->last_success_at->gt($sourceIndexedTime))
                {
                    break 2;
                }

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