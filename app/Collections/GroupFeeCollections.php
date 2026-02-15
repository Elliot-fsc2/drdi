<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class GroupFeeCollections extends Collection
{
    public function totalCollectibles(): float
    {
        return $this->sum('base_fee');
    }

    public function totalExpenses(): float
    {
        return $this->sum('honorarium_total');
    }

    public function totalSavings(): float
    {
        return $this->totalCollectibles() - $this->totalExpenses();
    }

}
