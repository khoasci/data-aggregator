<?php

namespace App\Models;

use Laravel\Scout\Searchable;

trait SolrSearchable
{

    use Searchable;

    protected $apiCtrl;

    public function __construct(array $attributes = array()) {

        parent::__construct($attributes);

        $this->apiCtrl = $this->apiCtrl ?: str_plural( class_basename(static::class) ) . 'Controller';

    }

    protected function searchableLink()
    {

        return action($this->apiCtrl . '@show', ['id' => $this->getKey()]);

    }

    protected function searchableModel()
    {

        return kebab_case(class_basename(static::class));

    }

    protected function searchableSource()
    {

        return kebab_case( array_slice( explode('\\', static::class), -2, 1)[0] );

    }

    protected function searchableId()
    {

        return $this->searchableSource() .'/' .$this->searchableModel() .'/' .$this->shop_id;

    }

    public function toSearchableArray()
    {

        // @TODO Also send all attributes
        // @TODO Transform attributes as in API
        // @TODO Inject titles etc. from related models

        $array = [
            'id' => $this->searchableId(),
            'api_id' => $this->getKey(),
            'api_model' => $this->searchableModel(),
            'api_link' => $this->searchableLink(),
            'title' => $this->title,
        ];

        return $array;

    }

}
