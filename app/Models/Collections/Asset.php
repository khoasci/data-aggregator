<?php

namespace App\Models\Collections;

use App\Models\CollectionsModel;
use App\Models\ElasticSearchable;
use App\Models\Documentable;

/**
 * A binary representation of a collections resource, like an artwork, artist, exhibition, etc.
 */
class Asset extends CollectionsModel
{

    use ElasticSearchable;
    use Documentable;

    protected $primaryKey = 'lake_guid';
    protected $keyType = 'string';

    protected $dates = ['source_created_at', 'source_modified_at', 'source_indexed_at'];

    protected $casts = [
        'metadata' => 'object',
    ];

    protected static $assetType = null;

    /**
     * Filters the `assets` table by `type` to match `$assetType` of the model.
     * Uses the inline method for scope definition, rather than creating new classes.
     *
     * @link https://stackoverflow.com/questions/20701216/laravel-default-orderby
     *
     * {@inheritdoc}
     */
    protected static function boot() {

        parent::boot();

        // Allows querying all assets via the Asset class directly
        if( !static::$assetType )
        {
            return;
        }

        static::addGlobalScope('assets', function ($builder) {
            $builder->where('type', '=', static::$assetType );
        });

    }

    /**
     * Create a new instance of the given model. For Assets, we use this to set a default `type`.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {

        $model = parent::newInstance($attributes, $exists);
        $model->type = static::$assetType;
        return $model;

    }


    // @TODO: It looks like the agent_citi_id field is missing...
    // public function artist()
    // {
    //
    //     return $this->belongsTo('App\Models\Collections\Artist', 'agent_citi_id');
    //
    // }

    public function categories()
    {

        return $this->belongsToMany('App\Models\Collections\Category', 'asset_category', 'asset_lake_guid');

    }

    // Note: Not all Images are meant to be associated w/ Artworks
    public function artworks()
    {

        return $this->belongsToMany('App\Models\Collections\Artwork', 'artwork_asset', 'asset_lake_guid');

    }

    public function getFillFieldsFrom($source)
    {

        return [
            'description' => $source->description,
            'content' => $source->content,
            'published' => $source->published,
        ];

    }

    public function attachFrom($source)
    {

        // if ($source->artist_id)
        // {
        //
        //     $this->agent_citi_id = $source->artist_id;
        //
        // }

        return $this;

    }

    /**
     * Specific field definitions for a given class. See `transformMapping()` for more info.
     */
    protected function transformMappingInternal()
    {

        return array_merge(
            [

                'type' => [
                    "doc" => "Typs always takes one of the following values: image, link, sound, text, video",
                    "type" => "string",
                    "value" => function() { return $this->type; },
                ],
                'description' => [
                    "doc" => "Explanation of what this asset is",
                    "type" => "string",
                    "value" => function() { return $this->description; },
                ],
                'content' => [
                    "doc" => "Text of URL of the contents of this asset",
                    "type" => "string",
                    "value" => function() { return $this->content; },
                ],
                // @TODO Re-enable this once the artist association is fixed
                // 'artist' => [
                //     "doc" => "Name of the artist associated with this asset",
                //     "value" => function() { return $this->artist()->getResults() ? $this->artist()->getResults()->title : ''; },
                // ],
                // 'artist_id' => [
                //     "doc" => "Unique identifier of the artist associated with this asset",
                //     "value" => function() { return $this->agent_citi_id; },
                // ],
                'category_ids' => [
                    "doc" => "Unique identifier of the categories associated with this asset",
                    "type" => "array",
                    "value" => function() { return $this->categories->pluck('citi_id')->all(); },
                ],
                'artwork_ids' => [
                    "doc" => "Unique identifiers of the artworks associated with this asset",
                    "value" => function() { return $this->artworks->pluck('citi_id')->all(); },
                ],
                'artwork_titles' => [
                    "doc" => "The names of the artworks associated with this asset",
                    "value" => function() { return $this->artworks()->pluck('title'); },
                ],
                'metadata' => [
                    "doc" => "Type-specific metadata about this asset",
                    "type" => "object",
                    "value" => function() { return $this->metadata; },
                ],
            ],
            $this->transformAsset()
        );

    }

    /**
     * Provide a way for child classes add fields to the transformation.
     *
     * @return array
     */
    public function transformAsset()
    {

        return [];

    }

    /**
     * Turn the titles for related models into a generic array
     *
     * @return array
     */
    protected function transformTitles()
    {

        return [

            'category_titles' => $this->categories->pluck('title')->all(),

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
                // @TODO Re-enable this once the artist association is fixed
                // 'artist' => [
                //     'type' => 'text',
                // ],
                // 'artist_id' => [
                //     'type' => 'integer',
                // ],
                'category_ids' => [
                    'type' => 'integer',
                ],
                'category_titles' => [
                    'type' => 'text',
                ],
            ];

    }

}
