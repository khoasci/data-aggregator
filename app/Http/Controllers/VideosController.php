<?php

namespace App\Http\Controllers;

class VideosController extends AssetsController
{

    protected $model = \App\Models\Collections\Video::class;

    protected $transformer = \App\Http\Transformers\AssetTransformer::class;

}
