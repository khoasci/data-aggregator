<?php

namespace App\Console\Commands\Docs;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CreateSwaggerDoc extends AbstractDocCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:swagger
                            {appUrl? : The root URL to use for the documentation. Defaults to APP_URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate swagger documentation for API endpoints';

    protected $appUrl;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if ($this->argument('appUrl'))
        {
            $this->appUrl = $this->argument('appUrl');
        }

        /**
         * Start off the doc
         */
        $doc = "{\n";
        $doc .= "  \"swagger\": \"2.0\",\n";
        $doc .= "  \"info\": {\n";
        $doc .= "    \"title\": \"Art Institution of Chicago API\",\n";
        $doc .= '    "description": "An API for an aggregator of Art Institute of Chicago public data. ';
        $doc .= 'This documentation was generated by `php artisan docs:swagger` on ' . Carbon::now() . "\",\n";
        $doc .= "    \"termsOfService\": \"http://www.artic.edu/terms/terms-and-conditions\",\n";
        $doc .= "    \"contact\": {\n";
        $doc .= "      \"email\": \"museumtechnology@artic.edu\"\n";
        $doc .= "    },\n";
        $doc .= "    \"license\": {\n";
        $doc .= "      \"name\": \"\"\n";
        $doc .= "    },\n";
        $doc .= '    "version": "' . config('aic.version', '1.0.0') . "\"\n";
        $doc .= "  },\n";
        $doc .= '  "host": "' . parse_url(config('app.url'), PHP_URL_HOST) . "\",\n";
        $doc .= "  \"basePath\": \"/api/v1\",\n";
        $doc .= "  \"schemes\": [\n";
        $doc .= "    \"http\"\n";
        $doc .= "  ],\n";
        $doc .= "  \"paths\": {\n";

        /**
         * Endpoints
         */
        foreach ($this->getCategories() as $namespace => $heading)
        {
            foreach ($this->getModelsForNamespace($namespace) as $model)
            {
                $doc .= $model::instance()->swaggerEndpoints($this->appUrl);
            }
        }

        /**
         * Search
         */
        $doc .= "    \"/search\": {\n";
        $doc .= "      \"get\": {\n";
        $doc .= "        \"tags\": [\n";
        $doc .= "            \"search\"\n";
        $doc .= "        ],\n";
        $doc .= "        \"summary\": \"Search all data in the aggregator\",\n";
        $instance = \App\Models\Collections\Artwork::instance();
        $doc .= $instance->swaggerProduces();
        $doc .= $instance->swaggerParameters($instance->docSearchParametersRaw());
        $doc .= $instance->swaggerResponses('SearchResult');
        $doc .= "      }\n";
        $doc .= "    }\n";

        $doc .= "  },\n";

        /**
         * Fields
         */
        $doc .= "  \"definitions\": {\n";
        $doc .= "    \"Error\": {\n";
        $doc .= "      \"required\": [\n";
        $doc .= "        \"status\",\n";
        $doc .= "        \"error\",\n";
        $doc .= "        \"detail\"\n";
        $doc .= "      ],\n";
        $doc .= "      \"properties\": {\n";
        $doc .= "        \"status\": {\n";
        $doc .= "          \"type\": \"integer\"\n";
        $doc .= "        },\n";
        $doc .= "        \"error\": {\n";
        $doc .= "          \"type\": \"string\"\n";
        $doc .= "        },\n";
        $doc .= "        \"detail\": {\n";
        $doc .= "          \"type\": \"string\"\n";
        $doc .= "        }\n";
        $doc .= "      }\n";
        $doc .= "    },\n";

        foreach ($this->getCategories() as $namespace => $heading)
        {
            foreach ($this->getModelsForNamespace($namespace) as $model)
            {
                $doc .= $model::instance()->swaggerFields($this->appUrl);
            }
        }

        $doc .= "    \"SearchResult\": {\n";
        $doc .= "      \"properties\": {\n";
        $doc .= "        \"_score\": {\n";
        $doc .= "          \"description\": \"Search index ranking of the result\"\n";
        $doc .= "        },\n";
        $doc .= "        \"id\": {\n";
        $doc .= "          \"description\": \"Unique identifier within the search index\"\n";
        $doc .= "        },\n";
        $doc .= "        \"api_id\": {\n";
        $doc .= "          \"description\": \"API unique identifier\"\n";
        $doc .= "        },\n";
        $doc .= "        \"api_model\": {\n";
        $doc .= "          \"description\": \"Name of the model the resource represents\"\n";
        $doc .= "        },\n";
        $doc .= "        \"api_link\": {\n";
        $doc .= "          \"description\": \"URL to this recource in the API\"\n";
        $doc .= "        },\n";
        $doc .= "        \"title\": {\n";
        $doc .= "          \"description\": \"The name of this resource\"\n";
        $doc .= "        },\n";
        $doc .= "        \"timestamp\": {\n";
        $doc .= "          \"description\": \"Date this record was last updated in the API\"\n";
        $doc .= "        },\n";
        $doc .= "        \"is_boosted\": {\n";
        $doc .= "          \"description\": \"Whether this record has been flagged to be boosted\"\n";
        $doc .= "        },\n";
        $doc .= "        \"thumbnail\": {\n";
        $doc .= "          \"description\": \"Metadata on the image representing this record\"\n";
        $doc .= "        }\n";
        $doc .= "      },\n";
        $doc .= "      \"type\": \"object\"\n";
        $doc .= "    }\n";
        $doc .= "\n";
        $doc .= "  },\n";
        $doc .= "\n";

        /**
         * Parameters
         */
        $doc .= "  \"parameters\": {\n";
        $doc .= "    \"id\": {\n";
        $doc .= "      \"name\": \"id\",\n";
        $doc .= "      \"in\": \"path\",\n";
        $doc .= "      \"type\": \"string\",\n";
        $doc .= "      \"required\": true\n";
        $doc .= "    },\n";

        $params = array_merge(
            $instance->docListParametersRaw(),
            $instance->docSearchParametersRaw()
        );

        foreach ($params as $param => $description)
        {
            $doc .= '    "' . $param . "\": {\n";
            $doc .= '      "name": "' . $param . "\",\n";
            $doc .= "      \"in\": \"query\",\n";
            $doc .= '      "description": "' . $description . "\",\n";
            $doc .= "      \"schema\": {\n";
            $doc .= '        "type": "' . ($param == 'limit' || $param == 'page' ? 'integer' : 'string') . "\"\n";
            $doc .= "      }\n";
            $doc .= '    }' . ($description !== end($params) ? ',' : '') . "\n";
        }

        $doc .= "  },\n";
        $doc .= "  \"externalDocs\": {\n";
        $doc .= "    \"description\": \"See more documentation on our API here:\",\n";
        $doc .= "    \"url\": \"https://www.github.com/art-institute-of-chicago/data-aggregator\"\n";
        $doc .= "  }\n";
        $doc .= "}\n";

        Storage::disk('local')->put('swagger.json', $doc);

    }

}
