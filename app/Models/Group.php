<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'section_id', 'leader_id', 'final_title_id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function leader()
    {
        return $this->belongsTo(Student::class, 'leader_id');
    }

    public function members()
    {
        return $this->belongsToMany(Student::class, 'members', 'group_id', 'student_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function finalTitle()
    {
        return $this->belongsTo(Proposal::class, 'final_title_id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }

    public function fee()
    {
        return $this->hasOne(GroupFee::class);
    }
}
