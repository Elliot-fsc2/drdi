<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Group;

class GroupMasterlist implements FromView, WithEvents, WithColumnWidths, WithStyles
{
    public function __construct(
        public ?int $semesterId = null,
        public string $search = ''
    ) {}

    public function view(): View
    {
        $groups = Group::with([
            'section.program',
            'section.semester',
            'section.instructor',
            'leader',
            'members.program',
            'personnel.instructor',
            'fee',
        ])
            ->whereHas('section', function ($query) {
                if ($this->semesterId) {
                    $query->where('semester_id', $this->semesterId);
                }
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('members', function ($memberQuery) {
                            $memberQuery->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhere('student_number', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('section.program', function ($programQuery) {
                            $programQuery->where('name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->orderBy('name')
            ->get();

        return view('components.group-masterlist-export', [
            'groups' => $groups
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Group Name
            'B' => 30, // Researchers
            'C' => 20, // Program
            'D' => 20, // Subject Name
            'E' => 20, // Subject Instructor
            'F' => 30, // Assigned Personnel
            'G' => 12, // Base Fee
            'H' => 12, // Honorarium
            'I' => 12, // Total
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E8F0'], // Tailwind slate-200 equivalent
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Set page orientation and paper size for PDF
                $event->sheet->getDelegate()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
                
                $highestRow = $event->sheet->getHighestRow();
                
                // Apply word wrapping and vertical top alignment to all cells
                $style = $event->sheet->getDelegate()->getStyle('A1:I' . $highestRow);
                $style->getAlignment()->setWrapText(true);
                $style->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            },
        ];
    }
}
