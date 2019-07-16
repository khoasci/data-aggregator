<?php

namespace App\Transformers\Outbound\Web;

use App\Transformers\Outbound\Collections\Traits\HidesDefaultFields;

use App\Transformers\Outbound\AbstractTransformer as BaseTransformer;

class EventEmailSeriesPivot extends BaseTransformer
{
    use HidesDefaultFields;

    protected function getFields()
    {
        return [
            'event_id' => [
                'doc' => 'Unique identifier of the event associated with this pivot',
                'type' => 'number',
                'value' => function ($item) {
                    return $item->event->id ?? null;
                },
            ],
            'email_series_id' => [
                'doc' => 'Unique identifier of the email series associated with this pivot',
                'type' => 'number',
                'value' => function ($item) {
                    return $item->emailSeries->id ?? null;
                },
            ],
            'send_non_member' => [
                'doc' => 'Whether this event should be sent to Non-Members subscribed to this email series',
                'type' => 'boolean',
            ],
            'send_member' => [
                'doc' => 'Whether this event should be sent to Members subscribed to this email series',
                'type' => 'boolean',
            ],
            'send_sustaining_fellow' => [
                'doc' => 'Whether this event should be sent to Sustaining Fellows subscribed to this email series',
                'type' => 'boolean',
            ],
            'send_affiliate_member' => [
                'doc' => 'Whether this event should be sent to Affiliate Members subscribed to this email series',
                'type' => 'boolean',
            ],
            'non_member_copy' => [
                'doc' => 'Copy to use for Non-Members when communicating this event in this email series',
                'type' => 'string',
            ],
            'member_copy' => [
                'doc' => 'Copy to use for Members when communicating this event in this email series',
                'type' => 'string',
            ],
            'sustaining_fellow_copy' => [
                'doc' => 'Copy to use for Sustaining Fellows when communicating this event in this email series',
                'type' => 'string',
            ],
            'affiliate_member_copy' => [
                'doc' => 'Copy to use for Affiliate Members when communicating this event in this email series',
                'type' => 'string',
            ],
        ];
    }

}