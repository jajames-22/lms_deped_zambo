<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssessmentTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'category', 
            'time_limit', 
            'type', 
            'question',
            'option_1', 
            'option_2', 
            'option_3', 
            'option_4', 
            'correct_answer'
        ];
    }

    public function array(): array
    {
        // Providing two rows of sample data so users know how to fill it out
        return [
            ['General Knowledge', '10', 'mcq', 'What is the capital of the Philippines?', 'Cebu', 'Manila', 'Davao', 'Iloilo', 'Option 2'],
            ['General Knowledge', '5', 'true_false', 'The earth is flat.', '', '', '', '', 'Option 2'],
        ];
    } 
}