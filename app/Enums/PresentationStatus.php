<?php

namespace App\Enums;

enum PresentationStatus: string
{
  case PASSED = 'passed';
  case REDEFENSE = 'redefense';
  case FAILED = 'failed';
  case SCHEDULED = 'scheduled';
}
