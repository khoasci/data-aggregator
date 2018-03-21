<?php

namespace App\Models\Web;

use App\Models\WebModel;
use App\Models\Documentable;
use App\Models\ElasticSearchable;

/**
 * An event on the website
 */
class Event extends WebModel
{

    protected $casts = [
        'source_created_at' => 'date',
        'source_modified_at' => 'date',
        'published' => 'boolean',
        'is_private' => 'boolean',
        'is_after_hours' => 'boolean',
        'is_ticketed' => 'boolean',
        'is_free' => 'boolean',
        'is_member_exclusive' => 'boolean',
        'hidden' => 'boolean',
        'all_dates' => 'array',
    ];

    /**
     * Specific field definitions for a given class. See `transformMapping()` for more info.
     */
    protected function transformMappingInternal()
    {

        return [
            [
                "name" => 'type',
                "doc" => "Number indicating the type of event",
                "type" => "number",
                'elasticsearch_type' => 'integer',
                "value" => function() { return $this->type; },
            ],
            [
                "name" => 'short_description',
                "doc" => "Brief description of the event",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->short_description; },
            ],
            [
                "name" => 'description',
                "doc" => "Description of the event",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->description; },
            ],
            [
                "name" => 'hero_caption',
                "doc" => "Text displayed with the hero image on the event",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->hero_caption; },
            ],
            [
                "name" => 'is_private',
                "doc" => "Whether the event is private",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->is_private; },
            ],
            [
                "name" => 'is_after_hours',
                "doc" => "Whether the event is to be held after the museum closes",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->is_after_hours; },
            ],
            [
                "name" => 'is_ticketed',
                "doc" => "Whether a ticket is required to attend the event",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->is_ticketed; },
            ],
            [
                "name" => 'is_free',
                "doc" => "Whether the event is free",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->is_free; },
            ],
            [
                "name" => 'is_member_exclusive',
                "doc" => "Whether the event is exclusive to members of the museum",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->is_member_exclusive; },
            ],
            [
                "name" => 'hidden',
                "doc" => "Whether the event should appear in listings and in search",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->hidden; },
            ],
            [
                "name" => 'rsvp_link',
                "doc" => "The URL to the sales site for this event",
                "type" => "url",
                'elasticsearch_type' => 'keyword',
                "value" => function() { return $this->rsvp_link; },
            ],
            [
                "name" => 'start_date',
                "doc" => "The date the event begins",
                "type" => "string",
                'elasticsearch_type' => 'keyword',
                "value" => function() { return $this->start_date; },
            ],
            [
                "name" => 'end_date',
                "doc" => "The date the event ends",
                "type" => "string",
                'elasticsearch_type' => 'keyword',
                "value" => function() { return $this->end_date; },
            ],
            [
                "name" => 'all_dates',
                "doc" => "All the dates this event takes place",
                "type" => "array",
                'elasticsearch_type' => 'keyword',
                "value" => function() { return $this->all_dates; },
            ],
            [
                "name" => 'location',
                "doc" => "Where the event takes place",
                "type" => "string",
                'elasticsearch_type' => 'keyword',
                "value" => function() { return $this->location; },
            ],
            [
                "name" => 'sponsors_description',
                "doc" => "A description of who sponsors the event",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->sponsors_description; },
            ],
            [
                "name" => 'sponsors_sub_copy',
                "doc" => "Further details on who sponsors the event",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->sponsors_sub_copy; },
            ],
            [
                "name" => 'layout_type',
                "doc" => "Number indicating the type of layout this event page uses",
                "type" => "number",
                'elasticsearch_type' => 'integer',
                "value" => function() { return $this->layout_type; },
            ],
            [
                "name" => 'buy_button_text',
                "doc" => "The text used on the ticket/registration button",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->buy_button_text; },
            ],
            [
                "name" => 'buy_button_caption',
                "doc" => "Additional text below the ticket/registration button",
                "type" => "string",
                'elasticsearch_type' => 'text',
                "value" => function() { return $this->buy_button_caption; },
            ],
            [
                "name" => 'published',
                "doc" => "Whether the location is published on the website",
                "type" => "boolean",
                'elasticsearch_type' => 'boolean',
                "value" => function() { return $this->published; },
            ],
        ];

    }

    /**
     * Provide child classes a space to implement fill functionality for arrays and objects
     * returned from source APIs
     *
     * @param  object  $source
     * @return $this
     */
    protected function fillArraysAndObjectsFrom($source)
    {

        $this->all_dates = $source->all_dates;

        return $this;

    }

}
