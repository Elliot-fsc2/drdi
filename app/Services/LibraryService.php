<?php

namespace App\Services;

use App\Models\Group;

class LibraryService
{
  public function __construct(private Group $group) {}

  public function create()
  {
    if ($this->group->isEligibleForLibrary()) {
    }
  }

  public function update()
  {
    return 'Library updated';
  }
}
