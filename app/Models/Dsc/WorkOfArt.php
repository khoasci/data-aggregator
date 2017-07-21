<?php

namespace App\Models\Dsc;

use App\Models\DscModel;

class WorkOfArt extends DscModel
{

    public $table = 'works_of_art';

    public function publication()
    {

        return $this->belongsTo('App\Models\Dsc\Publication');

    }

    public function artwork()
    {

        return $this->belongsTo('App\Models\Collections\Artwork');

    }

}
