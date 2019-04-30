<?php

namespace App\Console\Commands\Bulk;

use Illuminate\Support\Facades\DB;
use App\Models\Collections\Artwork;

use Aic\Hub\Foundation\AbstractCommand as BaseCommand;

class BulkImport extends BaseCommand
{

    protected $signature = 'bulk:import
                            {source : Name of dataservice to query}
                            {endpoint : Endpoint on dataservice to import}
                            {ids? : Comma-separated ids to import}';

    protected $description = "Upsert resources from a data service";

    protected $chunkSize = 100; // Approx. 5 sec per 100 artworks

    protected $urlFormat;

    protected $tableMapping;

    public function handle()
    {
        $source = config('resources.sources.' . $this->argument('source'));
        $resource = config('resources.inbound.' . $this->argument('source') . '.' . $this->argument('endpoint'));

        $endpoint = $this->argument('endpoint');
        $ids = $this->argument('ids');

        $model = new $resource['model'];
        $table = $model->getTable();

        $transformer = new $resource['transformer'];

        // Query for the first page + get total
        // Limit has to be 1 due to a few 🐞's
        $json = $this->query($source, $endpoint, 1, 1, $ids);

        // Assumes the dataservice has standardized pagination
        $total = $json->pagination->total;
        $totalPages = ceil($total/$this->chunkSize);

        $bar = $this->output->createProgressBar($total);

        for ($currentPage = 1; $currentPage <= $totalPages; $currentPage++)
        {
            $json = $this->query($source, $endpoint, $currentPage, $this->chunkSize, $ids);

            $data = collect($json->data)->map(function($datum) use ($transformer, $table) {
                return [
                    'fill' => $transformer->getFill($table, $datum),
                    'sync' => [
                        'id' => $transformer->getId($datum),
                        'relations' => $transformer->getSyncNew($datum),
                    ],
                ];
            });

            // TODO: Take care of date and JSON columns in transformer?
            $fills = $data->pluck('fill')->map(function($datum) use ($model) {
                $clone = clone $model;
                array_map([$clone, 'setAttribute'], array_keys($datum), array_values($datum));
                return $clone->getAttributes();
            });

            // Manually append timestamps
            $now = date("Y-m-d H:i:s");
            $fills = $fills->map(function($datum) use ($now) {
                return array_merge($datum, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

            // Flatten relations, index by table name. Please refactor me.
            $syncs = $data->pluck('sync')->map(function($datum) use ($model) {
                $relations = collect($datum['relations'])->map(function($items, $relationMethod) use ($model, $datum) {
                    $relation = $model->$relationMethod();
                    return [
                        $relation->getTable() => collect($items)->map(function($value, $key) use ($relation, $datum) {
                            return is_array($value) ? array_merge([
                                $relation->getForeignPivotKeyName() => $datum['id'],
                                $relation->getRelatedPivotKeyName() => $key,
                            ], $value) : [
                                $relation->getForeignPivotKeyName() => $datum['id'],
                                $relation->getRelatedPivotKeyName() => $value,
                            ];
                        })->values()->all(),
                    ];
                })->values();

                $tables = array_unique(array_merge(...array_map('array_keys', $relations->all())));

                return collect($tables)->map(function($table) use ($relations) {
                    $values = $relations->pluck($table)->filter()->all();
                    return [
                        $table => empty($values) ? [] : array_merge(...$values),
                    ];
                })->collapse();
            });

            // Merge an indexed collection of assoc. collections w/o overwriting
            $syncs = $syncs->first()->map(function($items, $table) use ($syncs) {
                return $syncs->pluck($table)->collapse()->all();
            });

            $inserts = array_merge([$table => $fills->all()], $syncs->all());

            // https://gist.github.com/VinceG/0fb570925748ab35bc53f2a798cb517c
            foreach ($inserts as $tableName => $items) {
                DB::table($tableName)->insertUpdate($items);
            }

            $bar->advance(count($data));
        }

        $bar->finish();
        $this->output->newLine(1);
    }

    protected function query($source, $endpoint, $page, $limit, $ids = null)
    {
        return json_decode($this->fetch(sprintf($this->getUrlFormat(), $source, $endpoint, $page, $limit, $ids)));
    }

    protected function getUrlFormat()
    {
        // Prep URL $format for sprintf calls
        return $this->urlFormat ?? $this->urlFormat = '%s/%s?' . urldecode(http_build_query([
            'page' => '%d',
            'limit' => '%d',
            'ids' => '%s',
        ]));
    }

    protected function fetch($file, &$headers = null)
    {
        if(!$contents = @file_get_contents($file))
        {
            throw new \Exception('Fetch failed: ' . $file);
        }

        if (isset($http_response_header))
        {
            $headers = $http_response_header;
        }

        return $contents;
    }

}
