<?php

namespace App\Http\Transformers;

use App\Collections\Exhibition;
use League\Fractal\TransformerAbstract;

class ExhibitionTransformer extends ApiTransformer
{

    public $citiObject = true;

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = ['artworks',];

    /**
     * List of resources to automatically include.
     *
     * @var array
     */
    protected $defaultIncludes = ['artworks'];

    protected function transformFields($item)
    {

        return [
            'description' => $item->description,
            'type' => $item->type,
            'department' => $item->department()->getResults() ? $item->department()->getResults()->title : '',
            'department_id' => $item->department_citi_id,
            'gallery' => $item->gallery()->getResults() ? $item->gallery()->getResults()->title : '',
            'gallery_id' => $item->gallery_citi_id,
            'dates' => $item->dates,
            'active' => (bool) $item->active,
        ];

    }


    /**
     * Include artworks.
     *
     * @param  \App\Collections\Exhibition  $exhibition
     * @return League\Fractal\ItemResource
     */
    public function includeArtworks(Exhibition $exhibition)
    {
        return $this->collection($exhibition->artworks()->getResults(), new ArtworkTransformer);
    }

}