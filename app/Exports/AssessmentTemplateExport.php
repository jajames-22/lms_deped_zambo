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
            'media_url', // <-- Updated to media_url
            'option_1', 
            'option_2', 
            'option_3', 
            'option_4', 
            'correct_answer'
        ];
    }

    public function array(): array
    {
        return [
            // Example 1: Standard MCQ
            ['General Knowledge', '10', 'mcq', 'What is the capital of the Philippines?', '', 'Cebu', 'Manila', 'Davao', 'Iloilo', 'Option 2'],
            
            // Example 2: True/False 
            ['General Knowledge', '5', 'true_false', 'The earth is flat.', '', 'True', 'False', '', '', 'Option 2'],
            
            // Example 3: A question WITH a media URL!
            ['General Knowledge', '10', 'mcq', 'What animal is shown in the picture?', 'https://upload.wikimedia.org/wikipedia/commons/4/4d/Cat_November_2010-1a.jpg', 'Dog', 'Cat', 'Bird', 'Fish', 'Option 2'],
        ];
    } 
}