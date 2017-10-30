<?php

namespace App\Models\Dsc;

use App\Models\DscModel;
use App\Models\ElasticSearchable;
use App\Models\Documentable;

class WorkOfArt extends DscModel
{

    use ElasticSearchable;
    use Documentable;

    public $table = 'works_of_art';

    // @TODO: Because WorkOfArt uses ElasitcSearchable directly, we error out on $apiCtrl
    // protected $apiCtrl = 'WorksOfArt';

    protected function searchableLink()
    {

        return action('WorksOfArtController@show', ['id' => $this->getKey()]);

    }

    public function publication()
    {

        return $this->belongsTo('App\Models\Dsc\Publication');

    }

    public function artwork()
    {

        return $this->belongsTo('App\Models\Collections\Artwork');

    }


    /**
     * Turn this model object into a generic array.
     *
     * @param boolean  $withTitles
     * @return array
     */
    public function transformFields()
    {

        return [
            'content' => $this->content,
            'weight' => $this->weight,
            'depth' => $this->depth,
            'publication' => $this->publication ? $this->publication->title : '',
            'publication_id' => $this->publication ? $this->publication->dsc_id : null,
            'artwork' => $this->artwork ? $this->artwork->title : '',
            'artwork_id' => $this->artwork ? $this->artwork->citi_id : null,
        ];

    }


    /**
     * Generate model-specific fields for an array representing the schema for this object.
     *
     * @return array
     */
    public function elasticsearchMappingFields()
    {

        return
            [
                'content' => [
                    'type' => 'text',
                ],
                'weight' => [
                    'type' => 'integer',
                ],
                'depth' => [
                    'type' => 'integer',
                ],
                'publication' => [
                    'type' => 'text',
                ],
                'publication_id' => [
                    'type' => 'integer',
                ],
                'artwork' => [
                    'type' => 'text',
                ],
                'artwork_id' => [
                    'type' => 'integer',
                ],
            ];

    }

    /**
     * Get an example ID for documentation generation
     *
     * @return string
     */
    public function exampleId()
    {

        return "5400";

    }

}
