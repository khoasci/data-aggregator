<?php

namespace App\Transformers\Inbound;

use App\Transformers\Datum;

class WebTransformer extends BaseTransformer
{
    /**
     * Get dates from source data. Meant to be overwritten.
     *
     * @TODO Grab dates from the model's `getDates` method..?
     *
     * @return array
     */
    protected function getDates(Datum $datum)
    {
        return array_merge(
            parent::getDates($datum),
            [
                'source_modified_at' => $datum->date('last_updated'),
            ]
        );
    }
}
