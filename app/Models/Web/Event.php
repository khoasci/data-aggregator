<?php

namespace App\Models\Web;

use App\Models\WebModel;

/**
 * An event on the website
 */
class Event extends WebModel
{

    protected $casts = [
        'published' => 'boolean',
        'is_private' => 'boolean',
        'is_after_hours' => 'boolean',
        'is_ticketed' => 'boolean',
        'is_free' => 'boolean',
        'is_member_exclusive' => 'boolean',
        'is_admission_required' => 'boolean',
        'is_registration_required' => 'boolean',
        'is_sold_out' => 'boolean',
        'show_presented_by' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'alt_event_types' => 'array',
        'alt_audiences' => 'array',
        'programs' => 'array',
        'publish_start_date' => 'datetime',
        'publish_end_date' => 'datetime',
    ];

    public function ticketedEvent()
    {
        return $this->belongsTo('App\Models\Membership\TicketedEvent', 'ticketed_event_id', 'membership_id');
    }

    public function emailSeriesPivots()
    {
        return $this->hasMany('App\Models\Web\EventEmailSeriesPivot');
    }

    public function emailSeries()
    {
        return $this
            ->belongsToMany('App\Models\Web\EmailSeries', 'event_email_series')
            ->using('App\Models\Web\EventEmailSeriesPivot')
            ->withPivot(
                'send_affiliate',
                'affiliate_copy',
                'send_member',
                'member_copy',
                'send_sustaining_fellow',
                'sustaining_fellow_copy',
                'send_nonmember',
                'nonmember_copy'
            );
    }

    public function sponsor()
    {
        return $this->belongsTo('App\Models\Web\Sponsor');
    }

    public function eventHost()
    {
        return $this->belongsTo('App\Models\Web\EventProgram');
    }

}
