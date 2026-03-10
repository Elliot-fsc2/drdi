<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PresentationType: string implements HasLabel
{
  case THESIS_A_PROPOSAL = 'Title Proposal';
  case THESIS_A_ORAL = 'Oral Defense';
  case THESIS_A_MOCK = 'Mock Defense';
  case THESIS_A_FINAL = 'Final Defense';
  case THESIS_B_ORAL = 'Thesis B - Oral Defense';
  case THESIS_B_MOCK = 'Thesis B - Mock Defense';
  case THESIS_B_FINAL = 'Thesis B - Final Defense';

  public function getLabel(): string
  {
    return match ($this) {
      self::THESIS_A_PROPOSAL => 'Title Proposal',
      self::THESIS_A_ORAL => 'Oral Defense',
      self::THESIS_A_MOCK => 'Mock Defense',
      self::THESIS_A_FINAL => 'Final Defense',
      self::THESIS_B_ORAL => 'Thesis B - Oral Defense',
      self::THESIS_B_MOCK => 'Thesis B - Mock Defense',
      self::THESIS_B_FINAL => 'Thesis B - Final Defense',
    };
  }
}
