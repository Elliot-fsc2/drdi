<?php

namespace App\Models;

use App\Enums\ResearchLibraryStatus;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ResearchLibrary extends Model
{
    use LogsActivity;

    protected $fillable = [
        'group_id',
        'title',
        'academic_year',
        'abstract',
        'file_path',
        'is_published',
        'published_at',
        'status',
        'review_note',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'status' => ResearchLibraryStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Research Library')
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName} by :causer.name");
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', ResearchLibraryStatus::PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ResearchLibraryStatus::APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', ResearchLibraryStatus::REJECTED);
    }

    public function getRouteKeyName(): string
    {
        return 'title';
    }
}
