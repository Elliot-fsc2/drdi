<?php

namespace App\Models;

use App\Enums\PresentationStatus;
use App\Enums\PresentationType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Schedule extends Model
{
    use LogsActivity;

    protected $fillable = [
        'section_id',
        'group_id',
        'venue',
        'date',
        'start_time',
        'end_time',
        'presentation_type',
        'status',
        'panelists',
    ];

    public function casts()
    {
        return [
            'date' => 'date',
            'panelists' => 'array',
            'status' => PresentationStatus::class,
            'presentation_type' => PresentationType::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Schedule')
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName} by :causer.name");
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
