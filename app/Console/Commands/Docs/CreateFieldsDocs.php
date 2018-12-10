<?php

namespace App\Console\Commands\Docs;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CreateFieldsDocs extends AbstractDocCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate documentation for all the fields on each endpoint';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $doc = '';

        foreach ($this->getCategories() as $namespace => $heading)
        {
            $doc .= "# $heading\n\n";

            foreach ($this->getModelsForNamespace($namespace) as $model)
            {
                $doc .= $model::instance()->docFields();
            }
        }

        $doc .= "> Generated by `php artisan docs:fields` on " .Carbon::now() ."\n";

        Storage::disk('local')->put('FIELDS.md', $doc);

    }

}
