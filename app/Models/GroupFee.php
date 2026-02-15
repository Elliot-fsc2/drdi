<?php

namespace App\Models;

use App\Collections\GroupFeeCollections;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Model;

#[CollectedBy(GroupFeeCollections::class)]
class GroupFee extends Model
{
    protected $fillable = [
        'group_id',
        'base_fee',
        'honorarium_total',
        'total_merger_amount',
    ];

    public function scopeActive($query)
    {
        return $query->whereHas('group.section.semester', function ($q) {
            $q->active();
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
