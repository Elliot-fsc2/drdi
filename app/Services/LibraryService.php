<?php

namespace App\Services;

use App\Models\Group;

class LibraryService
{

  public function create(Group $group)
  {
    if ($group->isEligibleForLibrary()) {
    }
  }

  public function update()
  {
    return 'Library updated';
  }
}
