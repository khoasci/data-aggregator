<?php

namespace App\Transformers\Inbound\Web;

use App\Transformers\Datum;
use App\Transformers\Inbound\AbstractTransformer;

class Event extends AbstractTransformer
{

    protected function getExtraFields( Datum $datum )
    {

        return [
            'start_date' => $datum->datetime('start_date'),
            'end_date' => $datum->datetime('end_date'),
            'type' => $datum->event_type,
        ];

    }

}
